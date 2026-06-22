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
                    ->label(__('core::ui.fields.name'))
                    ->translatableTabs()
                    ->requiredOnFallbackLocale()
                    ->columnSpanFull(),
                TextInput::make('ror_id')
                    ->label(__('core::ui.fields.ror_id'))
                    ->maxLength(9)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }
}
