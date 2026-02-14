<?php

namespace Metafori\Core\Filament\Resources\OrganizationResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->requiredWith('ror_id')
                    ->translatableTabs()
                    ->requiredOnFallbackLocale()
                    ->columnSpanFull(),
                TextInput::make('ror_id')
                    ->label('ROR ID')
                    ->maxLength(9)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }
}
