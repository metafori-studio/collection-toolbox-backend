<?php

namespace Metafori\Core\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum LocalityType: string implements HasColor, HasLabel
{
    use HasTranslatedLabel;

    case COUNTRY = 'country';
    case REGION = 'region';
    case DISTRICT = 'district';
    case CITY = 'city';
    case BOROUGH = 'borough';
    case CUSTOM = 'custom';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CUSTOM => Color::Neutral,
            default => Color::Amber,
        };
    }
}
