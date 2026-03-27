<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AuthorsSelect;
use Metafori\Etno\Filament\Forms\Components\OriginatorsRepeater;
use Metafori\Etno\Filament\Forms\Components\ResearchersSelect;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class AuthorsAndCreatorsSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Authors and Creators')
            ->schema(fn () => [
                AuthorsSelect::make('authors')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                ResearchersSelect::make('researchers')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                OriginatorsRepeater::make('originators')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
            ])
            ->collapsible();
    }
}
