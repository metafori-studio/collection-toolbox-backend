<?php

namespace Metafori\Core\Filament\Resources\KeywordResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KeywordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('core::ui.fields.name'))
                    ->translatableTabs()
                    ->requiredOnFallbackLocale(),
            ])
            ->columns(1);
    }
}
