<?php

namespace App\Console\Commands;

use Ramsey\Uuid\Uuid;
use App\Aggregates\SmarthomeAggregate;
use App\Console\Commands\ZipkinCommandAbstract;

class CreateSmarthome extends ZipkinCommandAbstract
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smarthome:create {uuid} {description?} {properties?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new smarthome';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uuid = $this->argument('uuid') ?: Uuid::uuid4();
        
        SmarthomeAggregate::retrieve($uuid)
            ->createSmarthome(
                $uuid, 
                $this->argument('description'), 
                $this->argument('properties'), 
            )->persist();
    }
}
