<?php

namespace Metafori\Core\Tests\Fixtures;

use Filament\Support\Contracts\HasLabel;

enum TestPermission: string implements HasLabel
{
    case Example = 'example.permission';

    public function getLabel(): string
    {
        return match ($this) {
            self::Example => 'Example permission',
        };
    }
}
