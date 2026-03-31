<?php

namespace Metafori\Etno\Jobs\Items;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

class ProcessMediaUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Item $item,
        public string $tempFilePath,
        public string $originalFilename,
        public string $fromDisk,
        public string $collectionName = 'default',
        public array $customProperties = [],
        public ?User $user = null,
    ) {}

    public function handle(ItemRepository $repository): void
    {
        try {
            $this->item->addMediaFromDisk($this->tempFilePath, $this->fromDisk)
                ->usingName(pathinfo($this->originalFilename, PATHINFO_FILENAME))
                ->usingFileName($this->originalFilename)
                ->withCustomProperties($this->customProperties)
                ->toMediaCollection($this->collectionName);
        } finally {
            $repository->decrementPendingMediaUploads($this->item);
        }
    }

    public function failed(): void
    {
        if ($this->user) {
            Notification::make()
                ->title('Media upload failed')
                ->body("Media upload failed for file {$this->originalFilename} in {$this->item->id}")
                ->actions([
                    $this->viewItemMediaAction(),
                ])
                ->danger()
                ->sendToDatabase($this->user);
        }
    }

    protected function viewItemMediaAction(): Action
    {
        $url = ItemResource::getUrl('edit', [
            'record' => $this->item,
            'relation' => 'media',
        ]);

        return Action::make()
            ->label('View Item Media')
            ->color('primary')
            ->url($url);
    }
}
