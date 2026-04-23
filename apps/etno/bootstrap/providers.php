<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\EtnoPanelProvider;
use Metafori\Core\Providers\CoreServiceProvider;
use Metafori\Etno\Providers\EtnoServiceProvider;
use Metafori\Opensearch\Providers\OpensearchServiceProvider;

return [
    AppServiceProvider::class,
    EtnoPanelProvider::class,
    CoreServiceProvider::class,
    EtnoServiceProvider::class,
    OpensearchServiceProvider::class,
];
