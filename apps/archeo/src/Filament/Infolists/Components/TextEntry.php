<?php

namespace Metafori\Archeo\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry as BaseTextEntry;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;

class TextEntry extends BaseTextEntry
{
    public function setUp(): void
    {
        parent::setUp();

        $this->fontFamily(FontFamily::Mono)
            ->weight(FontWeight::Bold)
            ->placeholder('–');
    }
}
