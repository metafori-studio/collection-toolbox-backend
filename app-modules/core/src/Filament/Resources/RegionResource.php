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
use Metafori\Core\Filament\Resources\RegionResource\Pages;
use Metafori\Core\Models\Region;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('core::ui.resources.region.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('core::ui.resources.region.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('core::ui.resources.region.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('core::ui.navigation_groups.localities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('country_id')
                    ->label(__('core::ui.fields.country'))
                    ->relationship('country', 'name')
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
                TextColumn::make('country.name')
                    ->label(__('core::ui.fields.country'))
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
            'index' => Pages\ManageRegions::route('/'),
        ];
    }
}
