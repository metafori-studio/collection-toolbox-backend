<?php

namespace Metafori\Core\Filament\Resources\LocalityResource\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Metafori\Core\Enums\LocalityType;

class LocalityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('type')
                    ->required()
                    ->options(LocalityType::class)
                    ->searchable(),
                SelectTree::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'name', 'parent_id')
                    ->enableBranchNode()
                    ->searchable(),
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
