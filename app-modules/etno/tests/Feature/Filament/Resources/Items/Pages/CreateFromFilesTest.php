<?php

namespace Metafori\Etno\Tests\Feature\Filament\Resources\Items\Pages;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Forms\Components\Items\GroupingStrategySelect;
use Metafori\Etno\Filament\Resources\Items\Pages\CreateFromFiles;
use Metafori\Etno\Jobs\Items\ProcessMediaUploadJob;
use Metafori\Etno\Models\Document;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Queue::fake();

    $user = User::factory()->admin()->create();
    actingAs($user);

    $this->document = Document::factory()->create();
});

it('dynamically changes grouping strategy with the same files', function () {
    $files = [
        UploadedFile::fake()->create('image.jpg', mimeType: 'image/jpeg'),
        UploadedFile::fake()->create('image.tif', mimeType: 'image/tiff'),
        UploadedFile::fake()->create('image.png', mimeType: 'image/png'),
        UploadedFile::fake()->create('photo.png', mimeType: 'image/png'),
    ];

    $livewire = livewire(CreateFromFiles::class, [
        'parentRecord' => $this->document,
    ]);

    // no grouping
    $livewire
        ->fillForm([
            'attachments' => $files,
            'grouping_strategy' => GroupingStrategySelect::STRATEGY_NONE,
        ])
        ->assertFormSet([
            'grouping_strategy' => GroupingStrategySelect::STRATEGY_NONE,
        ]);

    $items = collect($livewire->get('data.items'));
    expect($items)->toHaveCount(4)
        ->map->media_files->each->toHaveCount(1);

    // by mime type
    $livewire
        ->fillForm([
            'grouping_strategy' => GroupingStrategySelect::STRATEGY_MIME_TYPE,
        ]);

    $itemsMime = collect($livewire->get('data.items'));
    expect($itemsMime)->toHaveCount(3)
        ->map(fn ($item) => \count($item['media_files']))
        ->sequence(1, 1, 2);

    // by basename
    $livewire
        ->fillForm([
            'grouping_strategy' => GroupingStrategySelect::STRATEGY_BASENAME,
        ]);

    $itemsBasename = collect($livewire->get('data.items'));
    expect($itemsBasename)->toHaveCount(2)
        ->map(fn ($item) => \count($item['media_files']))
        ->sequence(3, 1);

    $livewire->call('createFromFiles')
        ->assertHasNoFormErrors();

    Queue::assertPushed(ProcessMediaUploadJob::class, 4);
});
