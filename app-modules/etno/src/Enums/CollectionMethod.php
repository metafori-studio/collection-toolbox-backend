<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum CollectionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case FieldResearch = 'field_research';
    case Survey = 'survey';
    case ArchivalResearch = 'archival_research';
}
