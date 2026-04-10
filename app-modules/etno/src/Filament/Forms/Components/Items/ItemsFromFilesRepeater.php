<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Actions\Items\OrderFilesByNameAction;
use Metafori\Etno\Filament\Forms\Components\SuffixInput;
use Metafori\Etno\Filament\Resources\Items\Schemas\MediaForm;

class ItemsFromFilesRepeater extends Repeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $transcriptActions = array_map(
            fn (TranscriptFormat $format) => Action::make("has_{$format->value}")
                ->tooltip("{$format->getLabel()} Transcript Attached")
                ->icon(Heroicon::CheckCircle)
                ->color('primary')
                ->disabled()
                ->hidden(fn (array $arguments, self $component): bool => ! $this->hasTranscript($format, $arguments['item'], $component)),
            TranscriptFormat::cases()
        );

        $this
            ->extraItemActions($transcriptActions)
            ->table([
                TableColumn::make('New Item ID')
                    ->markAsRequired(),
                TableColumn::make('Media Files')
                    ->width('80%'),
            ])
            ->compact()
            ->schema([
                SuffixInput::make('suffix')
                    ->distinct(),

                Section::make(fn ($state) => $state['file']->getClientOriginalName())
                    ->schema([
                        Hidden::make('file'),
                        ...MediaForm::transcriptFields(),
                    ])
                    ->statePath('media')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ])
            ->hiddenLabel()
            ->collapsible()
            ->collapsed()
            ->addable(false)
            ->reorderableWithDragAndDrop()
            ->hintAction(
                OrderFilesByNameAction::make('order_by_name')
                    ->fileStatePath('media.file')
            );
    }

    public function hasTranscript(TranscriptFormat $format, string $item, self $component): bool
    {
        $state = $component->getState()[$item] ?? [];

        return isset($state['media']['custom_properties']['transcripts'][$format->value]);
    }
}
