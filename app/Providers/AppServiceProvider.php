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

        // Prevent OpenTelemetry from loading if all exporters are set to null
        $tracesExporter = config('opentelemetry.traces.exporter');
        $metricsExporter = config('opentelemetry.metrics.exporter');
        $logsExporter = config('opentelemetry.logs.exporter');

        if ($tracesExporter === 'null' && $metricsExporter === 'null' && $logsExporter === 'null') {
            $this->app->register(LaravelOpenTelemetryServiceProvider::class, false);
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
