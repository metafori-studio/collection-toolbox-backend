<?php

namespace Metafori\Core\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Metafori\Core\Filament\Resources\KeywordResource\Pages;
use Metafori\Core\Filament\Resources\KeywordResource\Schemas\KeywordForm;
use Metafori\Core\Filament\Resources\KeywordResource\Tables\KeywordTable;
use Metafori\Core\Models\Keyword;

class KeywordResource extends Resource
{
    protected static ?string $model = Keyword::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return KeywordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeywordTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeywords::route('/'),
            'create' => Pages\CreateKeyword::route('/create'),
            'edit' => Pages\EditKeyword::route('/{record}/edit'),
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
