<?php

namespace Metafori\Core\Filament\Resources;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Metafori\Core\Filament\Resources\MunicipalityPartResource\Pages;
use Metafori\Core\Models\MunicipalityPart;

class MunicipalityPartResource extends Resource
{
    protected static ?string $model = MunicipalityPart::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('core::ui.resources.municipality_part.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('core::ui.resources.municipality_part.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('core::ui.resources.municipality_part.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('core::ui.navigation_groups.localities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('municipality_id')
                    ->label(__('core::ui.fields.municipality'))
                    ->relationship('municipality', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                TextInput::make('name')
                    ->label(__('core::ui.fields.name'))
                    ->translatableTabs()
                    ->requiredOnFallbackLocale()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('core::ui.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('municipality.name')
                    ->label(__('core::ui.fields.municipality'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\ForceDeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMunicipalityParts::route('/'),
        ];
    }
}
