<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Keepsuit\LaravelOpenTelemetry\LaravelOpenTelemetryServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // Register OpenTelemetry only when at least one exporter is not disabled.
        $exporters = [
            config('opentelemetry.traces.exporter'),
            config('opentelemetry.metrics.exporter'),
            config('opentelemetry.logs.exporter'),
        ];
        if (array_filter($exporters, fn ($e) => $e !== null && $e !== 'null')) {
            $this->app->register(LaravelOpenTelemetryServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
    }
}
