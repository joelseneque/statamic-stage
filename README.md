# Statamic Stage

A Statamic addon that provides a staging-to-production workflow with a one-click "Push to Production" button and read-only production mode.

## Features

- **Push to Production Button** - One-click merge from staging branch to main/production branch
- **Read-Only Production Mode** - Prevents content editing on production environment
- **Dashboard Widget** - Shows pending changes count with quick access to push
- **Artisan Commands** - CLI commands for automation and scripting
- **Permission System** - Role-based access control for the push feature
- **Activity Logging** - All push attempts are logged

## Requirements

- PHP 8.2+
- Statamic 5.0+
- **Statamic Pro** (required for Git Automation on staging and multi-user support)
- Git installed on the server
- SSH keys or credentials configured for git push access

## Installation

```bash
composer require joelseneque/statamic-stage
```

### Publish Configuration (Optional)

If you want to customize the configuration per project, publish the config file:

```bash
php artisan vendor:publish --tag=statamic-stage-config
```

This will create `config/statamic-stage.php` in your project where you can customize:
- Tracked paths
- Branch names
- Commit message templates
- And more...

Most settings can also be configured via environment variables (see Configuration section below).

## How It Works

This addon is designed to work alongside **Statamic's built-in Git Automation** feature (requires Pro). The workflow is:

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   LOCAL     │     │   STAGING   │     │ PRODUCTION  │
│             │     │             │     │             │
│ Development │────▶│ Content     │────▶│ Live Site   │
│ & Code      │push │ Editing     │merge│ Read-Only   │
└─────────────┘     └─────────────┘     └─────────────┘
                           │                   │
                    Git Automation      This Addon
                    (auto-commits)      (blocks edits)
```

1. **Local**: Developers work on code, push to `staging` branch
2. **Staging**: Content editors make changes in the CP. Statamic Git Automation auto-commits and pushes to `staging` branch
3. **Production**: Read-only mode. Changes are deployed by merging `staging` → `main` via the "Push to Production" button

## Setup Guide

### 1. Create the Staging Branch

```bash
# From your local development environment
git checkout -b staging
git push -u origin staging
git checkout main
```

### 2. Configure Laravel Forge (or your deployment platform)

#### Staging Site
- **Repository branch**: `staging`
- **Deploy when code is pushed**: Yes (auto-deploy on push to staging)

#### Production Site
- **Repository branch**: `main`
- **Deploy when code is pushed**: Yes (auto-deploy on push to main)

### 3. Environment Configuration

#### Local Environment (`.env`)
```env
APP_ENV=local

# Track all git changes (code + content), not just content paths
STATAMIC_STAGE_TRACK_ALL=true
```

#### Staging Environment (`.env`)
```env
APP_ENV=staging

# Enable Statamic Git Automation (requires Pro)
STATAMIC_GIT_ENABLED=true
STATAMIC_GIT_AUTOMATIC=true
STATAMIC_GIT_PUSH=true

# Optional: Use queue for commits (recommended)
STATAMIC_GIT_QUEUE_CONNECTION=redis
```

#### Production Environment (`.env`)
```env
APP_ENV=production

# Disable Git Automation on production
STATAMIC_GIT_ENABLED=false

