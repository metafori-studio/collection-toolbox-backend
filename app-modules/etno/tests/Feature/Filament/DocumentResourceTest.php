<?php

namespace Metafori\Etno\Tests\Feature\Filament;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\User;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Filament\Resources\Documents\Pages\CreateDocument;
use Metafori\Etno\Filament\Resources\Documents\Pages\EditDocument;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Project;
use Metafori\Etno\Support\Frontend;

use function Pest\Livewire\livewire;

it('saves correctly for primitive fields on document form', function (string $column, mixed $value) {
    $value = value($value);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            $column => $value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $document->refresh();

    if (is_iterable($document->$column)) {
        expect(collect($document->$column)->toArray())->toEqual(collect($value)->toArray());
    } else {
        expect($document->$column)->toEqual($value);
    }
})->with('document_inputs_primitive');

it('saves correctly for translatable fields on document form', function (string $column, array $value) {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            $column => $value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $document->refresh();

    expect($document->getTranslations($column))->toEqual($value);
})->with('document_inputs_translatable');

it('saves correctly for relational fields on document form', function (string $column, mixed $value) {
    $value = value($value);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            $column => $value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $document->refresh();

    expect($document->$column)->toBe($value);
})->with('document_inputs_relational_belongsto');

dataset('document_inputs_translatable', [
    'title' => ['title', ['en' => 'Document Title EN', 'sk' => 'Document Title SK']],
    'subtitle' => ['subtitle', ['en' => 'Document Subtitle EN', 'sk' => 'Document Subtitle SK']],
    'abstract' => ['abstract', ['en' => 'Document Abstract EN', 'sk' => 'Document Abstract SK']],
    'content_note' => ['content_note', ['en' => 'Document Content Note EN', 'sk' => 'Document Content Note SK']],
    'general_note' => ['general_note', ['en' => 'Document General Note EN', 'sk' => 'Document General Note SK']],
    'location_note' => ['location_note', ['en' => 'Document Location Note EN', 'sk' => 'Document Location Note SK']],
    'technical_note' => ['technical_note', ['en' => 'Document Technical Note EN', 'sk' => 'Document Technical Note SK']],
    'terms_of_use' => ['terms_of_use', ['en' => 'Document Terms of Use EN', 'sk' => 'Document Terms of Use SK']],
]);

dataset('document_inputs_primitive', [
    'languages' => ['languages', [Language::English]],
    'collection_method' => ['collection_method', CollectionMethod::FieldResearch],
    'accrual_method' => ['accrual_method', AccrualMethod::Donation],
    'type' => ['type', ItemType::BachelorsThesis],
    'access_rights' => ['access_rights', AccessRights::RestrictedAccess],
    'license' => ['license', License::CcByNc],
    'production_methods' => ['production_methods', [ProductionMethod::DotMatrixPrinting]],
    'doi' => ['doi', '10.5678/567'],
    'extents' => ['extents', [['value' => '20.0', 'unit' => ExtentUnit::Drawing->value]]],
]);

dataset('document_inputs_relational_belongsto', [
    'institution_id' => ['institution_id', fn () => Organization::factory()->create()->id],
    'project_id' => ['project_id', fn () => Project::factory()->create()->id],
]);

it('shows view_frontend action and hides unpublished text when document is published', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->published(true)->create();

    $expectedUrl = Frontend::documentUrl($document->id);

    livewire(EditDocument::class, ['record' => $document->id])
        ->assertActionVisible('view_frontend')
        ->assertActionHasUrl('view_frontend', $expectedUrl)
        ->assertActionShouldOpenUrlInNewTab('view_frontend')
        ->assertActionHidden('unpublished');
});

it('shows unpublished text and hides view_frontend action when document is unpublished', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->published(false)->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->assertActionVisible('unpublished')
        ->assertActionHidden('view_frontend');
});

it('can create a document with a unique id', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    livewire(CreateDocument::class)
        ->fillForm([
            'id' => 'DOC12345',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Document::find('DOC12345'))->not->toBeNull();
});

it('cannot create a document with a duplicate active id', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    Document::factory()->create(['id' => 'DOC12345']);

    livewire(CreateDocument::class)
        ->fillForm([
            'id' => 'DOC12345',
        ])
        ->call('create')
        ->assertHasFormErrors(['id' => 'unique'])
        ->assertNotNotified();
});

it('cannot create a document with an id matching a soft-deleted document', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create(['id' => 'DOC12345']);
    $document->delete();

    livewire(CreateDocument::class)
        ->fillForm([
            'id' => 'DOC12345',
        ])
        ->call('create')
        ->assertHasFormErrors(['id' => __('etno::ui.validation.id_trashed')])
        ->assertNotified(
            Notification::make()
                ->warning()
                ->title(__('etno::ui.notifications.document_id_trashed.title'))
                ->body(__('etno::ui.notifications.document_id_trashed.body', ['id' => $document->id]))
                ->actions([
                    Action::make('view_document')
                        ->label(__('etno::ui.actions.view_document'))
                        ->button()
                        ->url(DocumentResource::getUrl('edit', ['record' => $document])),
                ])
                ->persistent()
        );

    expect(Document::withTrashed()->where('id', 'DOC12345')->count())->toBe(1);
});

it('can edit a soft-deleted document without id validation error', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create(['id' => 'DOC12345']);
    $document->delete();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            'doi' => '10.5678/999',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($document->fresh()->doi)->toBe('10.5678/999');
});
