<?php

namespace Metafori\Etno\Filament\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;

class MediaFilesRepeaterHelper
{
    /**
     * Builds the standard transcript UI schema for bulk media uploads.
     */
    public static function schema(array $additionalTopFields = []): array
    {
        return array_merge($additionalTopFields, [
            Hidden::make('tmp_uuid'),
            Hidden::make('path'),
            Hidden::make('basename'),
            Textarea::make('custom_properties.transcript_xml')
                ->label('Transcript (XML)')
                ->live(onBlur: true),
            Textarea::make('custom_properties.transcript_txt')
                ->label('Transcript (TXT)')
                ->live(onBlur: true),
        ]);
    }

    /**
     * Attaches standard Livewire-updated Repeater headers for visualizing transcripts.
     */
    public static function configure(Repeater $repeater): Repeater
    {
        return $repeater
            ->extraItemActions([
                Action::make('has_xml')
                    ->label('XML')
                    ->tooltip('XML Transcript Attached')
                    ->icon(Heroicon::CheckCircle)
                    ->color('primary')
                    ->disabled()
                    ->hidden(fn (array $arguments, Repeater $component): bool => empty($component->getItemState($arguments['item'])['custom_properties']['transcript_xml'] ?? null)),

                Action::make('has_txt')
                    ->label('TXT')
                    ->tooltip('TXT Transcript Attached')
                    ->icon(Heroicon::CheckCircle)
                    ->color('primary')
                    ->disabled()
                    ->hidden(fn (array $arguments, Repeater $component): bool => empty($component->getItemState($arguments['item'])['custom_properties']['transcript_txt'] ?? null)),
            ])
            ->collapsible()
            ->collapsed(fn (array $state) => ! isset($state['custom_properties']['transcript_xml']) && ! isset($state['custom_properties']['transcript_txt']));
    }
}
