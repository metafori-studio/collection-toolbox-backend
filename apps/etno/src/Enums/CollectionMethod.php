<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum CollectionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case ArchivalResearch = 'archival-research';
    case FieldResearch = 'field-research';
    case Survey = 'survey';
}
