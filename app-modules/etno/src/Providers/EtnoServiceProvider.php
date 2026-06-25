<?php

namespace Metafori\Etno\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Console\Commands\CreateItemSearchIndexCommand;
use Metafori\Etno\EtnoPlugin;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Observers\DocumentObserver;
use Metafori\Etno\Observers\ItemObserver;
use Metafori\Etno\Observers\LocalityObserver;

class EtnoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'etno') {
                return;
            }

            $panel->plugin(EtnoPlugin::make());
        });
    }

    public function boot(): void
    {
        config([
            'frontend.routes.document' => '/{locale}/documents/{id}',
            'frontend.routes.item' => '/{locale}/items/{id}',
        ]);

        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'etno');

        Item::observe(ItemObserver::class);
        Document::observe(DocumentObserver::class);

        $localityModels = [
            Country::class,
            Region::class,
            District::class,
            Municipality::class,
            MunicipalityPart::class,
            Location::class,
        ];

        foreach ($localityModels as $model) {
            $model::observe(LocalityObserver::class);
        }

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateItemSearchIndexCommand::class,
            ]);
        }
    }
}
