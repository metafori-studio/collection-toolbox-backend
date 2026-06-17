<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\FileUpload;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Actions\Items\RegenerateIds;
use Metafori\Etno\Filament\Concerns\HandlesMediaUploads;
use Metafori\Etno\Filament\Concerns\WithDocument;
use Metafori\Etno\Filament\Contracts\HasDocument;
use Metafori\Etno\Filament\Forms\Components\Items\ItemsFromFilesRepeater;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

class CreateItemsFromFiles extends Page implements HasDocument, HasForms
{
    use HandlesMediaUploads;
    use InteractsWithForms;
    use WithDocument;

    protected static string $resource = ItemResource::class;

    public function getBreadcrumb(): string
    {
        return __('etno::ui.pages.create_items_from_files.breadcrumb');
    }

    public function getTitle(): string
    {
        return __('etno::ui.pages.create_items_from_files.breadcrumb');
    }

    public ?array $data = [];

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Wizard::make([
                    Step::make(__('etno::ui.pages.create_items_from_files.steps.upload_files'))
                        ->schema([
                            FileUpload::make('files')
                                ->acceptedFileTypes([
                                    ...(new Item)->allowedMediaMimeTypes(),
                                    ...TranscriptFormat::mimeTypes(),
                                ])
                                ->required()
                                ->previewable(false)
                                ->multiple()
                                ->hiddenLabel()
                                ->storeFiles(false),
                        ])
                        ->afterValidation(function (Set $set, Get $get) {
                            $files = $get->array('files');
                            $suffix = $this->getDocument()
                                ->generateNextSequenceSuffix();

                            [$transcripts, $mediaFiles] = $this->extractTranscripts($files);
                            $items = $this->syncItems($mediaFiles, $suffix);
                            $items = $this->applyTranscriptsToItems($items, $transcripts);

                            $set('items', $items);
                        }),
                    Step::make(__('etno::ui.pages.create_items_from_files.steps.review_rearrange'))
                        ->schema([
                            RegenerateIds::make('regenerate_ids'),
                            ItemsFromFilesRepeater::make('items'),
                            Hidden::make('transcripts')
                                ->default([]),
                        ]),
                ])
                    ->submitAction(
                        Action::make('create')
                            ->label(__('etno::ui.actions.create_items'))
                            ->submit('createFromFiles')
                    ),
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
                    ->livewireSubmitHandler('createFromFiles'),
            ]);
    }

    protected function syncItems($files, string $suffix): array
    {
        $items = [];
        foreach ($files as $uuid => $file) {
            $items[$uuid] = [
                'suffix' => $suffix,
                'media' => [
                    'file' => $file,
                    'custom_properties' => [],
                ],
            ];

            $suffix = Document::incrementSuffix($suffix);
        }

        return $items;
    }

    public function createFromFiles(): void
    {
        $data = $this->form->getState();
        $items = $this->getDocument()->items();

        foreach ($data['items'] as $itemData) {
            $createdItem = $items->create(['suffix' => $itemData['suffix']]);
            $this->addItemMedia($createdItem, $itemData['media']['file'], $itemData['media']['custom_properties'] ?? []);
        }

        $this->redirect(DocumentResource::getUrl('edit', [
            'record' => $this->getParentRecord(),
            'relation' => 'items',
        ]));
    }
}
