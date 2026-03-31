<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Enums\TranscriptFormat;

class MediaFilesRepeater extends Repeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $dropzoneJs = <<<'JS'
            let file = $event.dataTransfer.files[0];
            if (!file) return;
            let reader = new FileReader();
            reader.onload = (e) => {
                $el.value = e.target.result;
                $el.dispatchEvent(new Event('input', { bubbles: true }));
                $el.dispatchEvent(new Event('change', { bubbles: true }));
                $el.dispatchEvent(new Event('blur', { bubbles: true }));
            };
            reader.readAsText(file);
        JS;

        $dropzoneAttributes = [
            'x-on:drop.prevent' => $dropzoneJs,
        ];

        $transcriptActions = [];
        $transcriptFields = [];

        foreach (TranscriptFormat::cases() as $format) {
            $transcriptActions[] = Action::make("has_{$format->value}")
                ->label($format->getLabel())
                ->tooltip("{$format->getLabel()} Transcript Attached")
                ->icon(Heroicon::CheckCircle)
                ->color('primary')
                ->disabled(true)
                ->hidden(fn (array $arguments, self $component): bool => ! isset($arguments['item']) || ! $this->hasTranscript($format, $arguments['item'], $component));

            $transcriptFields[] = Textarea::make("custom_properties.{$format->getCustomPropertyKey()}")
                ->rows(8)
                ->label("Transcript ({$format->getLabel()})")
                ->placeholder("You can drag & drop a {$format->getLabel()} file here...")
                ->live(onBlur: true)
                ->extraInputAttributes($dropzoneAttributes);
        }

        $this->extraItemActions([
            ...$transcriptActions,
            Action::make('toggle_details')
                ->label('Transcript')
                ->icon('heroicon-m-pencil-square')
                ->link()
                ->color('gray')
                ->alpineClickHandler('isCollapsed = !isCollapsed'),
        ])
            ->schema([
                Hidden::make('tmp_uuid'),
                Hidden::make('path'),
                Hidden::make('basename'),
                ...$transcriptFields,
            ])
            ->itemLabel(fn (array $state) => $state['client_original_name'])
            ->collapsible()
            ->collapsed(function (array $state): bool {
                foreach (TranscriptFormat::cases() as $format) {
                    if (isset($state['custom_properties'][$format->getCustomPropertyKey()])) {
                        return false;
                    }
                }

                return true;
            })
            ->defaultItems(0)
            ->addable(false)
            ->reorderableWithDragAndDrop()
            ->reorderableWithButtons();
    }

    public function hasTranscript(TranscriptFormat $format, string $item, Repeater $component): bool
    {
        $state = $component->getState()[$item] ?? [];

        return ! empty($state['custom_properties'][$format->getCustomPropertyKey()]);
    }
}
