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

    public function getMimeType(): string
    {
        return match ($this) {
            self::Xml => 'text/xml',
            self::Txt => 'text/plain',
        };
    }

    public static function mimeTypes(): array
    {
        return array_map(fn (self $format) => $format->getMimeType(), self::cases());
    }
}
