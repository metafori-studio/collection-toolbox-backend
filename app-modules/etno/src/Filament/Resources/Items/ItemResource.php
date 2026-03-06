<?php

namespace Metafori\Etno\Filament\Resources\Items;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\Items\Pages\CreateItem;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Filament\Resources\Items\Pages\ListItems;
use Metafori\Etno\Filament\Resources\Items\Schemas\ItemForm;
use Metafori\Etno\Filament\Resources\Items\Tables\ItemsTable;
use Metafori\Etno\Models\Item;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItems::route('/'),
            'create' => CreateItem::route('/create'),
            'edit' => EditItem::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
