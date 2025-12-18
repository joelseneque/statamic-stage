<?php

namespace JoelSeneque\StatamicStage;

use Illuminate\Support\Facades\Event;
use Statamic\Events\AssetSaving;
use Statamic\Events\EntrySaving;
use Statamic\Events\GlobalVariablesSaving;
use Statamic\Events\NavTreeSaving;
use Statamic\Events\TermSaving;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\Utility;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $widgets = [
        Widgets\PushToProductionWidget::class,
    ];

    protected $commands = [
        Commands\PushToProduction::class,
        Commands\StageStatus::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $listen = [
        Events\PushToProductionStarted::class => [
            Listeners\LogPushActivity::class,
        ],
        Events\PushToProductionCompleted::class => [
            Listeners\LogPushActivity::class,
        ],
        Events\PushToProductionFailed::class => [
            Listeners\LogPushActivity::class,
        ],
    ];

    public function register(): void
    {
        $this->app->singleton(Stage::class, function () {
            return new Stage(new Git\GitOperations);
        });
    }

    public function bootAddon(): void
    {
        $this
            ->bootConfig()
            ->bootTranslations()
            ->bootPermissions()
            ->bootUtility()
            ->bootNavigation()
            ->bootReadOnlyMode();
    }

    protected function bootConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-stage.php', 'statamic-stage');

        $this->publishes([
            __DIR__.'/../config/statamic-stage.php' => config_path('statamic-stage.php'),
        ], 'statamic-stage-config');

        return $this;
    }

    protected function bootTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-stage');

        return $this;
    }

    protected function bootPermissions(): static
    {
        Permission::group('statamic-stage', 'Statamic Stage', function () {
            Permission::register('push to production')
                ->label(__('statamic-stage::messages.permission_label'))
                ->description(__('statamic-stage::messages.permission_description'));
        });

        return $this;
    }

    protected function bootUtility(): static
    {
        if ($this->shouldShowPushInterface()) {
            Utility::register('stage')
                ->action([Http\Controllers\StageController::class, 'index'])
                ->title(__('statamic-stage::messages.utility_title'))
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><line x1="1.05" y1="12" x2="7" y2="12"/><line x1="17.01" y1="12" x2="22.96" y2="12"/></svg>')
                ->navTitle(__('statamic-stage::messages.nav_title'))
                ->description(__('statamic-stage::messages.utility_description'))
                ->routes(function ($router) {
                    $router->post('push', [Http\Controllers\StageController::class, 'push'])->name('push');
                    $router->get('status', [Http\Controllers\StageController::class, 'status'])->name('status');
                });
        }

        return $this;
    }

    protected function bootNavigation(): static
    {
        if ($this->shouldShowPushInterface()) {
            Nav::extend(function ($nav) {
                $nav->tools(__('statamic-stage::messages.nav_title'))
                    ->route('utilities.stage')
                    ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><line x1="1.05" y1="12" x2="7" y2="12"/><line x1="17.01" y1="12" x2="22.96" y2="12"/></svg>')
                    ->can('push to production');
            });
        }

        return $this;
    }

    protected function bootReadOnlyMode(): static
    {
        if ($this->isProductionReadOnly()) {
            $listener = new Listeners\PreventProductionEdits;

            Event::listen(EntrySaving::class, [$listener, 'handle']);
            Event::listen(TermSaving::class, [$listener, 'handle']);
            Event::listen(GlobalVariablesSaving::class, [$listener, 'handle']);
            Event::listen(NavTreeSaving::class, [$listener, 'handle']);
            Event::listen(AssetSaving::class, [$listener, 'handle']);
        }

        return $this;
    }

    protected function shouldShowPushInterface(): bool
    {
        return in_array(
            app()->environment(),
            config('statamic-stage.environments.show_push_button', ['local', 'staging'])
        );
    }

    protected function isProductionReadOnly(): bool
    {
        return config('statamic-stage.read_only_on_production', true)
            && app()->environment('production');
    }
}
