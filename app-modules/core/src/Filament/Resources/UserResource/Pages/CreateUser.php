<?php

namespace Metafori\Core\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Metafori\Core\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);
    }
}
