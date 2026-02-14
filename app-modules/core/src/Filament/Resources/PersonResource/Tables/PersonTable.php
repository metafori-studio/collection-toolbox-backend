<?php

namespace Metafori\Core\Filament\Resources\PersonResource\Tables;

use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PersonTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('given_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orcid')
                    ->label('ORCID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('family_name');
    }
}
