<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum AccessRight: string implements HasLabel
{
    use HasTranslatedLabel;

    case Open = 'open';
    case Restricted = 'restricted';
    case Blocked = 'blocked';
    case MetadataOnly = 'metadata_only';
    case Paid = 'paid';
    case Closed = 'closed';
}
