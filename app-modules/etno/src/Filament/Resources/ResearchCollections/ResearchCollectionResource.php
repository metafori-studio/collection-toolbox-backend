<?php

namespace Metafori\Etno\Filament\Resources\ResearchCollections;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\ResearchCollections\Pages\CreateResearchCollection;
use Metafori\Etno\Filament\Resources\ResearchCollections\Pages\EditResearchCollection;
use Metafori\Etno\Filament\Resources\ResearchCollections\Pages\ListResearchCollections;
use Metafori\Etno\Filament\Resources\ResearchCollections\Schemas\ResearchCollectionForm;
use Metafori\Etno\Filament\Resources\ResearchCollections\Tables\ResearchCollectionsTable;
use Metafori\Etno\Models\ResearchCollection;

class ResearchCollectionResource extends Resource
{
    protected static ?string $model = ResearchCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('etno::ui.resources.research_collection.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('etno::ui.resources.research_collection.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('etno::ui.resources.research_collection.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ResearchCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchCollectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchCollections::route('/'),
            'create' => CreateResearchCollection::route('/create'),
            'edit' => EditResearchCollection::route('/{record}/edit'),
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
