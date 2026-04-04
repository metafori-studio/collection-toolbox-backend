<?php

namespace Metafori\Etno\Filament\Resources\Items;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Filament\Resources\Items\Schemas\ItemForm;
use Metafori\Etno\Filament\Resources\Items\Tables\ItemsTable;
use Metafori\Etno\Models\Item;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static ?string $parentResource = DocumentResource::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getParentResourceRegistration(): ?\Filament\Resources\ParentResourceRegistration
    {
        return DocumentResource::asParent()
            ->relationship('items')
            ->inverseRelationship('document');
    }

    public static function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'media' => RelationManagers\MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
