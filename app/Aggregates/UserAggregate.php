<?php

namespace App\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use App\Events\UserCreated;

class UserAggregate extends AggregateRoot
{
    public function createUser($name, $email, $password)
    {
        $this->recordThat(new UserCreated($name, $email, $password));
        
        return $this;
    }
    
}
