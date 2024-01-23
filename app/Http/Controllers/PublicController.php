<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Zipkin\Timestamp;
use App\Facades\Zipkin;
use App\Aggregates\UserAggregate;
use Ramsey\Uuid\Uuid;
use Faker;
use Illuminate\Support\Facades\Log;

class PublicController extends ZipkinBaseController
{
    public function welcome(Request $request): View
    {
        // Create tracer
//        $tracing = $this->zipkinService->createTracing('child_span_tracing', $request->ip());
        $tracer = Zipkin::getTracer(); 

        /**
         * Create Span
         * @var Zipkin\Span $span 
         */
        $span = Zipkin::createChild('Create user aggregate');
        $span->annotate("Start", Timestamp\now());
        $start = Timestamp\now();
        Log::debug('start', [$start]);
        $span->start($start);
        
        Log::debug('current span', [Zipkin::getCurrentSpan()]);
        
        $faker = Faker\Factory::create();
        
        call_user_func(
                ['\Illuminate\Support\Facades\Artisan', 'call'],
                command: 'user:create',
                parameters: [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'password' => $faker->password,
                    'uuid' => Uuid::uuid4(),
                ]
            );
//        \Illuminate\Support\Facades\Artisan::call(
//                command: 'user:create',
//                parameters: [
//                    'name' => $faker->name,
//                    'email' => $faker->email,
//                    'password' => $faker->password,
//                    'uuid' => Uuid::uuid4(),
//                ]
//            );
    
        // Close Span
        $span->annotate("End", Timestamp\now());
        $end = Timestamp\now();
        Log::debug('end', [$end]);
        $span->finish($end);
        
        Zipkin::closeCurrentSpan();
        
        // Create Span
        $span = Zipkin::createChild('Child span 2');
        $span->annotate("Start", Timestamp\now());
        $span->start(Timestamp\now());
        
        $span2 = Zipkin::createChild('Child span 2.1');
        $span2->annotate("Start", Timestamp\now());
        $span2->setName('Child span 2.1');
        $span2->start(Timestamp\now());
        
        usleep(rand(1000, 10000));
    
        // Close Span
        $span2->annotate("End", Timestamp\now());
        $span2->finish(Timestamp\now());
        Zipkin::closeCurrentSpan();
        
        usleep(rand(1000, 10000));
    
        // Close Span
        $span->annotate("End", Timestamp\now());
        $span->finish(Timestamp\now());
        Zipkin::closeCurrentSpan();
        
        return view('welcome');
    }
}
