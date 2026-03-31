<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Component;
use Metafori\Etno\Filament\Actions\Items\RegenerateIds;

class GroupingStrategySelect extends Select
{
    public const string STRATEGY_MIME_TYPE = 'mime-type';

    public const string STRATEGY_BASENAME = 'basename';

    public const null STRATEGY_NONE = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Grouping Strategy')
            ->inlineLabel()
            ->placeholder('No Grouping')
            ->options([
                self::STRATEGY_MIME_TYPE => 'By MIME Type',
                self::STRATEGY_BASENAME => 'By Basename',
            ])
            ->live()
            ->afterStateUpdated(function (Set $set, Get $get, Component $livewire) {
                $strategy = $get('grouping_strategy');

                $items = $get->array('items');
                $flatFiles = collect($items)
                    ->flatMap(fn ($item) => $item['media_files'] ?? [])
                    ->toArray();

                $record = $livewire->getParentRecord();
                $suffix = RegenerateIds::getNextSequenceSuffix($record);

                $set('items', $livewire->groupFiles($flatFiles, $strategy, $suffix));
            });
    }
}
