<?php

namespace Metafori\Core\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Metafori\Core\Filament\Resources\PersonResource\Pages;
use Metafori\Core\Filament\Resources\PersonResource\Schemas\PersonForm;
use Metafori\Core\Filament\Resources\PersonResource\Tables\PersonTable;
use Metafori\Core\Models\Person;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return PersonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
