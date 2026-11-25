<?php

namespace Metafori\Archeo\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum Permission: string implements HasLabel
{
    use HasTranslatedLabel;

    case ActivityViewAnyDocument = 'activity.view-any-document';
}
