<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Metafori\Core\Filament\Schemas\Components\PrecisionDateSection as CorePrecisionDateSection;
use Metafori\Etno\Filament\Schemas\Components\Concerns\CanBeInherited;

class PrecisionDateSection extends CorePrecisionDateSection
{
    use CanBeInherited;

    public function getFieldNames(): array
    {
        return [
            $this->startFieldName(),
            $this->endFieldName(),
            $this->settingsFieldName(),
        ];
    }
}
