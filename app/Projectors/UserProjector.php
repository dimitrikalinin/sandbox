<?php

namespace App\Projectors;

use App\Events\UserCreated;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use App\Models\User;

class UserProjector extends Projector
{
    public function onUserCreated(UserCreated $event)
    {
        $user = new User;
        $user->name = $event->userAttributes['name'];
        $user->email = $event->userAttributes['email'];
        $user->password = $event->userAttributes['password'];
        $user->save();
    }
}