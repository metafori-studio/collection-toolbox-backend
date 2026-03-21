<?php

namespace Metafori\Core\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Metafori\Core\Filament\Resources\UserResource;
use Metafori\Core\Support\Facades\Password;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        Password::broker()->sendSetLink($user);
    }
}
