<div class="card p-4">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h2 class="font-bold text-gray-800 dark:text-gray-100">
                {{ __('statamic-stage::messages.widget_title') }}
            </h2>

            @if($hasPendingCommits)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ trans_choice('statamic-stage::messages.pending_commits_count', $commitsCount, ['count' => $commitsCount]) }}
                </p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                    {{ __('statamic-stage::messages.branches_in_sync') }}
                </p>
            @endif
        </div>

        <div class="ml-4">
            @if($canPush)
                <a href="{{ cp_route('utilities.stage') }}"
                   class="btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <line x1="1.05" y1="12" x2="7" y2="12"/>
                        <line x1="17.01" y1="12" x2="22.96" y2="12"/>
                    </svg>
                    {{ __('statamic-stage::messages.widget_push_button') }}
                </a>
            @endif
        </div>
    </div>
</div>
