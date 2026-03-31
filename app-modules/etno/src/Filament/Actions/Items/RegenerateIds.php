<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Models\Document;

class RegenerateIds extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Regenerate IDs')
            ->icon(Heroicon::ArrowPath)
            ->visible(fn (Get $get) => ! empty($get('items')))
            ->action(function (Get $get, Set $set, \Livewire\Component $livewire) {
                $this->regenerateIds($get, $set, $livewire);
            });
    }

    public function regenerateIds(Get $get, Set $set, \Livewire\Component $livewire): void
    {
        $record = $livewire->getParentRecord();
        $items = $get->array('items');
        $suffix = self::getNextSequenceSuffix($record);

        foreach ($items as $key => $itemData) {
            $items[$key]['id'] = $record ? "{$record->id}:{$suffix}" : $suffix;
            $suffix = (string) str_increment($suffix);
        }

        $set('items', $items);
    }

    public static function getNextSequenceSuffix(Document $parentRecord, array $currentItems = []): string
    {
        $suffixes = [];

        if ($parentRecord) {
            $suffixes = $parentRecord->items()
                ->pluck('id')
                ->map(fn ($id) => str((string) $id)->afterLast(':')->toString())
                ->filter(fn ($s) => preg_match('/^[a-z]+$/', $s))
                ->toArray();
        }

        $formItemSuffixes = collect($currentItems)
            ->pluck('id')
            ->map(fn ($id) => str((string) $id)->afterLast(':')->toString())
            ->filter(fn ($s) => preg_match('/^[a-z]+$/', $s))
            ->toArray();

        $suffixes = array_merge($suffixes, $formItemSuffixes);

        if (empty($suffixes)) {
            return 'a';
        }

        usort($suffixes, function ($a, $b) {
            if (strlen($a) === strlen($b)) {
                return strcmp($a, $b);
            }

            return strlen($a) - strlen($b);
        });

        $maxSuffix = end($suffixes);

        return (string) str_increment($maxSuffix);
    }
}
