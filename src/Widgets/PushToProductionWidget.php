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

        // Fetch the latest remote refs before comparing branches. On a
        // single-branch clone (e.g. Forge) the remote-tracking refs won't
        // exist until fetched, otherwise getPendingCommits() fails with
        // "unknown revision" (git exit code 128).
        Stage::fetchRemote();

        $pendingCommits = Stage::getPendingCommits();

        return view('statamic-stage::widgets.push-to-production', [
            'hasPendingCommits' => count($pendingCommits) > 0,
            'commitsCount' => count($pendingCommits),
            'canPush' => auth()->user()?->can('push to production'),
            'gitError' => Stage::getLastError(),
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
