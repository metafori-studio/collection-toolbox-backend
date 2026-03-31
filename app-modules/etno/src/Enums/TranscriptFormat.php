<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;

enum TranscriptFormat: string implements HasLabel
{
    case Xml = 'xml';
    case Txt = 'txt';

    public function getLabel(): string
    {
        return strtoupper($this->value);
    }

    public function getCustomPropertyKey(): string
    {
        return "transcript_{$this->value}";
    }
}
