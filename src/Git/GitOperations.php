<?php

namespace JoelSeneque\StatamicStage\Git;

use Illuminate\Support\Collection;
use JoelSeneque\StatamicStage\Git\Exceptions\GitConflictException;
use JoelSeneque\StatamicStage\Git\Exceptions\GitOperationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitOperations
{
    protected string $basePath;

    protected array $trackedPaths;

    protected string $gitBinary;

    protected bool $trackAllChanges;

    public function __construct()
    {
        $this->basePath = base_path();
        $this->trackAllChanges = config('statamic-stage.track_all_changes', false);
        $this->trackedPaths = config('statamic-stage.tracked_paths', [
            'content',
            'resources/blueprints',
            'resources/fieldsets',
            'resources/forms',
            'public/assets',
        ]);
        $this->gitBinary = config('statamic-stage.git.binary', 'git');
    }

    public function hasUncommittedChanges(): bool
    {
        $status = $this->getStatusOutput();

        return ! empty(trim($status));
    }

    public function getStatus(): array
    {
        $status = $this->getStatusOutput();
        $lines = collect(explode("\n", $status))->filter()->values();

        return [
            'raw' => $status,
            'files' => $this->parseStatusFiles($lines),
            'counts' => [
                'total' => $lines->count(),
                'added' => $lines->filter(fn ($l) => str_starts_with($l, 'A ') || str_starts_with($l, '??'))->count(),
                'modified' => $lines->filter(fn ($l) => str_starts_with($l, 'M ') || str_starts_with($l, ' M'))->count(),
                'deleted' => $lines->filter(fn ($l) => str_starts_with($l, 'D ') || str_starts_with($l, ' D'))->count(),
            ],
        ];
    }

    protected function getStatusOutput(): string
    {
        // If tracking all changes, don't filter by paths
        if ($this->trackAllChanges) {
            return $this->run([$this->gitBinary, 'status', '--porcelain']);
        }

        $existingPaths = collect($this->trackedPaths)
            ->filter(fn ($path) => file_exists(base_path($path)))
            ->values()
            ->toArray();

        if (empty($existingPaths)) {
            return '';
        }

        return $this->run([$this->gitBinary, 'status', '--porcelain', ...$existingPaths]);
    }

    protected function parseStatusFiles(Collection $lines): array
    {
        return $lines->map(function ($line) {
            $status = substr($line, 0, 2);
            $file = trim(substr($line, 3));

            $type = match (true) {
                str_contains($status, 'A') || $status === '??' => 'added',
                str_contains($status, 'M') => 'modified',
                str_contains($status, 'D') => 'deleted',
                default => 'unknown',
            };

            return [
                'file' => $file,
                'type' => $type,
                'status' => trim($status),
            ];
        })->toArray();
    }

    public function getCurrentBranch(): string
    {
        return trim($this->run([$this->gitBinary, 'rev-parse', '--abbrev-ref', 'HEAD']));
    }

    public function commitChanges(string $message): void
    {
        // Stage changes based on tracking mode
        if ($this->trackAllChanges) {
            // Stage all changes
            $this->run([$this->gitBinary, 'add', '-A']);
        } else {
            // Stage only tracked content paths that exist
            foreach ($this->trackedPaths as $path) {
                $fullPath = base_path($path);
                if (file_exists($fullPath)) {
                    $this->run([$this->gitBinary, 'add', $path]);
                }
            }
        }

        // Check if there's anything staged
        $stagedChanges = $this->run([$this->gitBinary, 'diff', '--cached', '--name-only']);
        if (empty(trim($stagedChanges))) {
            return;
        }

        // Commit with configured user
        $userName = config('statamic-stage.git.user.name', 'Statamic Stage');
        $userEmail = config('statamic-stage.git.user.email', 'stage@statamic.local');

        $this->run([
            $this->gitBinary,
            '-c', "user.name={$userName}",
            '-c', "user.email={$userEmail}",
            'commit', '-m', $message,
        ]);
    }

    public function pushToStagingBranch(): void
    {
        $stagingBranch = config('statamic-stage.branches.staging', 'staging');
        $remote = config('statamic-stage.git.remote', 'origin');
        $currentBranch = $this->getCurrentBranch();

        // Ensure we're on staging branch
        if ($currentBranch !== $stagingBranch) {
            throw new GitOperationException(
                "Must be on {$stagingBranch} branch to push. Currently on: {$currentBranch}"
            );
        }

        // Push to remote
        $this->run([$this->gitBinary, 'push', $remote, $stagingBranch]);
    }

    public function mergeToProduction(): void
    {
        $stagingBranch = config('statamic-stage.branches.staging', 'staging');
        $productionBranch = config('statamic-stage.branches.production', 'main');
        $remote = config('statamic-stage.git.remote', 'origin');

        // Fetch latest from remote
        $this->run([$this->gitBinary, 'fetch', $remote]);

        // Checkout production branch
        $this->run([$this->gitBinary, 'checkout', $productionBranch]);

        // Pull latest production changes
        $this->run([$this->gitBinary, 'pull', $remote, $productionBranch]);

        // Merge staging into production
        try {
            $userName = config('statamic-stage.git.user.name', 'Statamic Stage');
            $userEmail = config('statamic-stage.git.user.email', 'stage@statamic.local');

            $this->run([
                $this->gitBinary,
                '-c', "user.name={$userName}",
                '-c', "user.email={$userEmail}",
                'merge', $stagingBranch,
                '--no-edit',
                '-m', $this->getMergeCommitMessage(),
            ]);
        } catch (GitOperationException $e) {
            // Check for conflicts
            $conflicts = $this->run([$this->gitBinary, 'diff', '--name-only', '--diff-filter=U']);
            if (! empty(trim($conflicts))) {
                // Abort the merge
                $this->run([$this->gitBinary, 'merge', '--abort']);
                // Return to staging branch
                $this->run([$this->gitBinary, 'checkout', $stagingBranch]);

                throw new GitConflictException(
                    'Merge conflict detected',
                    $conflicts
                );
            }

            // Return to staging branch before re-throwing
            $this->run([$this->gitBinary, 'checkout', $stagingBranch]);

            throw $e;
        }

        // Push to production
        $this->run([$this->gitBinary, 'push', $remote, $productionBranch]);

        // Return to staging branch
        $this->run([$this->gitBinary, 'checkout', $stagingBranch]);
    }

    public function pushToProduction(?string $commitMessage = null): array
    {
        $log = [];

        // Step 1: Commit any uncommitted changes
        if ($this->hasUncommittedChanges()) {
            $message = $commitMessage ?? config('statamic-stage.commit_message', 'Content update from staging');
            $this->commitChanges($message);
            $log[] = "Committed pending changes: {$message}";
        }

        // Step 2: Push to staging branch
        $this->pushToStagingBranch();
        $log[] = 'Pushed to staging branch';

        // Step 3: Merge staging to production
        $this->mergeToProduction();
        $log[] = 'Merged staging to production and pushed';

        return $log;
    }

    public function getRecentMergeCommits(int $limit = 5): array
    {
        $productionBranch = config('statamic-stage.branches.production', 'main');

        try {
            $output = $this->run([
                $this->gitBinary, 'log',
                $productionBranch,
                '--merges',
                '--oneline',
                "-{$limit}",
            ]);

            return collect(explode("\n", $output))
                ->filter()
                ->map(function ($line) {
                    $parts = explode(' ', $line, 2);

                    return [
                        'hash' => $parts[0] ?? '',
                        'message' => $parts[1] ?? '',
                    ];
                })
                ->toArray();
        } catch (GitOperationException) {
            return [];
        }
    }

    /**
     * Get commits that are on staging but not on production (pending to be merged).
     */
    public function getPendingCommits(): array
    {
        $stagingBranch = config('statamic-stage.branches.staging', 'staging');
        $productionBranch = config('statamic-stage.branches.production', 'main');
        $remote = config('statamic-stage.git.remote', 'origin');

        try {
            // Fetch latest from remote to ensure we have up-to-date refs
            $this->run([$this->gitBinary, 'fetch', $remote]);

            // Get commits that are in staging but not in main
            // Using remote refs to compare what's actually on the server
            $output = $this->run([
                $this->gitBinary, 'log',
                "{$remote}/{$productionBranch}..{$remote}/{$stagingBranch}",
                '--oneline',
                '--no-merges',
            ]);

            return collect(explode("\n", $output))
                ->filter()
                ->map(function ($line) {
                    $parts = explode(' ', $line, 2);

                    return [
                        'hash' => $parts[0] ?? '',
                        'message' => $parts[1] ?? '',
                    ];
                })
                ->values()
                ->toArray();
        } catch (GitOperationException) {
            return [];
        }
    }

    /**
     * Check if there are pending commits to merge from staging to production.
     */
    public function hasPendingCommits(): bool
    {
        return count($this->getPendingCommits()) > 0;
    }

    /**
     * Get the diff of files between staging and production branches.
     */
    public function getBranchDiff(): array
    {
        $stagingBranch = config('statamic-stage.branches.staging', 'staging');
        $productionBranch = config('statamic-stage.branches.production', 'main');
        $remote = config('statamic-stage.git.remote', 'origin');

        try {
            // Fetch to ensure we have latest refs
            $this->run([$this->gitBinary, 'fetch', $remote]);

            // Get files that differ between production and staging
            $output = $this->run([
                $this->gitBinary, 'diff',
                '--name-status',
                "{$remote}/{$productionBranch}...{$remote}/{$stagingBranch}",
            ]);

            $lines = collect(explode("\n", $output))->filter()->values();

            $files = $lines->map(function ($line) {
                // Format: "M\tpath/to/file" or "A\tpath/to/file"
                $parts = preg_split('/\s+/', $line, 2);
                $status = $parts[0] ?? '';
                $file = $parts[1] ?? '';

                $type = match ($status) {
                    'A' => 'added',
                    'M' => 'modified',
                    'D' => 'deleted',
                    'R' => 'renamed',
                    default => 'unknown',
                };

                return [
                    'file' => $file,
                    'type' => $type,
                    'status' => $status,
                ];
            })->toArray();

            return [
                'files' => $files,
                'counts' => [
                    'total' => count($files),
                    'added' => collect($files)->where('type', 'added')->count(),
                    'modified' => collect($files)->where('type', 'modified')->count(),
                    'deleted' => collect($files)->where('type', 'deleted')->count(),
                ],
            ];
        } catch (GitOperationException) {
            return [
                'files' => [],
                'counts' => ['total' => 0, 'added' => 0, 'modified' => 0, 'deleted' => 0],
            ];
        }
    }

    protected function getMergeCommitMessage(): string
    {
        $template = config(
            'statamic-stage.merge_message',
            'Merge staging to production - {date} by {user}'
        );

        return strtr($template, [
            '{date}' => now()->toDateTimeString(),
            '{user}' => auth()->user()?->name() ?? 'System',
        ]);
    }

    protected function run(array $command): string
    {
        $process = new Process($command, $this->basePath);
        $process->setTimeout(120);

        try {
            $process->mustRun();

            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new GitOperationException(
                $e->getMessage(),
                implode(' ', $command),
                $process->getErrorOutput()
            );
        }
    }
}
