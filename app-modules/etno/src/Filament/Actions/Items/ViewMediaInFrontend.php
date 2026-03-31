<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Support\Frontend;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ViewMediaInFrontend extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('View in Frontend')
            ->icon(Heroicon::ArrowTopRightOnSquare)
            ->url(fn (Media $record, Frontend $frontend): string => $frontend->itemUrl($record->model->id))
            ->openUrlInNewTab()
            ->link();
    }
}
