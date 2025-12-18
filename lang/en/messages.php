<?php

return [

    // Permissions
    'permission_label' => 'Push to Production',
    'permission_description' => 'Ability to push staging content to production',

    // Navigation & Utility
    'utility_title' => 'Staging',
    'utility_description' => 'Push staging content to live production',
    'nav_title' => 'Push to Production',

    // Widget
    'widget_title' => 'Push to Production',
    'widget_no_changes' => 'No pending changes',
    'widget_changes_count' => ':count pending change|:count pending changes',
    'widget_push_button' => 'Push to Production',

    // Utility Page
    'page_title' => 'Push to Production',
    'page_description' => 'Review pending changes and push them to the production site.',
    'current_branch' => 'Current Branch',
    'staging_branch' => 'Staging Branch',
    'production_branch' => 'Production Branch',
    'pending_changes' => 'Pending Changes',
    'pending_commits_count' => ':count commit ready to merge|:count commits ready to merge',
    'files_changed' => ':count file changed|:count files changed',
    'branches_in_sync' => 'Staging and production are in sync',
    'uncommitted_changes' => 'Uncommitted Local Changes',
    'uncommitted_will_be_committed' => 'These changes will be committed before pushing.',
    'status_clean' => 'No uncommitted changes',
    'status_changes' => ':count uncommitted change|:count uncommitted changes',
    'commit_message_label' => 'Commit Message (optional)',
    'commit_message_placeholder' => 'Content update from staging',
    'push_button' => 'Push to Live Production',
    'push_confirm' => 'Are you sure you want to push all changes to production? This will trigger a deployment.',
    'nothing_to_push' => 'Nothing to push - branches are in sync',
    'recent_pushes' => 'Recent Pushes',
    'no_recent_pushes' => 'No recent pushes',

    // Status Types
    'added' => 'Added',
    'modified' => 'Modified',
    'deleted' => 'Deleted',

    // Success Messages
    'push_successful' => 'Changes have been pushed to production successfully!',
    'push_in_progress' => 'Push to production is in progress...',

    // Error Messages
    'push_failed' => 'Push failed: :error',
    'merge_conflict' => 'Merge conflict detected in: :files. Please resolve conflicts manually.',
    'not_on_staging_branch' => 'You must be on the staging branch to push to production.',
    'push_locked' => 'A push is already in progress. Please wait.',

    // Read-Only Mode
    'read_only_banner' => 'Read-Only Mode',
    'read_only_message' => 'Content editing is disabled on production. Make changes on the staging site.',
    'read_only_blocked' => 'Content cannot be saved in production. Please make changes on the staging site.',

    // Commands
    'command_push_description' => 'Push staging content to production',
    'command_status_description' => 'Show staging content status',

];
