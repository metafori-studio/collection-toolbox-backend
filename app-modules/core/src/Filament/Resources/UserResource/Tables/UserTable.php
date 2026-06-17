<?php

namespace Metafori\Core\Filament\Resources\UserResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('core::ui.fields.user_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('core::ui.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.enum')
                    ->label(__('core::ui.fields.roles'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
