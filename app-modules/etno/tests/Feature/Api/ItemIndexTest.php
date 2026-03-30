<?php

use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\ItemOriginator;
use Metafori\Etno\Models\Project;
use Metafori\Etno\Models\ResearchCollection;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;

uses(RefreshIndices::class);

it('can list items', function () {
    $document = Document::factory()
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasOriginators(2);
    Item::factory()
        ->count(2)
        ->for($document, 'document')
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index'));

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'document_id',
                    'title',
                    'authors' => [
                        '*' => [
                            'id',
                            'given_name',
                            'family_name',
                        ],
                    ],
                    'researchers' => [
                        '*' => [
                            'id',
                            'given_name',
                            'family_name',
                        ],
                    ],
                    'originators' => [
                        '*' => [
                            'id',
                            'person' => [
                                'id',
                                'given_name',
                                'family_name',
                            ],
                            'label',
                        ],
                    ],
                    'locality' => [
                        'id',
                        'name',
                    ],
                ],
            ],
            'meta',
            'links',
        ])
        ->assertJsonCount(2, 'data.0.authors')
        ->assertJsonCount(2, 'data.0.researchers')
        ->assertJsonCount(2, 'data.0.originators');
});

it('can filter items by simple property', function (string $property, string $enumClass) {
    $cases = collect($enumClass::cases());
    $matchingValue = $cases->first();
    $otherValue = $cases->last();

    $matching = Item::factory()->create([
        $property => $matchingValue,
        'document_overrides' => [$property],
    ]);

    Item::factory()->count(2)->create([
        $property => $otherValue,
        'document_overrides' => [$property],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [$property => [$matchingValue->value]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
})->with([
    'type' => ['type', ItemType::class],
    'language' => ['language', Language::class],
    'accrual_method' => ['accrual_method', AccrualMethod::class],
    'collection_method' => ['collection_method', CollectionMethod::class],
    'access_rights' => ['access_rights', AccessRights::class],
    'license' => ['license', License::class],
]);

it('can filter items by simple property inherited', function (string $property, string $enumClass) {
    $cases = collect($enumClass::cases());
    $matchingValue = $cases->first();
    $otherValue = $cases->last();

    Item::factory()
        ->count(2)
        ->for(Document::factory()->create([$property => $otherValue]), 'document')
        ->create();

    $matching = Item::factory()
        ->for(Document::factory()->create([$property => $matchingValue]), 'document')
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [$property => [$matchingValue->value]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
})->with([
    'type' => ['type', ItemType::class],
    'language' => ['language', Language::class],
    'accrual_method' => ['accrual_method', AccrualMethod::class],
    'collection_method' => ['collection_method', CollectionMethod::class],
    'access_rights' => ['access_rights', AccessRights::class],
    'license' => ['license', License::class],
]);

it('can filter items by array property', function () {
    $matching = Item::factory()->create([
        'production_methods' => [ProductionMethod::Drawing],
        'document_overrides' => ['production_methods'],
    ]);

    Item::factory()->create([
        'production_methods' => [ProductionMethod::Painting],
        'document_overrides' => ['production_methods'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ['production_methods' => [ProductionMethod::Drawing->value]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('can sort items', function () {
    $itemA = Item::factory()->create([
        'type' => ItemType::AudioRecording,
        'document_overrides' => ['type'],
    ]);
    $itemC = Item::factory()->create([
        'type' => ItemType::Map,
        'document_overrides' => ['type'],
    ]);
    $itemB = Item::factory()->create([
        'type' => ItemType::Drawing,
        'document_overrides' => ['type'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', ['sort' => '-type']));

    $response->assertStatus(200);
    $data = collect($response->json('data'));

    expect($data->pluck('id')->toArray())->toBe([$itemC->id, $itemB->id, $itemA->id]);
});

it('can filter items by belongsTo property', function (string $propertyKey, string $factoryClass) {
    $matchingEntity = $factoryClass::factory()->create();
    $otherEntity = $factoryClass::factory()->create();

    $matching = Item::factory()->create([
        "{$propertyKey}_id" => $matchingEntity->id,
        'document_overrides' => [$propertyKey],
    ]);

    Item::factory()->create([
        "{$propertyKey}_id" => $otherEntity->id,
        'document_overrides' => [$propertyKey],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ["{$propertyKey}.id" => [$matchingEntity->id]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
})->with([
    'institution' => ['institution', Organization::class],
    'project' => ['project', Project::class],
]);

it('can filter items by belongsToMany property', function (string $propertyKey, string $relation, string $factoryClass, string $filterCol) {
    $matchingEntity = $factoryClass::factory()->create();
    $otherEntity = $factoryClass::factory()->create();

    $matching = Item::factory()
        ->hasAttached($matchingEntity, [], $relation)
        ->create([
            'document_overrides' => [$relation],
        ]);

    Item::factory()
        ->hasAttached($otherEntity, [], $relation)
        ->create([
            'document_overrides' => [$relation],
        ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ["{$propertyKey}.{$filterCol}" => [$matchingEntity->id]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
})->with([
    'author' => ['author', 'authors', Person::class, 'person_id'],
    'researcher' => ['researcher', 'researchers', Person::class, 'person_id'],
    'keyword' => ['keyword', 'keywords', Keyword::class, 'id'],
    'research_collection' => ['research_collection', 'researchCollections', ResearchCollection::class, 'id'],
]);

it('can filter items by originator', function () {
    $matchingPerson = Person::factory()->create();
    $otherPerson = Person::factory()->create();

    $matching = Item::factory()->create(['document_overrides' => ['originators']]);
    ItemOriginator::factory()->create([
        'item_id' => $matching->id,
        'person_id' => $matchingPerson->id,
    ]);

    $other = Item::factory()->create(['document_overrides' => ['originators']]);
    ItemOriginator::factory()->create([
        'item_id' => $other->id,
        'person_id' => $otherPerson->id,
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ['originator.person_id' => [$matchingPerson->id]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('can filter items by locality', function (string $propertyKey, string $factoryClass) {
    $matchingLocality = $factoryClass::factory()->create();
    $otherLocality = $factoryClass::factory()->create();

    $matchingItem = Item::factory()
        ->for(Document::factory()->for($matchingLocality, 'locality'), 'document')
        ->create();

    Item::factory()
        ->for(Document::factory()->for($otherLocality, 'locality'), 'document')
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ["{$propertyKey}.id" => [$matchingLocality->id]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingItem->id);
})->with([
    'country' => ['country', Country::class],
    'region' => ['region', Region::class],
    'district' => ['district', District::class],
    'municipality' => ['municipality', Municipality::class],
    'municipality_part' => ['municipality_part', MunicipalityPart::class],
    'location' => ['location', Location::class],
]);
