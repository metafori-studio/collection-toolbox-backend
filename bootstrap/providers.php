<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\ArcheoPanelProvider;
use App\Providers\Filament\EtnoPanelProvider;
use App\Providers\MetricsServiceProvider;

return [
    AppServiceProvider::class,
    ArcheoPanelProvider::class,
    EtnoPanelProvider::class,
    MetricsServiceProvider::class,
];
