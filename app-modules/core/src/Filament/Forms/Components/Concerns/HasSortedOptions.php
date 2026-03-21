<?php

namespace Metafori\Core\Filament\Forms\Components\Concerns;

use Closure;

trait HasSortedOptions
{
    protected bool|Closure $isSortedOptions = false;

    public function sortedOptions(bool|Closure $condition = true): static
    {
        $this->isSortedOptions = $condition;

        return $this;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();

        if ($this->evaluate($this->isSortedOptions)) {
            return collect($options)->sort()->toArray();
        }

        return $options;
    }
}
