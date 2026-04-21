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

        // Only load OpenTelemetry when explicitly configured (not using defaults).
        // By default, all exporters are 'otlp' which requires gRPC dependencies.
        $tracesExporter = config('opentelemetry.traces.exporter', 'otlp');
        $metricsExporter = config('opentelemetry.metrics.exporter', 'otlp');
        $logsExporter = config('opentelemetry.logs.exporter', 'otlp');

        // Only load if at least one exporter is explicitly configured (not default otlp and not null)
        $hasExplicitConfig = ($tracesExporter !== 'otlp' && $tracesExporter !== 'null') ||
                           ($metricsExporter !== 'otlp' && $metricsExporter !== 'null') ||
                           ($logsExporter !== 'otlp' && $logsExporter !== 'null');

        if ($hasExplicitConfig) {
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
