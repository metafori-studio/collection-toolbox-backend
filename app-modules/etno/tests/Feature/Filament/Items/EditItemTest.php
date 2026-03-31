<?php

namespace Metafori\Etno\Tests\Feature\Filament;

use Carbon\CarbonInterface;
use Filament\Actions\Testing\TestAction;
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
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\Project;

use function Pest\Livewire\livewire;

it('creates item with unique identifier even when soft deleted record exists', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();
    $item = Item::factory()->create([
        'document_id' => $document->id,
    ]);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm([
            'suffix' => '1',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    expect($item->suffix)->toEqual('1');
    expect($item->identifier)->toEqual($document->id.':1');
});

it('saves and overrides correctly for primitive fields', function (string $column, mixed $parentValue, mixed $overrideValue) {
    $parentValue = value($parentValue);
    $overrideValue = value($overrideValue);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $documentData = [];
    $documentData[$column] = $parentValue;

    $document = Document::factory()->create($documentData);

    $itemData = ['document_id' => $document->id];
    $itemData[$column] = null;

    $item = Item::factory()->create($itemData);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make($column.'_toggle_inheritance')->schemaComponent($column))
        ->fillForm([
            $column => $overrideValue,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    if (is_iterable($item->$column)) {
        expect(collect($item->$column)->toArray())->toEqual(collect($overrideValue)->toArray());
    } else {
        expect($item->$column)->toEqual($overrideValue);
    }

    expect($item->isInherited($column))->toBeFalse();
})->with('inheritable_inputs_primitive');

it('saves and overrides correctly for translatable fields', function (string $column, array $parentValue, array $overrideValue) {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $documentData = [];
    $documentData[$column] = $parentValue;

    $document = Document::factory()->create($documentData);

    $itemData = ['document_id' => $document->id];
    $itemData[$column] = null;

    $item = Item::factory()->create($itemData);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make($column.'_toggle_inheritance')->schemaComponent($column.'.en'))
        ->fillForm([
            $column => $overrideValue,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    expect($item->getTranslations($column))->toEqual($overrideValue)
        ->and($item->isInherited($column))->toBeFalse();
})->with('inheritable_inputs_translatable');

it('saves and overrides correctly for relational fields', function (string $column, mixed $parentValue, mixed $overrideValue) {
    $parentValue = value($parentValue);
    $overrideValue = value($overrideValue);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $documentData = [
        'extents' => null,
        'time_period_start' => null,
        'time_period_end' => null,
        'submission_date_start' => null,
        'submission_date_end' => null,
        'publication_date_start' => null,
        'publication_date_end' => null,
    ];
    $documentData[$column] = $parentValue;

    $document = Document::factory()->create($documentData);

    $itemData = [
        'document_id' => $document->id,
        'document_overrides' => [],
        'extents' => null,
        'time_period_start' => null,
        'time_period_end' => null,
        'submission_date_start' => null,
        'submission_date_end' => null,
        'publication_date_start' => null,
        'publication_date_end' => null,
    ];
    $itemData[$column] = null;

    $item = Item::factory()->create($itemData);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make($column.'_toggle_inheritance')->schemaComponent($column))
        ->fillForm([
            $column => $overrideValue,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    expect($item->$column)->toBe($overrideValue)
        ->and($item->isInherited($column))->toBeFalse();
})->with('inheritable_inputs_relational_belongsto');

it('saves and overrides correctly for relational many fields', function (string $column, \Closure $parentValue, \Closure $overrideValue) {
    $parentValue = $parentValue();
    $overrideValue = $overrideValue();

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $documentData = [
        'extents' => null,
        'time_period_start' => null,
        'time_period_end' => null,
        'submission_date_start' => null,
        'submission_date_end' => null,
        'publication_date_start' => null,
        'publication_date_end' => null,
    ];

    $document = Document::factory()->create($documentData);
    $document->{$column}()->sync($parentValue);

    $itemData = [
        'document_id' => $document->id,
        'document_overrides' => [],
        'extents' => null,
        'time_period_start' => null,
        'time_period_end' => null,
        'submission_date_start' => null,
        'submission_date_end' => null,
        'publication_date_start' => null,
        'publication_date_end' => null,
    ];

    $item = Item::factory()->create($itemData);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make($column.'_toggle_inheritance')->schemaComponent($column))
        ->fillForm([
            $column => $overrideValue,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    expect($item->{$column}->pluck('id')->toArray())->toEqual($overrideValue)
        ->and($item->isInherited($column))->toBeFalse();
})->with('inheritable_inputs_relational_many');

it('validates and saves document_overrides correctly', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $document = Document::factory()->create();
    $item = Item::factory()->create([
        'document_id' => $document->id,
    ]);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm([
            'document_overrides' => ['invalid_override_field'],
        ])
        ->call('save')
        ->assertHasFormErrors(['document_overrides']);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm([
            'document_overrides' => [Item::INHERITABLES[0]],
        ])
        ->call('save')
        ->assertHasNoFormErrors(['document_overrides']);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm([
            'document_overrides' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors(['document_overrides']);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm([
            'document_overrides' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors(['document_overrides']);
});

dataset('inheritable_inputs_translatable', [
    'title' => ['title', ['en' => 'Parent Title EN', 'sk' => 'Parent Title SK'], ['en' => 'Overridden Title EN', 'sk' => 'Overridden Title SK']],
    'subtitle' => ['subtitle', ['en' => 'Parent Subtitle EN', 'sk' => 'Parent Subtitle SK'], ['en' => 'Overridden Subtitle EN', 'sk' => 'Overridden Subtitle SK']],
    'abstract' => ['abstract', ['en' => 'Parent Abstract EN', 'sk' => 'Parent Abstract SK'], ['en' => 'Overridden Abstract EN', 'sk' => 'Overridden Abstract SK']],
    'content_note' => ['content_note', ['en' => 'Parent Content Note EN', 'sk' => 'Parent Content Note SK'], ['en' => 'Overridden Content Note EN', 'sk' => 'Overridden Content Note SK']],
    'general_note' => ['general_note', ['en' => 'Parent General Note EN', 'sk' => 'Parent General Note SK'], ['en' => 'Overridden General Note EN', 'sk' => 'Overridden General Note SK']],
    'location_note' => ['location_note', ['en' => 'Parent Location Note EN', 'sk' => 'Parent Location Note SK'], ['en' => 'Overridden Location Note EN', 'sk' => 'Overridden Location Note SK']],
    'technical_note' => ['technical_note', ['en' => 'Parent Technical Note EN', 'sk' => 'Parent Technical Note SK'], ['en' => 'Overridden Technical Note EN', 'sk' => 'Overridden Technical Note SK']],
    'terms_of_use' => ['terms_of_use', ['en' => 'Parent Terms of Use EN', 'sk' => 'Parent Terms of Use SK'], ['en' => 'Overridden Terms of Use EN', 'sk' => 'Overridden Terms of Use SK']],
]);

dataset('inheritable_inputs_primitive', [
    'language' => ['language', Language::Afar, Language::English],
    'collection_method' => ['collection_method', CollectionMethod::ArchivalResearch, CollectionMethod::FieldResearch],
    'accrual_method' => ['accrual_method', AccrualMethod::Deposit, AccrualMethod::Donation],
    'type' => ['type', ItemType::AudioRecording, ItemType::BachelorsThesis],
    'access_rights' => ['access_rights', AccessRights::OpenAccess, AccessRights::RestrictedAccess],
    'license' => ['license', License::CcBy, License::CcByNc],
    'production_methods' => ['production_methods', [ProductionMethod::AudioRecording], [ProductionMethod::DotMatrixPrinting]],
    'doi' => ['doi', '10.1234/123', '10.5678/567'],
    'extents' => ['extents', [['value' => '10.5', 'unit' => ExtentUnit::Drawing->value]], [['value' => '20.0', 'unit' => ExtentUnit::Duration->value]]],
]);

dataset('inheritable_inputs_relational_belongsto', [
    'institution_id' => ['institution_id', fn () => Organization::factory()->create()->id, fn () => Organization::factory()->create()->id],
    'project_id' => ['project_id', fn () => Project::factory()->create()->id, fn () => Project::factory()->create()->id],
]);

dataset('inheritable_inputs_relational_many', [
    'authors' => ['authors', fn () => [\Metafori\Core\Models\Person::factory()->create()->id], fn () => [\Metafori\Core\Models\Person::factory()->create()->id]],
    'researchers' => ['researchers', fn () => [\Metafori\Core\Models\Person::factory()->create()->id], fn () => [\Metafori\Core\Models\Person::factory()->create()->id]],
]);

it('saves and overrides correctly for precision date sections', function (string $sectionName, array $parentValues, array $overrideValues) {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $documentData = $parentValues;
    $document = Document::factory()->create($documentData);

    $itemData = ['document_id' => $document->id];
    foreach (array_keys($parentValues) as $key) {
        $itemData[$key] = null;
    }

    $item = Item::factory()->create($itemData);

    livewire(EditItem::class, [
        'parentRecord' => $document,
        'record' => $item->id,
    ])
        ->fillForm(array_merge($overrideValues, [
            // @todo call toggle action instead
            'document_overrides' => array_keys($overrideValues),
        ]))
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify database
    $item->refresh();

    foreach ($overrideValues as $key => $value) {
        if ($item->$key instanceof CarbonInterface) {
            $formattedValue = match (true) {
                \strlen($value) === 4 => $item->$key->format('Y'),
                \strlen($value) === 7 => $item->$key->format('Y-m'),
                default => $item->$key->format('Y-m-d'),
            };
            expect($formattedValue)->toEqual($value);
        } else {
            expect($item->$key)->toEqual($value);
        }
        expect($item->isInherited($key))->toBeFalse();
    }
})->with('inheritable_inputs_precision_date');

dataset('inheritable_inputs_precision_date', [
    'time_period' => [
        'time_period',
        ['time_period_start' => '2000-01-01', 'time_period_end' => '2010-12-31', 'time_period_settings' => ['precision' => 'year', 'is_range' => true]],
        ['time_period_start' => '1900-01-01', 'time_period_end' => '1950-12-31', 'time_period_settings' => ['precision' => 'year', 'is_range' => true]],
    ],
    'publication_date' => [
        'publication_date',
        ['publication_date_start' => '2000-01-01', 'publication_date_end' => '2000-01-01', 'publication_date_settings' => ['precision' => 'day']],
        ['publication_date_start' => '1900-01-01', 'publication_date_end' => '1900-01-01', 'publication_date_settings' => ['precision' => 'day']],
    ],
    'submission_date' => [
        'submission_date',
        ['submission_date_start' => '2000-01-01', 'submission_date_end' => '2000-01-31', 'submission_date_settings' => ['precision' => 'month']],
        ['submission_date_start' => '1900-01-01', 'submission_date_end' => '1900-01-31', 'submission_date_settings' => ['precision' => 'month']],
    ],
]);
