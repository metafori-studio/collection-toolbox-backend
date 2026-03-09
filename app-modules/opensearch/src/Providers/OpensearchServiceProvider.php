<?php

namespace Metafori\Opensearch\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
