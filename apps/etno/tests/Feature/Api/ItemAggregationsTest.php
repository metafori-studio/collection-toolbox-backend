<?php

use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\DocumentOriginator;
use Metafori\Etno\Models\Project;
use Metafori\Etno\Models\ResearchCollection;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;

uses(RefreshIndices::class);

it('can get aggregations for items', function () {
    Document::factory()
        ->hasItems(1)
        ->published()
        ->create(['type' => ItemType::AudioRecording]);

    Document::factory()
        ->hasItems(2)
        ->published()
        ->create(['type' => ItemType::Map]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'type' => [
                    '*' => ['value', 'label', 'count'],
                ],
            ],
        ]);

    $data = $response->json('data.type');

    $mapAgg = collect($data)->firstWhere('value', ItemType::Map->value);
    $audioAgg = collect($data)->firstWhere('value', ItemType::AudioRecording->value);

    expect($mapAgg['count'])->toBe(2)
        ->and($audioAgg['count'])->toBe(1);
});

it('can filter aggregations by another property', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create([
            'type' => ItemType::AudioRecording,
            'institution_id' => $org1->id,
        ]);

    Document::factory()
        ->hasItems(2)
        ->published()
        ->create([
            'type' => ItemType::Map,
            'institution_id' => $org2->id,
        ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
        'filter' => ['institution.id' => [$org1->id]],
    ]));

    $response->assertStatus(200);

    $typeData = $response->json('data.type');

    expect(collect($typeData)->firstWhere('value', ItemType::AudioRecording->value)['count'])->toBe(1)
        ->and(collect($typeData)->firstWhere('value', ItemType::Map->value))->toBeNull();
});

it('ignores filters for the same field when aggregating', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create([
            'type' => ItemType::AudioRecording,
            'institution_id' => $org1->id,
        ]);

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create([
            'type' => ItemType::Map,
            'institution_id' => $org2->id,
        ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
        'filter' => ['type' => [ItemType::AudioRecording->value]],
    ]));

    $response->assertStatus(200);

    $typeData = $response->json('data.type');

    expect(collect($typeData)->firstWhere('value', ItemType::AudioRecording->value)['count'])->toBe(1)
        ->and(collect($typeData)->firstWhere('value', ItemType::Map->value)['count'])->toBe(1);

    $data = $response->json('data');
    $instData = $data['institution.id'] ?? [];

    expect(collect($instData)->firstWhere('value', $org1->id)['count'])->toBe(1)
        ->and(collect($instData)->firstWhere('value', $org2->id))->toBeNull();
});

it('resolves model labels for aggregations', function () {
    $org = Organization::factory()->create(['name' => 'Test Organization']);

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create(['institution_id' => $org->id]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations'));

    $response->assertStatus(200);

    $data = $response->json('data');
    $instData = $data['institution.id'] ?? [];

    $orgAgg = collect($instData)->firstWhere('value', $org->id);

    expect($orgAgg['label'])->toBe('Test Organization')
        ->and($orgAgg['count'])->toBe(1);
});

it('can filter aggregations by time period', function () {
    $matchingOrg = Organization::factory()->create();
    $otherOrg = Organization::factory()->create();

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create([
            'institution_id' => $matchingOrg->id,
            'time_period_start' => '1950-12-31',
            'time_period_end' => '1950-12-31',
        ]);

    Document::factory()
        ->hasItems(1)
        ->published()
        ->create([
            'institution_id' => $otherOrg->id,
            'time_period_start' => '1800-01-01',
            'time_period_end' => '1840-01-01',
        ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
        'filter' => ['time_period_from' => 1950],
    ]));

    $response->assertStatus(200);

    $data = $response->json('data');
    $instData = $data['institution.id'] ?? [];

    expect(collect($instData)->firstWhere('value', $matchingOrg->id)['count'])->toBe(1)
        ->and(collect($instData)->firstWhere('value', $otherOrg->id))->toBeNull();
});

it('can filter aggregations by all filterables at once', function () {
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

    Document::factory()
        ->published()
        ->for($location, 'locality')
        ->hasAttached($author, [], 'authors')
        ->hasAttached($researcher, [], 'researchers')
        ->hasAttached($keyword, [], 'keywords')
        ->hasAttached($researchCollection, [], 'researchCollections')
        ->has(DocumentOriginator::factory()->for($originatorPerson), 'originators')
        ->hasItems(1)
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
        ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
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

    $data = $response->json('data');

    expect(collect($data['type'])->firstWhere('value', ItemType::AudioRecording->value)['count'])->toBe(1)
        ->and(collect($data['type'])->firstWhere('value', ItemType::Map->value))->toBeNull();
});

it('does not include unpublished items in aggregations', function () {
    Document::factory()
        ->hasItems(1)
        ->published(false)
        ->create(['type' => ItemType::Map]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations'));
    $response->assertStatus(200);

    $typeData = $response->json('data.type');

    expect(collect($typeData)->firstWhere('value', ItemType::Map->value))->toBeNull();
});
