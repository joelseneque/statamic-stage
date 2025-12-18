<?php

namespace JoelSeneque\StatamicStage\Commands;

use Illuminate\Console\Command;
use JoelSeneque\StatamicStage\Events\PushToProductionCompleted;
use JoelSeneque\StatamicStage\Events\PushToProductionFailed;
use JoelSeneque\StatamicStage\Events\PushToProductionStarted;
use JoelSeneque\StatamicStage\Facades\Stage;
use JoelSeneque\StatamicStage\Git\Exceptions\GitConflictException;
use Statamic\Console\RunsInPlease;

class PushToProduction extends Command
{
    use RunsInPlease;

    protected $signature = 'stage:push
                            {--message= : Custom commit message}
                            {--force : Skip confirmation}';

    protected $description = 'Push staging content to production';

    public function handle(): int
    {
        $allowedEnvironments = config('statamic-stage.environments.show_push_button', ['local', 'staging']);

        if (! in_array(app()->environment(), $allowedEnvironments)) {
            $this->error('This command can only be run in staging/local environments.');

            return self::FAILURE;
        }

        $status = Stage::getStatus();

        $this->info('Current branch: '.Stage::getCurrentBranch());
        $this->newLine();

        if ($status['counts']['total'] === 0) {
            $this->info('No uncommitted changes to push.');

            if (! $this->option('force') && ! $this->confirm('Do you still want to merge staging to production?')) {
                return self::SUCCESS;
            }
        } else {
            $this->info('Changes to be pushed:');
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

            $this->newLine();

            if (! $this->option('force') && ! $this->confirm('Do you want to push these changes to production?')) {
                return self::SUCCESS;
            }
        }

        event(new PushToProductionStarted(null));

        try {
            $this->info('Pushing to production...');
            $this->newLine();

            $log = Stage::pushToProduction($this->option('message'));

            event(new PushToProductionCompleted(null, $log));

            $this->info('Push completed successfully!');
            $this->newLine();

            foreach ($log as $entry) {
                $this->line("  - {$entry}");
            }

            return self::SUCCESS;
        } catch (GitConflictException $e) {
            event(new PushToProductionFailed(null, $e->getMessage()));

            $this->error('Merge conflict detected!');
            $this->error('Conflicting files:');
            $this->line($e->getConflictingFiles());

            return self::FAILURE;
        } catch (\Exception $e) {
            event(new PushToProductionFailed(null, $e->getMessage()));

            $this->error("Push failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
