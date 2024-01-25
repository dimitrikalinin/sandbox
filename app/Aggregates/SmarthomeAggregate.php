<?php

namespace App\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use App\Events\SmarthomeCreated;

class SmarthomeAggregate extends AggregateRoot
{
    public function createSmarthome(string $id, string $description='', $properties=[])
    {
        $this->recordThat(new SmarthomeCreated($id, $description, $properties));
        
        return $this;
    }
}
