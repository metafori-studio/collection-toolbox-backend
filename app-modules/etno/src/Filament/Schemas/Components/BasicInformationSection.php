<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\DoiInput;
use Metafori\Etno\Filament\Forms\Components\IdInput;
use Metafori\Etno\Filament\Forms\Components\ResearchCollectionSelect;
use Metafori\Etno\Filament\Forms\Components\TypeSelect;

class BasicInformationSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Basic Information')
            ->schema([
                IdInput::make('id'),
                DoiInput::make('doi'),
                TypeSelect::make('type')
                    ->columnSpanFull(),
                ResearchCollectionSelect::make('researchCollections')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
