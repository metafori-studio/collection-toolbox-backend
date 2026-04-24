<?php

namespace Metafori\Etno\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\FailOnException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

class ProcessItemMediaUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Item $item,
        public string $filePath,
        public string $originalName,
        public string $mimeType,
        public array $customProperties = []
    ) {}

    public function handle(ItemRepository $itemRepository): void
    {
        $collection = Item::getMediaCollectionName($this->mimeType) ?? throw new InvalidArgumentException("Unsupported mime type: {$this->mimeType}");

        $this->item->addMediaFromDisk($this->filePath, FileUploadConfiguration::disk())
            ->usingName(File::name($this->originalName))
            ->usingFileName($this->originalName)
            ->withCustomProperties($this->customProperties)
            ->toMediaCollection($collection);

        $itemRepository->decrementProcessingMediaCount($this->item);
    }

    public function middleware(): array
    {
        return [
            new FailOnException([InvalidArgumentException::class]),
        ];
    }
}
