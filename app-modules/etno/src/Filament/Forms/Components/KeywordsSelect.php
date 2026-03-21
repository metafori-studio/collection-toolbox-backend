<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Core\Filament\Resources\KeywordResource\Schemas\KeywordForm;

class KeywordsSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Keywords')
            ->relationship('keywords', 'name')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => KeywordForm::configure($schema))
            ->saveOrder();
    }
}
