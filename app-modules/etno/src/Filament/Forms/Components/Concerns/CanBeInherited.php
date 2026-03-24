<?php

namespace Metafori\Etno\Filament\Forms\Components\Concerns;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Metafori\Etno\Filament\Actions\ToggleInheritanceAction;

trait CanBeInherited
{
    public function inheritable(\Closure|bool $condition = true): static
    {
        if (! $this->evaluate($condition)) {
            return $this;
        }

        $attributeName = $this->getName();
        $action = ToggleInheritanceAction::make("{$attributeName}_toggle_inheritance")
            ->attributeName($attributeName);

        $this->disabled(static fn (Get $get) => ToggleInheritanceAction::isInheritedState($get, $attributeName));

        return match (true) {
            $this instanceof MorphToSelect => $this->modifyTypeSelectUsing(static fn (Select $select) => $select->suffixAction($action)),
            method_exists($this, 'suffixAction') => $this->suffixAction($action),
            method_exists($this, 'hintAction') => $this->hintAction($action),
            default => $this,
        };
    }
}