# Enable read-only mode (default: true)
STATAMIC_STAGE_READ_ONLY=true
```

### 4. Configure Git User (Optional)

Set the git user for commits made by the addon:

```env
STATAMIC_STAGE_GIT_NAME="Statamic Stage"
STATAMIC_STAGE_GIT_EMAIL="stage@yourdomain.com"
```

### 5. Set Up Permissions

1. Go to **Statamic CP → Users → Roles**
2. Edit the role(s) that should have push access
3. Enable the **"Push to Production"** permission under "Statamic Stage"

### 6. Configure SSH Keys on Staging Server

The staging server needs to be able to push to your git repository. On Laravel Forge:

1. Go to your staging server in Forge
2. Navigate to **SSH Keys**
3. Copy the server's public key
4. Add it as a deploy key (with write access) in your GitHub/GitLab/Bitbucket repository

Alternatively, if using HTTPS:
```bash
# Store credentials (run on staging server)
git config --global credential.helper store
```

## Configuration

The configuration file is located at `config/statamic-stage.php`:

```php
return [
    // Environments where the push button is shown
    'environments' => [
        'show_push_button' => ['local', 'staging'],
    ],

    // Block content editing on production
    'read_only_on_production' => env('STATAMIC_STAGE_READ_ONLY', true),

    // Track ALL git changes (true) or just content paths (false)
    // Set to true for local dev where you push code + content together
    'track_all_changes' => env('STATAMIC_STAGE_TRACK_ALL', false),

    // Branch names
    'branches' => [
        'staging' => env('STATAMIC_STAGE_BRANCH', 'staging'),
        'production' => env('STATAMIC_STAGE_PRODUCTION_BRANCH', 'main'),
    ],

    // Git settings
    'git' => [
        'remote' => env('STATAMIC_STAGE_REMOTE', 'origin'),
        'binary' => env('STATAMIC_STAGE_GIT_BINARY', 'git'),
        'user' => [
            'name' => env('STATAMIC_STAGE_GIT_NAME', 'Statamic Stage'),
            'email' => env('STATAMIC_STAGE_GIT_EMAIL', 'stage@statamic.local'),
        ],
    ],

    // Paths to track for changes
    'tracked_paths' => [
        'content',
        'resources/blueprints',
        'resources/fieldsets',
        'resources/forms',
        'resources/users',
        'public/assets',
    ],

    // Commit messages
    'commit_message' => env('STATAMIC_STAGE_COMMIT_MSG', 'Content update from staging'),
    'merge_message' => env('STATAMIC_STAGE_MERGE_MSG', 'Merge staging to production - {date} by {user}'),

    // Logging
    'logging' => [
        'enabled' => env('STATAMIC_STAGE_LOGGING', true),
        'channel' => env('STATAMIC_STAGE_LOG_CHANNEL', null),
    ],
];
```

## Usage

### Control Panel

1. Navigate to **Tools → Push to Production** in the Statamic CP
2. Review the pending changes
3. Optionally enter a custom commit message
4. Click **"Push to Live Production"**

The addon will:
1. Commit any uncommitted changes on staging
2. Push to the staging branch
3. Merge staging into the production branch
4. Push to trigger the production deployment

### Artisan Commands

```bash
# Show current staging status
php artisan stage:status

# Push to production (interactive)
php artisan stage:push

# Push to production with custom message
php artisan stage:push --message="Monthly content update"

# Push without confirmation (for scripts/CI)
php artisan stage:push --force
```

### Dashboard Widget

Add the widget to your CP dashboard by editing `config/statamic/cp.php`:

```php
'widgets' => [
    // ... other widgets
    'push_to_production',
],
```

## Workflow Examples

### Daily Content Updates

1. Content editor logs into staging site
2. Makes changes to entries, globals, etc.
3. Statamic Git Automation auto-commits and pushes to `staging`
4. When ready, clicks "Push to Production"
5. Changes are merged to `main` and auto-deployed

### Scheduled Deployments

Use the Artisan command in a scheduled task:

```php
// app/Console/Kernel.php or routes/console.php
Schedule::command('stage:push --force')
    ->weeklyOn(1, '9:00')  // Every Monday at 9am
    ->environments(['staging']);
```

### CI/CD Integration

```yaml
# GitHub Actions example
- name: Push staging to production
  run: php artisan stage:push --force
  if: github.ref == 'refs/heads/staging'
```

## Troubleshooting

### "Merge conflict detected"

If you see this error, there are conflicting changes between staging and production branches. This usually happens if someone pushed directly to main.

**Resolution:**
1. SSH into your staging server
2. Manually resolve the conflict:
   ```bash
   cd /path/to/site
   git fetch origin
   git checkout main
   git merge staging
   # Resolve conflicts
   git add .
   git commit
   git push origin main
   git checkout staging
   ```

### "Permission denied" on git push

The server doesn't have write access to the repository.

**Resolution:**
1. Ensure SSH keys are set up correctly
2. Verify the deploy key has write access
3. Test manually: `ssh -T git@github.com`

### Changes not appearing after push

The production site may need to clear its cache:

```bash
php artisan cache:clear
php artisan statamic:stache:clear
```

Or configure your deployment script to run these automatically.

## Events

The addon dispatches events you can listen to:

```php
use JoelSeneque\StatamicStage\Events\PushToProductionStarted;
use JoelSeneque\StatamicStage\Events\PushToProductionCompleted;
use JoelSeneque\StatamicStage\Events\PushToProductionFailed;

// In a listener
public function handle(PushToProductionCompleted $event)
{
    // $event->user - The user who triggered the push
    // $event->log - Array of actions taken

    // Send notification, update external systems, etc.
}
```

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

Built by [Joel Seneque](https://github.com/joelseneque)
