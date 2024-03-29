<?php

namespace App\Services;

use App\Models\Auth\User;
use const Zipkin\Kind\SERVER;
use Zipkin\Span;
use Zipkin\Tracer;
use \Zipkin\Endpoint;
use \Zipkin\Propagation\DefaultSamplingFlags;
use \Zipkin\Samplers\BinarySampler;
use \Zipkin\Timestamp;
use \Zipkin\TracingBuilder;
use Illuminate\Support\Facades\Log;


class ZipkinService
{

    const REPORTER_URL               = "/api/v2/spans";
    const ROOT_SPAN_HTTP_STATUS_CODE = "http.status_code";
    const ROOT_SPAN_HTTP_PATH        = "http.path";
    const ROOT_SPAN_HTTP_METHOD      = "http.method";
    const ROOT_SPAN_USER_USERNAME    = "user.username";
    const ROOT_SPAN_RESPONSE_STATUS  = "response.status";
    const ROOT_SPAN_RESPONSE_MESSAGE = "response.message";

    private $config;
    private $httpReporterURL;
    private $tracing;
    private $tracer;
    private $rootSpan = null;
    private $spanStack = [];

    public function __construct()
    {
        $this->config          = config('zipkin');
        $this->httpReporterURL = $this->config["host"] . ":" . $this->config["port"] . self::REPORTER_URL;

        return $this;
    }

    public function setTracer(string $tracer = "middleware", string $ipV4 = "127.0.0.1")
    {
        $this->tracing = $this->createTracing($tracer, $ipV4);

        $this->tracer = $this->tracing->getTracer();

        return $this;
    }

    public function getTracer(): ?Tracer
    {
        return $this->tracer;
    }

    public function setRootSpan($span)
    {
        $this->rootSpan = $span;
        $this->spanStack = [$span];
        return $this;
    }
    
    public function setCurrentSpan($span)
    {
        array_push($this->spanStack, $span);
        return $this;
    }
    
    public function closeCurrentSpan(bool $autofinish = false)
    {
        $span = array_pop($this->spanStack);
        if ($autofinish) {
            $span->finish(Timestamp\now());
        }
        
        return $span;
    }

    public function createRootSpan(string $name, array $tags = [])
    {
        /* Always sample traces */
        $defaultSamplingFlags = DefaultSamplingFlags::createAsSampled();

        /* Creates the main span */
        $this->rootSpan = $this->tracer->newTrace($defaultSamplingFlags);
        $this->rootSpan->start(Timestamp\now());
        $this->rootSpan->setName($name);
        $this->rootSpan->setKind(SERVER);
        
        $this->spanStack = [$this->rootSpan];

        foreach ($tags as $key => $value) {
            $this->setRootSpanTag($key, $value);
        }

        return $this;
    }

    public function getRootSpan(): ?Span
    {
        return $this->rootSpan;
    }
    
    public function getCurrentSpan(): ?Span
    {
        return end($this->spanStack);
    }    
        
        
    public function getAllowedMethods()
    {
        return $this->config['allowed_methods'];
    }

    public function setRootSpanPath(string $path)
    {
        $this->setRootSpanTag(self::ROOT_SPAN_HTTP_PATH, $path);

        return $this;
    }

    public function setRootSpanMethod(string $method)
    {
        $this->setRootSpanTag(self::ROOT_SPAN_HTTP_METHOD, $method);
        return $this;
    }

    public function setRootSpanStatusCode(string $code)
    {
        $this->setRootSpanTag(self::ROOT_SPAN_HTTP_STATUS_CODE, $code);
        return $this;
    }

    public function setRootAuthUser(?User $user)
    {
        $this->setRootSpanTag(self::ROOT_SPAN_USER_USERNAME, $user->username ?? 'anonymous');
        return $this;
    }

    public function setRootSpanTag($key, $value)
    {
        $this->rootSpan->tag($key, $value);
        return $this;
    }
    public function setRootSpanAnnotation($key, $timestamp)
    {
        $this->rootSpan->annotate($key, $timestamp);
        return $this;
    }

    public function getRootSpanContext()
    {
        return $this->rootSpan->getContext();
    }

    /**
     * 
     * @param string $name
     * @param bool $autostart
     * @return Span|null
     */
    public function createChild(string $name, bool $autostart = false): ?Span
    {
        $span = $this->tracer->newChild(end($this->spanStack)->getContext());
        $span->setName($name);
        if ($autostart) {
            $span->start(Timestamp\now());
        }
        array_push($this->spanStack, $span);

        return $span;
    }

    public function createTracing($localServiceName, $localServiceIPv4, $localServicePort = null)
    {
        $endpoint = Endpoint::create($localServiceName, $localServiceIPv4, null, $localServicePort);
        
        $reporter = new \Zipkin\Reporters\Http(
            ['endpoint_url' => $this->httpReporterURL],
            \Zipkin\Reporters\Http\CurlFactory::create()
        );

        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return $tracing;

    }

    public function setRootResponse($status, $message = "")
    {
        $this->setRootSpanTag(self::ROOT_SPAN_RESPONSE_STATUS, $status)
            ->setRootSpanTag(self::ROOT_SPAN_RESPONSE_MESSAGE, $message);
        return $this;
    }

    public function closeSpan()
    {
        Log::debug('flush tracer');
        $this->getRootSpan()->finish();
        $this->getTracer()->flush();
    }

}