<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Services\ZipkinService;
use App\Facades\Zipkin;
use Zipkin\Timestamp;

class ZipkinBaseController extends Controller
{
    protected $span;
    public $zipkinService;

    public function __construct(ZipkinService $zipkinService)
    {
        $this->zipkinService = $zipkinService;

    }

    public function callAction($method, $parameters)
    {
        if (is_null(Zipkin::getRootSpan())) {
            return parent::callAction($method, $parameters);
        }

        $classCaller = get_called_class();
        $className   = Arr::last(explode("\\", $classCaller));

        $tracing = Zipkin::createTracing($className, Request::getClientip());
        $tracer  = $tracing->getTracer();

        $this->span = Zipkin::createChild($method);
        $this->span->annotate("Start", Timestamp\now());
        $this->span->start(Timestamp\now());
        $this->span->tag("class", $classCaller);
        $this->span->tag("method", $method);
        $this->span->tag("user", Auth::user()->username ?? 'anonymous');

        $action = parent::callAction($method, $parameters);

        $this->span->annotate("End", Timestamp\now());
        $this->span->finish(Timestamp\now());
        $tracer->flush();
        Zipkin::closeCurrentSpan();

        return $action;
    }

    public function __destruct()
    {

    }

}