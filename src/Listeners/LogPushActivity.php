<?php

namespace JoelSeneque\StatamicStage\Listeners;

use Illuminate\Support\Facades\Log;
use JoelSeneque\StatamicStage\Events\PushToProductionCompleted;
use JoelSeneque\StatamicStage\Events\PushToProductionFailed;
use JoelSeneque\StatamicStage\Events\PushToProductionStarted;

class LogPushActivity
{
    public function handle(object $event): void
    {
        if (! config('statamic-stage.logging.enabled', true)) {
            return;
        }

        $channel = config('statamic-stage.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $userName = $event->user?->name() ?? 'CLI/System';

        match (true) {
            $event instanceof PushToProductionStarted => $logger->info(
                "[Statamic Stage] Push to production started by {$userName}"
            ),
            $event instanceof PushToProductionCompleted => $logger->info(
                "[Statamic Stage] Push to production completed by {$userName}",
                ['log' => $event->log]
            ),
            $event instanceof PushToProductionFailed => $logger->error(
                "[Statamic Stage] Push to production failed for {$userName}",
                ['error' => $event->error]
            ),
            default => null,
        };
    }
}
