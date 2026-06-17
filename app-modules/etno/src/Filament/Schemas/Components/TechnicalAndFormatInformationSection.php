<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\ExtentsRepeater;
use Metafori\Etno\Filament\Forms\Components\ProductionMethodsSelect;
use Metafori\Etno\Filament\Forms\Components\TechnicalNoteInput;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class TechnicalAndFormatInformationSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading(__('etno::ui.sections.technical_and_format_information'))
            ->schema(fn () => [
                ExtentsRepeater::make('extents')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                ProductionMethodsSelect::make('production_methods')
                    ->inheritable($this->inheritable),
                TechnicalNoteInput::make('technical_note')
                    ->inheritable($this->inheritable)
                    ->translatableTabs(),
            ])
            ->collapsible()
            ->columns(2);
    }
}
