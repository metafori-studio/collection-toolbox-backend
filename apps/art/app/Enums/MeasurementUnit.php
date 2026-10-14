<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum MeasurementUnit: string implements HasLabel
{
    use HasTranslatedLabel;

    case Cm = 'cm';
}
