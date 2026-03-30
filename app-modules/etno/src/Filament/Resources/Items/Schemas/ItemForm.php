<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Metafori\Etno\Filament\Schemas\SharedMetadataSchema;
use Metafori\Etno\Models\Item;

class ItemForm
{
    use SharedMetadataSchema;

    public static function configure(Schema $schema): Schema
    {
        $components = self::components(inheritable: true);
        $components[] = Hidden::make('document_overrides')
            ->rules(['array', Rule::in(Item::INHERITABLES)])
            ->dehydrateStateUsing(fn ($state) => $state ?? [])
            ->default([]);

        return $schema->components($components)
            ->columns(1);
    }
}
