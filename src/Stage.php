<?php

namespace JoelSeneque\StatamicStage;

use JoelSeneque\StatamicStage\Git\GitOperations;

class Stage
{
    public function __construct(protected GitOperations $git) {}

    public function hasUncommittedChanges(): bool
    {
        return $this->git->hasUncommittedChanges();
    }

    public function getStatus(): array
    {
        return $this->git->getStatus();
    }

    public function getCurrentBranch(): string
    {
        return $this->git->getCurrentBranch();
    }

    public function pushToProduction(?string $commitMessage = null): array
    {
        return $this->git->pushToProduction($commitMessage);
    }

    public function getRecentPushLogs(): array
    {
        // Return recent git log entries for merge commits
        return $this->git->getRecentMergeCommits();
    }

    public function getPendingCommits(): array
    {
        return $this->git->getPendingCommits();
    }

    public function hasPendingCommits(): bool
    {
        return $this->git->hasPendingCommits();
    }

    public function getBranchDiff(): array
    {
        return $this->git->getBranchDiff();
    }
}
