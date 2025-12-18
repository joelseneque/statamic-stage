<?php

namespace JoelSeneque\StatamicStage\Commands;

use Illuminate\Console\Command;
use JoelSeneque\StatamicStage\Facades\Stage;
use Statamic\Console\RunsInPlease;

class StageStatus extends Command
{
    use RunsInPlease;

    protected $signature = 'stage:status';

    protected $description = 'Show staging content status';

    public function handle(): int
    {
        $status = Stage::getStatus();
        $currentBranch = Stage::getCurrentBranch();

        $this->info("Current branch: {$currentBranch}");
        $this->info('Staging branch: '.config('statamic-stage.branches.staging'));
        $this->info('Production branch: '.config('statamic-stage.branches.production'));
        $this->newLine();

        if ($status['counts']['total'] === 0) {
            $this->info('No uncommitted changes.');
        } else {
            $this->table(
                ['Type', 'Count'],
                [
                    ['Total', $status['counts']['total']],
                    ['Added', $status['counts']['added']],
                    ['Modified', $status['counts']['modified']],
                    ['Deleted', $status['counts']['deleted']],
                ]
            );

            $this->newLine();
            $this->info('Changed files:');
            $this->newLine();

            foreach ($status['files'] as $file) {
                $prefix = match ($file['type']) {
                    'added' => '<fg=green>A</>',
                    'modified' => '<fg=yellow>M</>',
                    'deleted' => '<fg=red>D</>',
                    default => '<fg=gray>?</>',
                };
                $this->line("  {$prefix} {$file['file']}");
            }
        }

        return self::SUCCESS;
    }
}
