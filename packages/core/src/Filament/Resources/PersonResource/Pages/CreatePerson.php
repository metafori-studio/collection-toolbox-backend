<?php

namespace Metafori\Core\Filament\Resources\PersonResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Metafori\Core\Filament\Resources\PersonResource;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;
}
