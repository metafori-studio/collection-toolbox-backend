<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Filament\Forms\Components\Repeater as BaseRepeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Metafori\Etno\Filament\Forms\Components\MediaFilesRepeater;

class ItemsFromFilesRepeater extends BaseRepeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            TextInput::make('id')
                ->required()
                ->label('New Item ID'),

            MediaFilesRepeater::make('media_files')
                ->reorderable(fn (Get $get) => $get('../../grouping_strategy') !== GroupingStrategySelect::STRATEGY_NONE)
                ->reorderableWithDragAndDrop()
                ->reorderableWithButtons()
                ->columnSpanFull(),
        ])
            ->collapsible()
            ->itemLabel(fn (array $state) => $state['group_name'])
            ->hiddenLabel()
            ->addable(false)
            ->reorderableWithDragAndDrop()
            ->reorderableWithButtons()
            ->defaultItems(0)
            ->columns(2);
    }
}
