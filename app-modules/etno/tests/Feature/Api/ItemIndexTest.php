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

    $matchingItem = Item::factory()->create([
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
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
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

    $matchingItem = Item::factory()
        ->for(Document::factory()->create([$property => $matchingValue]), 'document')
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [$property => [$matchingValue->value]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
})->with([
    'type' => ['type', ItemType::class],
    'language' => ['language', Language::class],
    'accrual_method' => ['accrual_method', AccrualMethod::class],
    'collection_method' => ['collection_method', CollectionMethod::class],
    'access_rights' => ['access_rights', AccessRights::class],
    'license' => ['license', License::class],
]);

it('can filter items by array property', function () {
    $matchingItem = Item::factory()->create([
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
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
});

it('can sort items', function () {
    $items = [
        Item::factory()->create([
            'type' => ItemType::AudioRecording,
            'document_overrides' => ['type'],
        ]),
        Item::factory()->create([
            'type' => ItemType::Drawing,
            'document_overrides' => ['type'],
        ]),
        Item::factory()->create([
            'type' => ItemType::Map,
            'document_overrides' => ['type'],
        ]),
    ];

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', ['sort' => '-type']));

    $response->assertStatus(200);
    $data = collect($response->json('data'));

    expect($data->pluck('id')->toArray())->toBe([$items[2]->identifier, $items[1]->identifier, $items[0]->identifier]);
});

it('can sort items by title using active locale keyword', function () {
    $document1 = Document::factory()->create(['title' => ['en' => 'Zebra']]);
    $document2 = Document::factory()->create(['title' => ['en' => 'Apple']]);
    $document3 = Document::factory()->create(['title' => ['en' => 'Mango']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();
    $item3 = Item::factory()->for($document3)->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', ['sort' => 'title']));
    $response->assertStatus(200);
    $data = collect($response->json('data'));
    expect($data->pluck('id')->toArray())->toBe([$item2->identifier, $item3->identifier, $item1->identifier]);
});

it('can filter items by belongsTo property', function (string $propertyKey, string $factoryClass) {
    $matchingEntity = $factoryClass::factory()->create();
    $otherEntity = $factoryClass::factory()->create();

    $matchingItem = Item::factory()->create([
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
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
})->with([
    'institution' => ['institution', Organization::class],
    'project' => ['project', Project::class],
]);

it('can filter items by belongsToMany property', function (string $propertyKey, string $relation, string $factoryClass, string $filterCol) {
    $matchingEntity = $factoryClass::factory()->create();
    $otherEntity = $factoryClass::factory()->create();

    $matchingItem = Item::factory()
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
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
})->with([
    'author' => ['author', 'authors', Person::class, 'person_id'],
    'researcher' => ['researcher', 'researchers', Person::class, 'person_id'],
    'keyword' => ['keyword', 'keywords', Keyword::class, 'id'],
    'research_collection' => ['research_collection', 'researchCollections', ResearchCollection::class, 'id'],
]);

it('can filter items by originator', function () {
    $matchingPerson = Person::factory()->create();
    $otherPerson = Person::factory()->create();

    $matchingItem = Item::factory()->create(['document_overrides' => ['originators']]);
    ItemOriginator::factory()
        ->for($matchingItem)
        ->for($matchingPerson)
        ->create();

    $other = Item::factory()->create(['document_overrides' => ['originators']]);
    ItemOriginator::factory()
        ->for($other)
        ->for($otherPerson)
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ['originator.person_id' => [$matchingPerson->id]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
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
        ->assertJsonPath('data.0.id', $matchingItem->identifier);
})->with([
    'country' => ['country', Country::class],
    'region' => ['region', Region::class],
    'district' => ['district', District::class],
    'municipality' => ['municipality', Municipality::class],
    'municipality_part' => ['municipality_part', MunicipalityPart::class],
    'location' => ['location', Location::class],
]);

it('can filter items by overlapping time period specifying only lower bound', function () {
    $matching = Item::factory()->create([
        'time_period_start' => '1950-12-31',
        'time_period_end' => '1950-12-31',
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    Item::factory()->create([
        'time_period_start' => '1800-01-01',
        'time_period_end' => '1850-01-01',
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [
            'time_period_from' => 1950,
        ],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->identifier);
});

it('can filter items by overlapping time period specifying only upper bound', function () {
    $matching = Item::factory()->create([
        'time_period_start' => '1950-12-31',
        'time_period_end' => '1950-12-31',
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    Item::factory()->create([
        'time_period_start' => '2000-01-01',
        'time_period_end' => '2000-01-01',
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [
            'time_period_to' => 1950,
        ],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->identifier);
});

it('can filter items by overlapping time period when end date is null', function () {
    $matching = Item::factory()->create([
        'time_period_start' => '1950-12-31',
        'time_period_end' => null,
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    Item::factory()->create([
        'time_period_start' => '1800-01-01',
        'time_period_end' => '1850-01-01',
        'document_overrides' => ['time_period_start', 'time_period_end'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [
            'time_period_from' => 2000,
            'time_period_to' => 2000,
        ],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->identifier);
});

it('validates time period bounds', function () {
    $response = getJson(route('api.etno.items.index', [
        'filter' => [
            'time_period_from' => 1950,
            'time_period_to' => 1949,
        ],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'filter.time_period_to' => 'The filter.time_period_to must be greater than or equal to filter.time_period_from.',
        ]);
});

it('can filter items by all filterables at once', function () {
    $municipalityPart = MunicipalityPart::factory()->create();
    $municipality = $municipalityPart->municipality;
    $district = $municipality->district;
    $region = $district->region;
    $country = $region->country;

    $location = Location::factory()
        ->for($municipalityPart, 'parent')
        ->create();

    $institution = Organization::factory()->create();
    $project = Project::factory()->create();
    $author = Person::factory()->create();
    $researcher = Person::factory()->create();
    $keyword = Keyword::factory()->create();
    $researchCollection = ResearchCollection::factory()->create();
    $originatorPerson = Person::factory()->create();

    $matching = Item::factory()
        ->for($location, 'locality')
        ->hasAttached($author, [], 'authors')
        ->hasAttached($researcher, [], 'researchers')
        ->hasAttached($keyword, [], 'keywords')
        ->hasAttached($researchCollection, [], 'researchCollections')
        ->create([
            'type' => ItemType::AudioRecording,
            'language' => Language::Slovak,
            'accrual_method' => AccrualMethod::Purchase,
            'collection_method' => CollectionMethod::FieldResearch,
            'access_rights' => AccessRights::OpenAccess,
            'license' => License::CcBy,
            'production_methods' => [ProductionMethod::Drawing],
            'institution_id' => $institution->id,
            'project_id' => $project->id,
            'time_period_start' => '1900-01-01',
            'time_period_end' => '1950-01-01',
            'document_overrides' => Item::INHERITABLES,
        ]);

    ItemOriginator::factory()
        ->for($matching)
        ->for($originatorPerson)
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => [
            'type' => [ItemType::AudioRecording->value],
            'language' => [Language::Slovak->value],
            'accrual_method' => [AccrualMethod::Purchase->value],
            'collection_method' => [CollectionMethod::FieldResearch->value],
            'access_rights' => [AccessRights::OpenAccess->value],
            'license' => [License::CcBy->value],
            'production_methods' => [ProductionMethod::Drawing->value],
            'institution.id' => [$institution->id],
            'project.id' => [$project->id],
            'author.person_id' => [$author->id],
            'researcher.person_id' => [$researcher->id],
            'keyword.id' => [$keyword->id],
            'research_collection.id' => [$researchCollection->id],
            'originator.person_id' => [$originatorPerson->id],
            'country.id' => [$country->id],
            'region.id' => [$region->id],
            'district.id' => [$district->id],
            'municipality.id' => [$municipality->id],
            'municipality_part.id' => [$municipalityPart->id],
            'location.id' => [$location->id],
            'time_period_from' => 1940,
            'time_period_to' => 1960,
        ],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->identifier);
});
