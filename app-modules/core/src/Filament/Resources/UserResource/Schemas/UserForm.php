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
                    ->label(__('core::ui.fields.user_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('core::ui.fields.email'))
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('roles')
                    ->label(__('core::ui.fields.roles'))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $role) => $role->label)
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
