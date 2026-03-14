<?php

namespace Metafori\Core\Filament\Forms\Components\Concerns;

trait SavesOrder
{
    public function saveOrder(string $column = 'sort_order'): static
    {
        return $this->saveRelationshipsUsing(function (self $component) use ($column) {
            $state = collect($component->getState())
                ->mapWithKeys(fn ($id, $index) => [$id => [$column => $index]])
                ->toArray();

            $component->getRelationship()->sync($state);
        });
    }
}
