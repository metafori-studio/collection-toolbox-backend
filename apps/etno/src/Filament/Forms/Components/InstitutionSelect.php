<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Core\Filament\Resources\OrganizationResource\Schemas\OrganizationForm;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class InstitutionSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Institution')
            ->relationship('institution', 'name')
            ->searchable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => OrganizationForm::configure($schema));
    }
}
