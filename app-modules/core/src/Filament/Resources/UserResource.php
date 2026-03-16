<?php

namespace Metafori\Core\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Metafori\Core\Filament\Resources\UserResource\Pages;
use Metafori\Core\Filament\Resources\UserResource\Schemas\UserForm;
use Metafori\Core\Filament\Resources\UserResource\Tables\UserTable;
use Metafori\Core\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
