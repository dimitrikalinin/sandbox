<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use App\Facades\Zipkin;
use Zipkin\Timestamp;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ZipkinCommandAbstract extends Command
{
    /**
     * Execute the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null(Zipkin::getCurrentSpan())) {
            return parent::execute($input, $output);
        }

        $classCaller = get_called_class();
        $className   = Arr::last(explode("\\", $classCaller));

        /**
         * @var $tracer App\Services\ZipkinService
         */
        // Create Span
        $span = Zipkin::createChild($className . ':execute', true);
        $span->annotate("Start", Timestamp\now());
        $span->tag("class", $classCaller);
        $span->tag("method", 'execute');
        $span->tag("user", Auth::user()->username ?? 'anonymous');

        $result = parent::execute($input, $output);

        $span->annotate("End", Timestamp\now());
        Zipkin::closeCurrentSpan(true);

        return $result;
    }
}
