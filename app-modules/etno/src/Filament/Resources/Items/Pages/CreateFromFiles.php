<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Metafori\Etno\Filament\Actions\Items\RegenerateIds;
use Metafori\Etno\Filament\Concerns\HandlesMediaUploads;
use Metafori\Etno\Filament\Forms\Components\Items\BulkMediaFileUpload;
use Metafori\Etno\Filament\Forms\Components\Items\GroupingStrategySelect;
use Metafori\Etno\Filament\Forms\Components\Items\ItemsFromFilesRepeater;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Jobs\Items\ProcessMediaUploadJob;
use Metafori\Etno\Repositories\ItemRepository;

class CreateFromFiles extends Page implements HasForms
{
    use HandlesMediaUploads;
    use InteractsWithForms;

    protected static string $resource = ItemResource::class;

    protected static ?string $breadcrumb = 'Create From Files';

    public ?array $data = [];

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                GroupingStrategySelect::make('grouping_strategy'),
                BulkMediaFileUpload::make('attachments'),
                Actions::make([
                    Placeholder::make('total')
                        ->label(function (Get $get): string {
                            $items = $get('items') ?? [];
                            $fileCount = collect($items)->sum(fn (array $item): int => \count($item['media_files'] ?? []));

                            return \sprintf('Total: %d item(s) / %d file(s)', \count($items), $fileCount);
                        }),
                    RegenerateIds::make('regenerate_ids'),
                ])->alignBetween(),
                ItemsFromFilesRepeater::make('items'),
                Hidden::make('uploaded_transcripts')
                    ->default([]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->id('form')
                    ->livewireSubmitHandler('createFromFiles')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->key('form-actions'),
                    ]),
            ]);
    }

    public function groupFiles(array $files, ?string $strategy, &$suffix, array $existingItems = []): array
    {
        $groupedFiles = collect($files)->groupBy(fn ($file) => match ($strategy) {
            GroupingStrategySelect::STRATEGY_BASENAME => $file['basename'],
            GroupingStrategySelect::STRATEGY_MIME_TYPE => $file['mime_type'],
            default => uniqid(),
        });

        foreach ($groupedFiles as $groupKey => $fileGroup) {
            $mappedFiles = collect($fileGroup)
                ->mapWithKeys(fn ($file) => [(string) Str::uuid() => $file])
                ->toArray();

            if ($strategy !== GroupingStrategySelect::STRATEGY_NONE) {
                // Try to find an existing item group that matches this key
                $existingIndex = collect($existingItems)
                    ->search(fn ($item) => ($item['group_name'] ?? null) === $groupKey);

                if ($existingIndex !== false) {
                    $existingItems[$existingIndex]['media_files'] ??= [];
                    $existingItems[$existingIndex]['media_files'] += $mappedFiles;

                    continue;
                }
            }

            $displayGroupName = $strategy === GroupingStrategySelect::STRATEGY_NONE
                ? $fileGroup[0]['client_original_name'] ?? ''
                : $groupKey;

            $existingItems[(string) Str::uuid()] = [
                'id' => sprintf('%s:%s', $this->getParentRecord()->id, $suffix),
                'group_name' => $displayGroupName,
                'media_files' => $mappedFiles,
            ];

            $suffix = (string) str_increment((string) $suffix);
        }

        return $existingItems;
    }

    protected function getFormActions(): array
    {
        $itemsUrl = DocumentResource::getUrl('edit', [
            'record' => $this->getParentRecord(),
            'relation' => 'items',
        ]);

        return [
            Action::make('createFromFiles')
                ->label('Create Items')
                ->submit('createFromFiles'),
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url($itemsUrl),
        ];
    }

    public function createFromFiles(ItemRepository $repository): void
    {
        $data = $this->form->getState();
        $items = $this->getParentRecord()->items();

        foreach ($data['items'] ?? [] as $itemData) {
            $createdItem = $items->create(['id' => $itemData['id']]);

            foreach ($itemData['media_files'] ?? [] as $fileData) {
                if (empty($fileData['path'])) {
                    continue;
                }

                ProcessMediaUploadJob::dispatch(
                    $createdItem,
                    $fileData['path'],
                    $fileData['client_original_name'],
                    config('media-library.disk_name'),
                    customProperties: $fileData['custom_properties'] ?? [],
                    user: auth()->user(),
                );

                $repository->incrementPendingMediaUploads($createdItem);
            }
        }

        $this->redirect(DocumentResource::getUrl('edit', [
            'record' => $this->getParentRecord(),
            'relation' => 'items',
        ]));
    }
}
