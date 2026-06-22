<?php

namespace Metafori\Core\Filament\Resources\LocationResource\Tables;

use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('core::ui.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('core::ui.fields.parent'))
                    ->placeholder(__('core::ui.placeholders.none'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->label(__('core::ui.fields.latitude')),
                TextColumn::make('longitude')
                    ->label(__('core::ui.fields.longitude')),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
                Actions\RestoreBulkAction::make(),
            ]);
    }
}
