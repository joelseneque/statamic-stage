<?php

namespace JoelSeneque\StatamicStage\Listeners;

class PreventProductionEdits
{
    public function handle(object $event): bool
    {
        if (! $this->isReadOnlyMode()) {
            return true;
        }

        // Throw an exception to prevent the save and show a message
        throw new \RuntimeException(
            __('statamic-stage::messages.read_only_blocked')
        );
    }

    protected function isReadOnlyMode(): bool
    {
        return config('statamic-stage.read_only_on_production', true)
            && app()->environment('production');
    }
}
