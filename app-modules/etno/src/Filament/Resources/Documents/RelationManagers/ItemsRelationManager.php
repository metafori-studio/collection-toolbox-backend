<?php

namespace Metafori\Etno\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Filament\Resources\Items\Schemas\ItemForm;
use Metafori\Etno\Filament\Resources\Items\Tables\ItemsTable;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $relatedResource = ItemResource::class;

    public function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ItemsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
