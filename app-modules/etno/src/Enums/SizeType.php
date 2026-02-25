<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum SizeType: string implements HasLabel
{
    use HasTranslatedLabel;

    case PAGE_COUNT = 'page_count';
    case DURATION = 'duration';
}
