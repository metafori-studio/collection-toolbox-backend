<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\ArcheoPanelProvider;
use Metafori\Archeo\Providers\ArcheoServiceProvider;
use Metafori\Core\Providers\CoreServiceProvider;

return [
    AppServiceProvider::class,
    ArcheoPanelProvider::class,
    CoreServiceProvider::class,
    ArcheoServiceProvider::class,
];
