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
use Metafori\Etno\EtnoPlugin;
use Metafori\Etno\Models\Item;
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
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'etno');

        Item::observe(ItemObserver::class);

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
    }
}
