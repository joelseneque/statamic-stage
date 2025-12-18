# Statamic Stage

A Statamic addon that provides a staging-to-production workflow with a one-click "Push to Production" button and read-only production mode.

## Features

- **Push to Production Button** - One-click merge from staging branch to main/production branch via GitHub API
- **Read-Only Production Mode** - Prevents content editing on production environment
- **Dashboard Widget** - Shows pending changes count with quick access to push
- **Artisan Commands** - CLI commands for automation and scripting
- **Permission System** - Role-based access control for the push feature
- **Activity Logging** - All push attempts are logged
- **Forge Compatible** - Works with Laravel Forge's release-based deployments

## Requirements

- PHP 8.2+
- Statamic 5.0+
- **Statamic Pro** (required for Git Automation on staging and multi-user support)
- Git installed on the server
- **GitHub Personal Access Token** (for merge operations via GitHub API)

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

# GitHub API Configuration (REQUIRED for merge operations)
STATAMIC_STAGE_GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
STATAMIC_STAGE_GITHUB_REPO=your-username/your-repo
```

#### Production Environment (`.env`)
```env
APP_ENV=production

# Disable Git Automation on production
STATAMIC_GIT_ENABLED=false

# Enable read-only mode (default: true)
STATAMIC_STAGE_READ_ONLY=true
```

### 4. Create a GitHub Personal Access Token

The addon uses the GitHub API to merge branches, which avoids issues with Forge's release-based deployment structure.

1. Go to https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Give it a descriptive name (e.g., "Statamic Stage - My Site")
4. Set expiration to **"No expiration"** (recommended) or choose a custom expiration
5. Select the **`repo`** scope (Full control of private repositories)
6. Click **"Generate token"**
7. Copy the token immediately (you won't see it again)

Add to your staging server's `.env`:
```env
STATAMIC_STAGE_GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
STATAMIC_STAGE_GITHUB_REPO=your-username/your-repo
```

### 5. Configure Git User (Optional)

Set the git user for commits made by the addon:

```env
STATAMIC_STAGE_GIT_NAME="Statamic Stage"
STATAMIC_STAGE_GIT_EMAIL="stage@yourdomain.com"
```

### 6. Set Up Permissions

1. Go to **Statamic CP → Users → Roles**
2. Edit the role(s) that should have push access
3. Enable the **"Push to Production"** permission under "Statamic Stage"

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

    // GitHub API settings (required for merge operations)
    'github' => [
        'token' => env('STATAMIC_STAGE_GITHUB_TOKEN'),
        'repo' => env('STATAMIC_STAGE_GITHUB_REPO'),  // Format: owner/repo
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
1. On **local**: Commit any uncommitted changes and push to the staging branch
2. Call the **GitHub API** to merge staging into the production branch
3. GitHub triggers auto-deploy on your production server (via Forge webhook)

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

### "GitHub token and repo must be configured"

You're missing the required GitHub API configuration.

**Resolution:**
Add to your `.env` file:
```env
STATAMIC_STAGE_GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
STATAMIC_STAGE_GITHUB_REPO=your-username/your-repo
```

See [Create a GitHub Personal Access Token](#4-create-a-github-personal-access-token) for instructions.

### "Merge conflict detected on GitHub"

There are conflicting changes between staging and production branches. This usually happens if someone pushed directly to main.

**Resolution:**
1. Go to your GitHub repository
2. Create a Pull Request from `staging` to `main`
3. Resolve the conflicts in GitHub's interface
4. Merge the PR
5. Or resolve locally:
   ```bash
   git fetch origin
   git checkout main
   git merge origin/staging
   # Resolve conflicts
   git add .
   git commit
   git push origin main
   ```

### "GitHub merge failed: Bad credentials"

Your GitHub token is invalid or expired.

**Resolution:**
1. Go to https://github.com/settings/tokens
2. Check if your token is still valid
3. If expired, generate a new token with `repo` scope
4. Update your `.env` with the new token
5. Clear config cache: `php artisan config:clear`

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
