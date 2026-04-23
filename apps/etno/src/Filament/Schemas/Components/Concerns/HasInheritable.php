<?php

namespace Metafori\Etno\Filament\Schemas\Components\Concerns;

trait HasInheritable
{
    protected bool $inheritable = false;

    public function inheritable(\Closure|bool $condition = true): static
    {
        $this->inheritable = $this->evaluate($condition);

        return $this;
    }
}
