<?php

namespace Metafori\Core\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum License: string implements HasLabel
{
    use HasTranslatedLabel;

    case CC_BY = 'cc-by';
    case CC_BY_NC = 'cc-by-nc';
    case CC_BY_NC_ND = 'cc-by-nc-nd';
    case CC_BY_NC_SA = 'cc-by-nc-sa';
    case CC_BY_ND = 'cc-by-nd';
    case CC_BY_SA = 'cc-by-sa';
    case CC0 = 'cc0';
}
