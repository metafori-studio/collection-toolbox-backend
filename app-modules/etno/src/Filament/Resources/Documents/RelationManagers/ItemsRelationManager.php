<?php

namespace Metafori\Etno\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Filament\Resources\Items\Tables\ItemsTable;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $relatedResource = ItemResource::class;

    public function table(Table $table): Table
    {
        return ItemsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->headerActions([
                CreateAction::make(),
                Action::make('create_from_files')
                    ->label(__('etno::ui.actions.create_from_files'))
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
                    ->url(fn (RelationManager $livewire): string => ItemResource::getUrl(
                        'create-from-files',
                        ['document' => $livewire->getOwnerRecord()]
                    )),
            ]);
    }
}
