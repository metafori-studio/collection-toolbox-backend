<?php

namespace Metafori\Opensearch\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Metafori\Opensearch\Scout\Engines\OpenSearchEngine;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpensearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/scout.opensearch.php', 'scout.opensearch'
        );

        $this->app->singleton(Client::class, fn (): Client => ClientBuilder::create()
            ->setHosts(config('scout.opensearch.hosts', []))
            ->build()
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/scout.opensearch.php' => config_path('scout.opensearch.php'),
            ], ['opensearch-config', 'opensearch']);
        }

        resolve(EngineManager::class)->extend(
            'opensearch',
            fn (Application $app): OpenSearchEngine => new OpenSearchEngine($app->make(Client::class))
        );
    }
}
