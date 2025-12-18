<?php

namespace JoelSeneque\StatamicStage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Contracts\Auth\User;

class PushToProductionStarted
{
    use Dispatchable;

    public function __construct(public ?User $user) {}
}
