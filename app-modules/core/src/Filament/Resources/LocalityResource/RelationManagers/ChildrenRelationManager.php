<?php

namespace Metafori\Core\Filament\Resources\LocalityResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Metafori\Core\Filament\Resources\LocalityResource\Schemas\LocalityForm;
use Metafori\Core\Filament\Resources\LocalityResource\Tables\LocalityTable;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public function form(Schema $schema): Schema
    {
        return LocalityForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return LocalityTable::configure($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ]);
    }
}
