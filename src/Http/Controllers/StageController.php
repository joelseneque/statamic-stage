<?php

namespace JoelSeneque\StatamicStage\Http\Controllers;

use Illuminate\Http\Request;
use JoelSeneque\StatamicStage\Events\PushToProductionCompleted;
use JoelSeneque\StatamicStage\Events\PushToProductionFailed;
use JoelSeneque\StatamicStage\Events\PushToProductionStarted;
use JoelSeneque\StatamicStage\Facades\Stage;
use JoelSeneque\StatamicStage\Git\Exceptions\GitConflictException;
use Statamic\Http\Controllers\CP\CpController;

class StageController extends CpController
{
    public function index()
    {
        abort_unless(auth()->user()?->can('push to production'), 403);

        return view('statamic-stage::utilities.stage', [
            'status' => Stage::getStatus(),
            'currentBranch' => Stage::getCurrentBranch(),
            'hasUncommittedChanges' => Stage::hasUncommittedChanges(),
            'pendingCommits' => Stage::getPendingCommits(),
            'branchDiff' => Stage::getBranchDiff(),
            'hasPendingCommits' => Stage::hasPendingCommits(),
            'config' => [
                'staging_branch' => config('statamic-stage.branches.staging'),
                'production_branch' => config('statamic-stage.branches.production'),
            ],
            'recentPushes' => Stage::getRecentPushLogs(),
        ]);
    }

    public function push(Request $request)
    {
        abort_unless(auth()->user()?->can('push to production'), 403);

        $request->validate([
            'commit_message' => 'nullable|string|max:255',
        ]);

        event(new PushToProductionStarted(auth()->user()));

        try {
            $log = Stage::pushToProduction($request->commit_message);

            event(new PushToProductionCompleted(auth()->user(), $log));

            return response()->json([
                'success' => true,
                'message' => __('statamic-stage::messages.push_successful'),
                'log' => $log,
            ]);
        } catch (GitConflictException $e) {
            event(new PushToProductionFailed(auth()->user(), $e->getMessage()));

            return response()->json([
                'success' => false,
                'message' => __('statamic-stage::messages.merge_conflict', [
                    'files' => $e->getConflictingFiles(),
                ]),
            ], 422);
        } catch (\Exception $e) {
            event(new PushToProductionFailed(auth()->user(), $e->getMessage()));

            return response()->json([
                'success' => false,
                'message' => __('statamic-stage::messages.push_failed', [
                    'error' => $e->getMessage(),
                ]),
            ], 500);
        }
    }

    public function status()
    {
        return response()->json([
            'has_changes' => Stage::hasUncommittedChanges(),
            'status' => Stage::getStatus(),
            'current_branch' => Stage::getCurrentBranch(),
        ]);
    }
}
