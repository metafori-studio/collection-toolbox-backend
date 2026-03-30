<?php

namespace Metafori\Etno\Filament\Resources\Documents;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Etno\Filament\Resources\Documents\Pages\CreateDocument;
use Metafori\Etno\Filament\Resources\Documents\Pages\EditDocument;
use Metafori\Etno\Filament\Resources\Documents\Pages\ListDocuments;
use Metafori\Etno\Filament\Resources\Documents\Schemas\DocumentForm;
use Metafori\Etno\Filament\Resources\Documents\Tables\DocumentsTable;
use Metafori\Etno\Models\Document;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'items' => RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
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
