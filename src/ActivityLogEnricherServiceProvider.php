<?php

declare(strict_types=1);

namespace JimmyAhalpara\ActivityLogEnricher;

use Illuminate\Support\ServiceProvider;

final class ActivityLogEnricherServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/activity-log-enricher.php',
            'activity-log-enricher'
        );

        $this->app->singleton(ActivityLogEnricher::class, static function () {
            return new ActivityLogEnricher();
        });

        $this->app->alias(ActivityLogEnricher::class, 'activity-log-enricher');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/activity-log-enricher.php' => config_path('activity-log-enricher.php'),
            ], 'activity-log-enricher-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            ActivityLogEnricher::class,
            'activity-log-enricher',
        ];
    }
}
