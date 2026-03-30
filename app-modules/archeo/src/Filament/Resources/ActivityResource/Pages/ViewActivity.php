<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Metafori\Archeo\Filament\Resources\ActivityResource;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
