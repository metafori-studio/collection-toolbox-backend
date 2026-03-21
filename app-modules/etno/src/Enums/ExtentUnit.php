<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum ExtentUnit: string implements HasLabel
{
    use HasTranslatedLabel;

    case Drawing = 'drawing';
    case Duration = 'duration';
    case Item = 'item';
    case Page = 'page';
    case Photograph = 'photograph';
    case ResearchSlip = 'research-slip';
    case Volume = 'volume';
}
