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
                    ->label('Parent')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->translatableTabs()
                    ->requiredOnFallbackLocale()
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->requiredWith('longitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->requiredWith('latitude')
                    ->numeric(),
            ]);
    }
}
