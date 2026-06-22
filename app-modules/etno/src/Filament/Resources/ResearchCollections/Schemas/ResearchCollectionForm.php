<?php

namespace Metafori\Etno\Filament\Resources\ResearchCollections\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResearchCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->label(__('etno::ui.fields.title'))
                    ->translatableTabs()
                    ->requiredOnFallbackLocale(),
            ])
            ->columns(1);
    }
}
