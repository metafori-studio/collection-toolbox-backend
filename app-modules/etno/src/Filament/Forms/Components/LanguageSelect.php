<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Enums\Language;
use Metafori\Core\Filament\Forms\Components\Select;

class LanguageSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Language')
            ->options(Language::class)
            ->sortedOptions()
            ->searchable();
    }
}
