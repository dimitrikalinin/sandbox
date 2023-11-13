<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Zipkin\Timestamp;
use App\Facades\Zipkin;

class PublicController extends ZipkinBaseController
{
    public function welcome(Request $request): View
    {
        // Create tracer
//        $tracing = $this->zipkinService->createTracing('child_span_tracing', $request->ip());
        $tracer = $this->zipkinService->getTracer(); 

        // Create Span
        $span = $tracer->nextSpan($this->span->getContext());
        $span->annotate("Start", Timestamp\now());
        $span->setName('Child span');
        $span->start(Timestamp\now());
        
        usleep(rand(1000, 10000));
    
        // Close Span
        $span->annotate("End", Timestamp\now());
        $span->finish(Timestamp\now());
        
        // Create Span
        $span = $tracer->nextSpan($this->span->getContext());
        $span->annotate("Start", Timestamp\now());
        $span->setName('Child span 2');
        $span->start(Timestamp\now());
        
        $span2 = $tracer->nextSpan($span->getContext());
        $span2->annotate("Start", Timestamp\now());
        $span2->setName('Child span 2.1');
        $span2->start(Timestamp\now());
        
        usleep(rand(1000, 10000));
    
        // Close Span
        $span2->annotate("End", Timestamp\now());
        $span2->finish(Timestamp\now());
        
        usleep(rand(1000, 10000));
    
        // Close Span
        $span->annotate("End", Timestamp\now());
        $span->finish(Timestamp\now());
        
        return view('welcome');
    }
}
