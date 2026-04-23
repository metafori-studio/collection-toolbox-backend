<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('archeo::activities.fields.assigned_user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label(__('archeo::activities.fields.expires_at'))
                    ->default(now()->addYear())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (Model $record): string => $record->user->name)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('archeo::activities.fields.assigned_user'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('archeo::activities.fields.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state): ?string => $state?->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('remaining')
                    ->label(__('archeo::activities.fields.remaining_time'))
                    ->getStateUsing(fn ($record): ?string => $record->expires_at?->diffForHumans() ?? '-')
                    ->color(fn ($record): ?string => $record->expires_at?->isPast() ? 'danger' : 'gray'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
