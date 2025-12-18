<?php

namespace JoelSeneque\StatamicStage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasUncommittedChanges()
 * @method static array getStatus()
 * @method static string getCurrentBranch()
 * @method static array pushToProduction(?string $commitMessage = null)
 * @method static array getRecentPushLogs()
 *
 * @see \JoelSeneque\StatamicStage\Stage
 */
class Stage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JoelSeneque\StatamicStage\Stage::class;
    }
}
