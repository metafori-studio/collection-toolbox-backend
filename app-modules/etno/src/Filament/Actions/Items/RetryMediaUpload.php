<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class RetryMediaUpload extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'retry_media_upload';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->label('Retry')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('primary')
            ->action(fn (Notification $notification) => $notification->delete());
    }
}
