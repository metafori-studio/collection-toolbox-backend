<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Resources\Projects\Schemas\ProjectForm;

class ProjectSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Project')
            ->relationship('project', 'title')
            ->searchable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => ProjectForm::configure($schema));
    }
}
