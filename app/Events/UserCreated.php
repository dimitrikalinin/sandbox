<?php

namespace App\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class UserCreated extends ShouldBeStored
{
    /** @var array */
    public $userAttributes;

    public function __construct(string $name, string $email, string $password)
    {
        $this->userAttributes = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];
    }
}