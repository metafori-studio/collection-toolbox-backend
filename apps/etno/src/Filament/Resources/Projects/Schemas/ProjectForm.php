<?php

namespace Metafori\Etno\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->translatableTabs()
                    ->requiredOnFallbackLocale(),
                TextInput::make('doi')
                    ->label('DOI')
                    ->placeholder('10.xxxx/xxxxx'),
            ])
            ->columns(1);
    }
}
