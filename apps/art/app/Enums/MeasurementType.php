<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum MeasurementType: string implements HasLabel
{
    use HasTranslatedLabel;

    case Depth = 'depth';
    case Height = 'height';
    case Other = 'other';
    case Width = 'width';
}
