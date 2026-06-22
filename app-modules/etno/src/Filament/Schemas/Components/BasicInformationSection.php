<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Livewire\Component;
use Metafori\Etno\Filament\Forms\Components\DoiInput;
use Metafori\Etno\Filament\Forms\Components\IdInput;
use Metafori\Etno\Filament\Forms\Components\ResearchCollectionSelect;
use Metafori\Etno\Filament\Forms\Components\SuffixInput;
use Metafori\Etno\Filament\Forms\Components\TypeSelect;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class BasicInformationSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading(__('etno::ui.sections.basic_information'))
            ->schema(fn (Component $livewire) => [
                $livewire->parentRecord
                    ? SuffixInput::make('suffix')
                    : IdInput::make('id'),
                DoiInput::make('doi')
                    ->inheritable($this->inheritable),
                TypeSelect::make('type')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                ResearchCollectionSelect::make('researchCollections')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
