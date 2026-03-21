<?php

namespace Metafori\Core\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum License: string implements HasLabel
{
    use HasTranslatedLabel;

    case CcBy = 'cc-by';
    case CcByNc = 'cc-by-nc';
    case CcByNcNd = 'cc-by-nc-nd';
    case CcByNcSa = 'cc-by-nc-sa';
    case CcByNd = 'cc-by-nd';
    case CcBySa = 'cc-by-sa';
    case Cc0 = 'cc0';
}
