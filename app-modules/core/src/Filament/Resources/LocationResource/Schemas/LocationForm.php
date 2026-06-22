<?php

namespace Metafori\Core\Filament\Resources\LocationResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\LocalitySelect;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                LocalitySelect::make('parent')
                    ->label(__('core::ui.fields.parent'))
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label(__('core::ui.fields.name'))
                    ->translatableTabs()
                    ->requiredOnFallbackLocale()
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->label(__('core::ui.fields.latitude'))
                    ->requiredWith('longitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->label(__('core::ui.fields.longitude'))
                    ->requiredWith('latitude')
                    ->numeric(),
            ]);
    }
}
