<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Core\Filament\Resources\KeywordResource\Schemas\KeywordForm;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class KeywordsSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.keywords'))
            ->relationship('keywords', 'name')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => KeywordForm::configure($schema))
            ->saveOrder();
    }
}
