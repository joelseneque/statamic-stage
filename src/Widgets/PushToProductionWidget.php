<?php

namespace JoelSeneque\StatamicStage\Widgets;

use JoelSeneque\StatamicStage\Facades\Stage;
use Statamic\Widgets\Widget;

class PushToProductionWidget extends Widget
{
    protected static $handle = 'push_to_production';

    public function html()
    {
        if (! $this->shouldShow()) {
            return '';
        }

        $pendingCommits = Stage::getPendingCommits();

        return view('statamic-stage::widgets.push-to-production', [
            'hasPendingCommits' => Stage::hasPendingCommits(),
            'commitsCount' => count($pendingCommits),
            'canPush' => auth()->user()?->can('push to production'),
        ])->render();
    }

    protected function shouldShow(): bool
    {
        return in_array(
            app()->environment(),
            config('statamic-stage.environments.show_push_button', ['local', 'staging'])
        );
    }
}
