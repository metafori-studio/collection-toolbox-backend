<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Metafori\Etno\Filament\Schemas\SharedMetadataSchema;

class ItemForm
{
    use SharedMetadataSchema;

    public static function configure(Schema $schema): Schema
    {
        $components = self::components(inheritable: true);
        $components[] = Hidden::make('document_overrides');

        return $schema->components($components)
            ->columns(1);
    }
}
