<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Filament\Forms\Components\Textarea;

class TranscriptTextarea extends Textarea
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
            'x-on:dragover.prevent' => '',
        ];

        $this->rows(8)
            ->live(onBlur: true)
            ->extraInputAttributes($dropzoneAttributes);
    }
}
