<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum DocumentNotation: string implements HasLabel
{
    use HasTranslatedLabel;

    case TYPESET = 'typeset';
    case MANUSCRIPT = 'manuscript';
    case DRAWING = 'drawing';
    case SHEET_MUSIC = 'sheet_music';
}
