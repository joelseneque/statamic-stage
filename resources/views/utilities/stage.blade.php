@extends('statamic::layout')
@section('title', __('statamic-stage::messages.page_title'))

@section('content')
<div class="max-w-4xl" id="stage-app">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">{{ __('statamic-stage::messages.page_title') }}</h1>
    </div>

    <div class="card p-6 mb-6">
        <p class="text-gray-700 dark:text-gray-300 mb-6">
            {{ __('statamic-stage::messages.page_description') }}
        </p>

        {{-- Git Error --}}
        @if(!empty($gitError))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-200 font-bold mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ __('statamic-stage::messages.git_error_title') }}
                </div>
                <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                    {{ __('statamic-stage::messages.git_error_message') }}
                </p>
                <pre class="bg-red-100 dark:bg-red-900/40 text-red-900 dark:text-red-200 text-xs rounded p-3 overflow-x-auto whitespace-pre-wrap">{{ $gitError }}</pre>
            </div>
        @endif

        {{-- Branch Info --}}
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="flex-1 min-w-0 bg-gray-100 dark:bg-dark-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    {{ __('statamic-stage::messages.current_branch') }}
                </div>
                <div class="font-mono font-bold text-gray-900 dark:text-white">
                    {{ $currentBranch }}
                </div>
            </div>

            <div class="flex-1 min-w-0 bg-gray-100 dark:bg-dark-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    {{ __('statamic-stage::messages.staging_branch') }}
                </div>
                <div class="font-mono font-bold text-gray-900 dark:text-white">
                    {{ $config['staging_branch'] }}
                </div>
            </div>

            <div class="flex-1 min-w-0 bg-gray-100 dark:bg-dark-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    {{ __('statamic-stage::messages.production_branch') }}
                </div>
                <div class="font-mono font-bold text-gray-900 dark:text-white">
                    {{ $config['production_branch'] }}
                </div>
            </div>
        </div>

        {{-- Pending Changes (Branch Diff) --}}
        <div class="mb-6">
            <h2 class="font-bold text-lg mb-3 text-gray-800 dark:text-gray-100">
                {{ __('statamic-stage::messages.pending_changes') }}
            </h2>

            @if($hasPendingCommits || $branchDiff['counts']['total'] > 0)
                {{-- Pending Commits --}}
                @if(count($pendingCommits) > 0)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200 font-medium mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                            </svg>
                            {{ trans_choice('statamic-stage::messages.pending_commits_count', count($pendingCommits), ['count' => count($pendingCommits)]) }}
                        </div>
                        <div class="space-y-1 mt-3">
                            @foreach($pendingCommits as $commit)
                                <div class="flex items-center gap-2 text-sm">
                                    <code class="bg-blue-100 dark:bg-blue-800/50 px-2 py-0.5 rounded text-xs font-mono">
                                        {{ $commit['hash'] }}
                                    </code>
                                    <span class="text-gray-700 dark:text-gray-300">{{ $commit['message'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- File Changes --}}
                @if($branchDiff['counts']['total'] > 0)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200 font-medium mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            {{ trans_choice('statamic-stage::messages.files_changed', $branchDiff['counts']['total'], ['count' => $branchDiff['counts']['total']]) }}
                        </div>

                        <div class="flex gap-4 text-sm">
                            @if($branchDiff['counts']['added'] > 0)
                                <span class="text-green-600 dark:text-green-400">
                                    +{{ $branchDiff['counts']['added'] }} {{ __('statamic-stage::messages.added') }}
                                </span>
                            @endif
                            @if($branchDiff['counts']['modified'] > 0)
                                <span class="text-yellow-600 dark:text-yellow-400">
                                    ~{{ $branchDiff['counts']['modified'] }} {{ __('statamic-stage::messages.modified') }}
                                </span>
                            @endif
                            @if($branchDiff['counts']['deleted'] > 0)
                                <span class="text-red-600 dark:text-red-400">
                                    -{{ $branchDiff['counts']['deleted'] }} {{ __('statamic-stage::messages.deleted') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- File List --}}
                    @if(!empty($branchDiff['files']))
                        <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-4 mb-4 max-h-64 overflow-y-auto">
                            <div class="font-mono text-sm space-y-1">
                                @foreach($branchDiff['files'] as $file)
                                    <div class="flex items-center gap-2">
                                        @if($file['type'] === 'added')
                                            <span class="text-green-600 dark:text-green-400 w-4">A</span>
                                        @elseif($file['type'] === 'modified')
                                            <span class="text-yellow-600 dark:text-yellow-400 w-4">M</span>
                                        @elseif($file['type'] === 'deleted')
                                            <span class="text-red-600 dark:text-red-400 w-4">D</span>
                                        @else
                                            <span class="text-gray-500 w-4">?</span>
                                        @endif
                                        <span class="text-gray-700 dark:text-gray-300">{{ $file['file'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @else
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('statamic-stage::messages.branches_in_sync') }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Uncommitted Local Changes --}}
        @if($hasUncommittedChanges)
            <div class="mb-6">
                <h2 class="font-bold text-lg mb-3 text-gray-800 dark:text-gray-100">
                    {{ __('statamic-stage::messages.uncommitted_changes') }}
                </h2>
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-orange-800 dark:text-orange-200 font-medium mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ trans_choice('statamic-stage::messages.status_changes', $status['counts']['total'], ['count' => $status['counts']['total']]) }}
                    </div>
                    <p class="text-sm text-orange-700 dark:text-orange-300">
                        {{ __('statamic-stage::messages.uncommitted_will_be_committed') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Push Form --}}
        <form id="push-form" class="border-t border-gray-200 dark:border-dark-600 pt-6">
            @csrf
            <div class="mb-4">
                <label for="commit_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('statamic-stage::messages.commit_message_label') }}
                </label>
                <input
                    type="text"
                    name="commit_message"
                    id="commit_message"
                    class="input-text"
                    placeholder="{{ __('statamic-stage::messages.commit_message_placeholder') }}"
                >
            </div>

            @php
                $pushDisabled = (! $hasPendingCommits && ! $hasUncommittedChanges) || ! empty($gitError);
            @endphp
            <div class="flex flex-col gap-3">
                <button
                    type="submit"
                    id="push-button"
                    class="inline-flex w-full items-center justify-center gap-3 rounded-lg px-8 py-4 text-lg font-bold text-white shadow-lg transition disabled:cursor-not-allowed disabled:opacity-50"
                    style="background-color: #16a34a;"
                    onmouseover="if(!this.disabled) this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'"
                    @if($pushDisabled) disabled @endif
                >
                    <svg id="push-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <line x1="1.05" y1="12" x2="7" y2="12"/>
                        <line x1="17.01" y1="12" x2="22.96" y2="12"/>
                    </svg>
                    <svg id="push-spinner" class="animate-spin h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="push-button-text">{{ __('statamic-stage::messages.push_button') }}</span>
                </button>

                <div id="push-status" class="text-sm text-gray-500 dark:text-gray-400 hidden">
                    {{ __('statamic-stage::messages.push_in_progress') }}
                </div>

                @if(!empty($gitError))
                    <span class="text-sm text-red-600 dark:text-red-400">
                        {{ __('statamic-stage::messages.git_error_title') }}
                    </span>
                @elseif(!$hasPendingCommits && !$hasUncommittedChanges)
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('statamic-stage::messages.nothing_to_push') }}
                    </span>
                @endif
            </div>
        </form>

        {{-- Result Messages --}}
        <div id="push-result" class="mt-4 hidden"></div>
    </div>

    {{-- Recent Pushes --}}
    @if(!empty($recentPushes))
    <div class="card p-6">
        <h2 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100">
            {{ __('statamic-stage::messages.recent_pushes') }}
        </h2>

        <div class="space-y-2">
            @foreach($recentPushes as $push)
                <div class="flex items-center gap-3 text-sm">
                    <code class="bg-gray-100 dark:bg-dark-700 px-2 py-1 rounded text-xs">
                        {{ $push['hash'] }}
                    </code>
                    <span class="text-gray-700 dark:text-gray-300">
                        {{ $push['message'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>


<script>
(function () {
    // Bind the push handler once, via event delegation on `document`, instead of
    // directly on the form element. This is deliberately defensive about the
    // Statamic CP environment:
    //   - it doesn't matter whether DOMContentLoaded has already fired;
    //   - if the CP re-renders or replaces the form node after this script runs,
    //     a directly-bound listener would be left on the detached node and the
    //     live form would fall back to a native submit (a plain page reload with
    //     no confirmation) — delegation on `document` keeps working because the
    //     submit event still bubbles up from whatever #push-form is current.
    // Element lookups happen at submit time so they always reference live nodes.
    if (window.__stagePushBound) {
        return;
    }
    window.__stagePushBound = true;

    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (! form || form.id !== 'push-form') {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        const button = document.getElementById('push-button');
        const buttonText = document.getElementById('push-button-text');
        const pushIcon = document.getElementById('push-icon');
        const pushSpinner = document.getElementById('push-spinner');
        const status = document.getElementById('push-status');
        const result = document.getElementById('push-result');
        const commitMessageInput = document.getElementById('commit_message');

        if (button && button.disabled) {
            return;
        }

        if (! confirm('{{ __('statamic-stage::messages.push_confirm') }}')) {
            return;
        }

        const commitMessage = commitMessageInput ? commitMessageInput.value : '';

        // Update UI to loading state
        if (button) {
            button.disabled = true;
            button.classList.add('opacity-50');
        }
        if (buttonText) { buttonText.textContent = '{{ __('statamic-stage::messages.push_in_progress') }}'; }
        if (pushIcon) { pushIcon.classList.add('hidden'); }
        if (pushSpinner) { pushSpinner.classList.remove('hidden'); }
        if (status) { status.classList.remove('hidden'); }
        if (result) { result.classList.add('hidden'); }

        try {
            const url = '{{ cp_route('utilities.stage.push') }}';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    commit_message: commitMessage
                })
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 500));
                throw new Error('Server returned non-JSON response. Check Laravel logs.');
            }

            const data = await response.json();

            if (result) { result.classList.remove('hidden'); }

            if (data.success && result) {
                result.innerHTML = `
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            ${data.message}
                        </div>
                        ${data.log ? '<div class="mt-2 text-sm text-green-700 dark:text-green-300"><ul class="list-disc pl-5">' + data.log.map(l => '<li>' + l + '</li>').join('') + '</ul></div>' : ''}
                    </div>
                `;
            }

            if (data.success) {
                setTimeout(() => window.location.reload(), 3000);
            } else if (result) {
                result.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-red-800 dark:text-red-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            ${data.message || 'Push failed'}
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Push error:', error);
            if (result) {
                result.classList.remove('hidden');
                result.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="text-red-800 dark:text-red-200">
                            <strong>Error:</strong> ${error.message || 'An error occurred. Please try again.'}
                        </div>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            Check the browser console and Laravel logs for more details.
                        </div>
                    </div>
                `;
            }
        } finally {
            // Reset button state
            if (button) {
                button.disabled = false;
                button.classList.remove('opacity-50');
            }
            if (buttonText) { buttonText.textContent = '{{ __('statamic-stage::messages.push_button') }}'; }
            if (pushIcon) { pushIcon.classList.remove('hidden'); }
            if (pushSpinner) { pushSpinner.classList.add('hidden'); }
            if (status) { status.classList.add('hidden'); }
        }
    });
})();
</script>
@endsection
