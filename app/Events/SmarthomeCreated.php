<?php

namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class SmarthomeCreated extends ShouldBeStored
{
    public $payload;

    public function __construct(string $id, string $description, string $properties)
    {
        $this->payload = [
            'id' => $id,
            'description' => $description,
            'properties' => $properties,
        ];
    }
}