<?php

namespace Metafori\Core\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Metafori\Core\Filament\Resources\LocalityResource\Pages;
use Metafori\Core\Filament\Resources\LocalityResource\RelationManagers;
use Metafori\Core\Filament\Resources\LocalityResource\Schemas\LocalityForm;
use Metafori\Core\Filament\Resources\LocalityResource\Tables\LocalityTable;
use Metafori\Core\Models\Locality;

class LocalityResource extends Resource
{
    protected static ?string $model = Locality::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    public static function form(Schema $schema): Schema
    {
        return LocalityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocalityTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocalities::route('/'),
            'create' => Pages\CreateLocality::route('/create'),
            'edit' => Pages\EditLocality::route('/{record}/edit'),
        ];
    }
}
