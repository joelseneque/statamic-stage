<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment Settings
    |--------------------------------------------------------------------------
    |
    | Define which environments should show the push-to-production interface.
    | Typically this includes 'local' and 'staging' environments.
    |
    */

    'environments' => [
        'show_push_button' => ['local', 'staging'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Read-Only Production Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, content editing is disabled on the production environment.
    | This prevents accidental edits to production content, ensuring all
    | changes flow through the staging workflow.
    |
    */

    'read_only_on_production' => env('STATAMIC_STAGE_READ_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Git Branch Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the branch names used for staging and production. The staging
    | branch is where content edits are made, and changes are merged to the
    | production branch when pushing to production.
    |
    */

    'branches' => [
        'staging' => env('STATAMIC_STAGE_BRANCH', 'staging'),
        'production' => env('STATAMIC_STAGE_PRODUCTION_BRANCH', 'main'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Git Settings
    |--------------------------------------------------------------------------
    |
    | Configure git-specific settings including the remote name, binary path,
    | and the user information used for commits made by the addon.
    |
    */

    'git' => [
        'remote' => env('STATAMIC_STAGE_REMOTE', 'origin'),
        'binary' => env('STATAMIC_STAGE_GIT_BINARY', 'git'),
        'user' => [
            'name' => env('STATAMIC_STAGE_GIT_NAME', 'Statamic Stage'),
            'email' => env('STATAMIC_STAGE_GIT_EMAIL', 'stage@statamic.local'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub API Settings
    |--------------------------------------------------------------------------
    |
    | Configure GitHub API access for merge operations. The token needs
    | 'repo' scope to create merges. The repo should be in 'owner/repo' format.
    |
    | Create a token at: https://github.com/settings/tokens
    | Required scope: repo (Full control of private repositories)
    |
    */

    'github' => [
        'token' => env('STATAMIC_STAGE_GITHUB_TOKEN'),
        'repo' => env('STATAMIC_STAGE_GITHUB_REPO'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Track All Changes
    |--------------------------------------------------------------------------
    |
    | When enabled, the addon will track ALL git changes in the repository,
    | not just the content paths defined below. This is useful for local
    | development where you want to push code + content changes together.
    |
    | Set to true for local development, false for staging (content-only).
    |
    */

    'track_all_changes' => env('STATAMIC_STAGE_TRACK_ALL', false),

    /*
    |--------------------------------------------------------------------------
    | Tracked Paths
    |--------------------------------------------------------------------------
    |
    | Define which paths should be tracked for changes. These paths are
    | monitored for uncommitted changes and are staged when committing.
    | Paths are relative to the application's base path.
    |
    | Note: This is ignored when 'track_all_changes' is set to true.
    |
    */

    'tracked_paths' => [
        'content',
        'resources/blueprints',
        'resources/fieldsets',
        'resources/forms',
        'resources/users',
        'public/assets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commit Messages
    |--------------------------------------------------------------------------
    |
    | Configure the default commit message used when auto-committing changes
    | and the merge commit message template. Available placeholders for
    | merge message: {date}, {user}
    |
    */

    'commit_message' => env('STATAMIC_STAGE_COMMIT_MSG', 'Content update from staging'),

    'merge_message' => env('STATAMIC_STAGE_MERGE_MSG', 'Merge staging to production - {date} by {user}'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure activity logging for push operations. When enabled, all
    | push attempts (successful and failed) are logged to the specified
    | Laravel log channel.
    |
    */

    'logging' => [
        'enabled' => env('STATAMIC_STAGE_LOGGING', true),
        'channel' => env('STATAMIC_STAGE_LOG_CHANNEL', null),
    ],

];
