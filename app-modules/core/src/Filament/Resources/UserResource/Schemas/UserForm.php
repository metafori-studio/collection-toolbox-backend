<?php

namespace Metafori\Core\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Metafori\Core\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $role) => $role->label)
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
