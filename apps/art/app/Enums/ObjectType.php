<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum ObjectType: string implements HasLabel
{
    use HasTranslatedLabel;

    case Positive = 'positive';
    case Postcard = 'postcard';
}
