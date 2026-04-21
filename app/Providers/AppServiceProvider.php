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

        // Only load OpenTelemetry when at least one exporter is configured.
        $tracesExporter = config('opentelemetry.traces.exporter');
        $metricsExporter = config('opentelemetry.metrics.exporter');
        $logsExporter = config('opentelemetry.logs.exporter');

        if ($tracesExporter !== 'null' || $metricsExporter !== 'null' || $logsExporter !== 'null') {
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
