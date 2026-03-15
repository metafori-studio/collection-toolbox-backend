<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Resources\PersonResource\Schemas\PersonForm;
use Metafori\Core\Models\Person;

class PersonSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getOptionLabelFromRecordUsing(fn (Person $person) => $person->display_name);
    }

    public function withOptionForm(): static
    {
        return $this->createOptionForm(fn (Schema $schema) => PersonForm::configure($schema)->getComponents());
    }
}
