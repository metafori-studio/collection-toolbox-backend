<?php

namespace Metafori\Core\Filament\Resources\PersonResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('given_name')
                    ->label(__('core::ui.fields.given_name'))
                    ->required(),
                TextInput::make('family_name')
                    ->label(__('core::ui.fields.family_name'))
                    ->required(),
                TextInput::make('orcid')
                    ->label(__('core::ui.fields.orcid'))
                    ->maxLength(19)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }
}
