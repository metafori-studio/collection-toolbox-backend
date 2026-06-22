<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;
use Metafori\Etno\Filament\Resources\Projects\Schemas\ProjectForm;

class ProjectSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.project'))
            ->relationship('project', 'title')
            ->searchable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => ProjectForm::configure($schema));
    }
}
