<?php

namespace Metafori\Core\Filament\Resources\PersonResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('given_name')
                    ->required(),
                TextInput::make('family_name')
                    ->required(),
                TextInput::make('orcid')
                    ->label('ORCID')
                    ->maxLength(19)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }
}
