<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AuthorsSelect;
use Metafori\Etno\Filament\Forms\Components\OriginatorsRepeater;
use Metafori\Etno\Filament\Forms\Components\ResearchersSelect;

class AuthorsAndCreatorsSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Authors and Creators')
            ->schema([
                AuthorsSelect::make('authors')
                    ->columnSpanFull(),
                ResearchersSelect::make('researchers')
                    ->columnSpanFull(),
                OriginatorsRepeater::make('originators')
                    ->columnSpanFull(),
            ]);
    }
}
