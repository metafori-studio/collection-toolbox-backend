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

        $this->app->singleton(Client::class, function (): Client {
            $builder = ClientBuilder::create()
                ->setHosts(config('scout.opensearch.hosts', []));

            $username = config('scout.opensearch.username');
            $password = config('scout.opensearch.password');

            if ($username && $password) {
                $builder->setBasicAuthentication($username, $password);
            }

            return $builder->build();
        });
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
