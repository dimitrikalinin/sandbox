<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use App\Aggregates\UserAggregate;
use App\Console\Commands\ZipkinCommandAbstract;

class CreateUser extends ZipkinCommandAbstract
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {name} {email} {password} {uuid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uuid = $this->argument('uuid') ?: Uuid::uuid4();
        
        UserAggregate::retrieve($uuid)
                ->createUser(
                        $this->argument('name'), 
                        $this->argument('email'), 
                        $this->argument('password'), 
                        )
                ->persist();
    }
}
