<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum DocumentFormat: string implements HasLabel
{
    use HasTranslatedLabel;

    case A4 = 'a4';
    case ARCHIVAL_CARD = 'archival_card';
    case ELECTRONIC_TEXT = 'electronic_text';
}
