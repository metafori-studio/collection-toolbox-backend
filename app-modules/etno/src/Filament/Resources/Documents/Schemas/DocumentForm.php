<?php

namespace Metafori\Etno\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Metafori\Etno\Filament\Schemas\SharedMetadataSchema;

class DocumentForm
{
    use SharedMetadataSchema;

    public static function configure(Schema $schema): Schema
    {
        $components = self::components(inheritable: false);

        return $schema->components($components)
            ->columns(1);
    }
}
