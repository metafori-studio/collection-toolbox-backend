<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Filament\Contracts\HasDocument;
use Metafori\Etno\Models\Document;

class RegenerateIds extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.actions.regenerate_ids'))
            ->icon(Heroicon::ArrowPath)
            ->visible(fn (Get $get) => ! empty($get->array('items')))
            ->action($this->regenerateIds(...));
    }

    public function regenerateIds(Get $get, Set $set, HasDocument $livewire): void
    {
        $items = $get->array('items');
        $suffix = $livewire->getDocument()
            ->generateNextSequenceSuffix();

        foreach (array_keys($items) as $key) {
            $items[$key]['suffix'] = $suffix;
            $suffix = Document::incrementSuffix($suffix);
        }

        $set('items', $items);
    }
}
