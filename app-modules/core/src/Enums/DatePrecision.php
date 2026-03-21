<?php

namespace Metafori\Core\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum DatePrecision: string implements HasLabel
{
    use HasTranslatedLabel;

    case Year = 'year';
    case Month = 'month';
    case Day = 'day';
}
