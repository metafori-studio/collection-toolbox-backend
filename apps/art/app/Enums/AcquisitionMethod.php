<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum AcquisitionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case Donation = 'donation';
}
