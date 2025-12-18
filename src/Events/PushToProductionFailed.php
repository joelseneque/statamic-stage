<?php

namespace JoelSeneque\StatamicStage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Contracts\Auth\User;

class PushToProductionFailed
{
    use Dispatchable;

    public function __construct(
        public ?User $user,
        public string $error
    ) {}
}
