<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use Filament\Schemas\Schema;
use Metafori\Etno\Filament\Schemas\SharedMetadataSchema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return SharedMetadataSchema::apply($schema);
    }
}
