<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Resources\Items\Schemas\MediaForm;

class MediaRepeater extends Repeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $transcriptActions = collect(TranscriptFormat::cases())
            ->map(
                fn (TranscriptFormat $format) => Action::make("has_{$format->value}")
                    ->tooltip("{$format->getLabel()} Transcript Attached")
                    ->icon(Heroicon::CheckCircle)
                    ->color('primary')
                    ->disabled(true)
                    ->hidden(fn (array $arguments, self $component): bool => ! $this->hasTranscript($format, $arguments['item'], $component))
            );

        $this->extraItemActions([
            ...$transcriptActions,
            Action::make('toggle_details')
                ->label('Transcript')
                ->icon(Heroicon::PencilSquare)
                ->link()
                ->color('gray')
                ->alpineClickHandler('isCollapsed = !isCollapsed'),
        ])
            ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                $mimeTypes = collect($value)
                    ->pluck('file')
                    ->map->getMimeType()
                    ->unique();

                if ($mimeTypes->count() > 1) {
                    $fail('The mime type of the file must match the other media files.');
                }
            })
            ->schema([
                \Filament\Forms\Components\Hidden::make('file'),
                ...MediaForm::transcriptFields(),
            ])
            ->columns(2)
            ->disableLabel()
            ->itemLabel(fn (array $state) => $state['file']->getClientOriginalName())
            ->collapsible()
            ->collapsed(true)
            ->defaultItems(0)
            ->addable(false)
            ->reorderableWithDragAndDrop()
            ->hintAction(
                Action::make('order_by_name')
                    ->hidden(fn (array $state) => \count($state ?? []) < 2)
                    ->label('Order by Name')
                    ->icon(Heroicon::BarsArrowDown)
                    ->action(function (self $component) {
                        $state = $component->getState();
                        uasort($state, fn ($a, $b) => strnatcasecmp(
                            $a['file']->getClientOriginalName(),
                            $b['file']->getClientOriginalName()
                        ));
                        $component->state($state);
                    })
            );
    }

    public function hasTranscript(TranscriptFormat $format, string $item, Repeater $component): bool
    {
        $state = $component->getState()[$item] ?? [];

        return isset($state['custom_properties']['transcripts'][$format->value]);
    }
}
