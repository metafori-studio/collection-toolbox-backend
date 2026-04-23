<?php

namespace Metafori\Core\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum Role: string implements HasColor, HasLabel
{
    use HasTranslatedLabel;

    case Admin = 'admin';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
        };
    }
}
