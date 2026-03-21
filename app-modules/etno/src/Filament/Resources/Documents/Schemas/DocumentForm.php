<?php

namespace Metafori\Etno\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Metafori\Etno\Filament\Schemas\SharedMetadataSchema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return SharedMetadataSchema::apply($schema);
    }
}
