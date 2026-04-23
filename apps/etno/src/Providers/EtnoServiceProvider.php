<?php

namespace Metafori\Etno\Providers;

use Illuminate\Support\ServiceProvider;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Observers\DocumentObserver;
use Metafori\Etno\Observers\ItemObserver;
use Metafori\Etno\Observers\LocalityObserver;

class EtnoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
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
    }
}
