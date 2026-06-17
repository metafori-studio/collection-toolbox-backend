<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Enums\Language;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class LanguageSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.language'))
            ->options(Language::class)
            ->sortedOptions()
            ->searchable();
    }
}
