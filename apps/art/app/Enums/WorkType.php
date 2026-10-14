<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum WorkType: string implements HasLabel
{
    use HasTranslatedLabel;

    case AppliedArt = 'applied-art';
    case Drawing = 'drawing';
    case Painting = 'painting';
    case Photograph = 'photograph';
    case Print = 'print';
    case Sculpture = 'sculpture';
}
