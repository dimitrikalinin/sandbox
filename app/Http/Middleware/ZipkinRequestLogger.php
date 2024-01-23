<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Services\ZipkinService;
use Illuminate\Support\Facades\Log;
use App\Facades\Zipkin;


class ZipkinRequestLogger
{
    private $zipkinService;

    public function __construct(ZipkinService $zipkinService)
    {
        $this->zipkinService = $zipkinService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (in_array($request->method(), Zipkin::getAllowedMethods())) {
            Log::debug(
                'route name',
                [
                    get_class($request->route()),
                    $request->route()->getName(),
                ]
            );

            Zipkin::setTracer($request->route()->getName(), $request->ip());

            foreach ($request->query() as $key => $value) {
                $tags["query." . $key] = $value;
            }

            Zipkin::createRootSpan('incoming_request', ($tags ?? []))
                ->setRootSpanMethod($request->method())
                ->setRootSpanPath($request->path())
                ->setRootAuthUser(Auth::user())
                ->setRootSpanTag('request.headers', json_encode($request->headers->all()))
                ->setRootSpanTag('request.body', json_encode($request->all()));
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {

        if (!is_null(Zipkin::getRootSpan())) {
            Zipkin::setRootSpanStatusCode($response->getStatusCode())->closeSpan();
        }

    }

}