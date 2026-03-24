<?php

namespace Metafori\Etno\Filament\Schemas\Components\Concerns;

use Filament\Schemas\Components\Utilities\Get;
use Metafori\Etno\Filament\Actions\ToggleInheritanceAction;

trait CanBeInherited
{
    public function inheritable(\Closure|bool $condition = true): static
    {
        if (! $this->evaluate($condition)) {
            return $this;
        }

        $name = $this->getName();
        $fieldNames = $this->getFieldNames();
        $action = ToggleInheritanceAction::make("{$name}_toggle_inheritance")
            ->names($fieldNames);

        $this->disabled(static fn (Get $get) => ToggleInheritanceAction::isInheritedState($get, $fieldNames));

        return $this->headerActions([$action]);
    }
}
