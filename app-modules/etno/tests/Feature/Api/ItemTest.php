<?php

use Metafori\Core\Models\Location;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
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
                    'title',
                    'authors',
                    'researchers',
                    'originators',
                ],
            ],
            'meta',
            'links',
        ])
        ->assertJsonCount(2, 'data.0.authors')
        ->assertJsonCount(2, 'data.0.researchers')
        ->assertJsonCount(2, 'data.0.originators');
});

it('can show a complete item with all relations', function () {
    $document = Document::factory()
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasKeywords(2)
        ->hasResearchCollections(2)
        ->hasOriginators(2);
    $item = Item::factory()
        ->for($document, 'document')
        ->create();

    $response = getJson(route('api.etno.items.show', $item->id));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $item->id,
        ])
        ->assertJsonCount(2, 'data.authors')
        ->assertJsonCount(2, 'data.researchers')
        ->assertJsonCount(2, 'data.keywords')
        ->assertJsonCount(2, 'data.research_collections')
        ->assertJsonCount(2, 'data.originators')
        ->assertJsonPath('data.institution.id', $item->document->institution_id)
        ->assertJsonPath('data.project.id', $item->document->project_id);
});

it('returns 404 for non-existent item', function () {
    $response = getJson(route('api.etno.items.show', 'invalid-id'));

    $response->assertStatus(404);
});

it('can fetch map points', function () {
    $localityWithCoords = Location::factory()->withCoordinates()->create();
    $localityWithoutCoords = Location::factory()->withoutCoordinates()->create();

    $documentWithCoords = Document::factory()->for($localityWithCoords, 'locality');
    $documentWithoutCoords = Document::factory()->for($localityWithoutCoords, 'locality');
    $documentWithoutLocality = Document::factory()->withoutLocality();

    $itemWithCoords = Item::factory()->for($documentWithCoords, 'document')->create();
    $itemWithoutCoords = Item::factory()->for($documentWithoutCoords, 'document')->create();
    $itemWithoutLocality = Item::factory()->for($documentWithoutLocality, 'document')->create();

    $response = getJson(route('api.etno.items.map-points'));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $itemWithCoords->id)
        ->assertJsonPath('data.0.latitude', $localityWithCoords->latitude)
        ->assertJsonPath('data.0.longitude', $localityWithCoords->longitude);
});

it('can filter items by simple property', function () {
    Item::factory()->count(2)->create([
        'type' => ItemType::NewspaperArticle,
        'document_overrides' => ['type'],
    ]);

    $matching = Item::factory()->create([
        'type' => ItemType::Photograph,
        'document_overrides' => ['type'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ['type' => [ItemType::Photograph->value]],
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);
});

it('can filter items by simple property inherited', function () {
    Item::factory()
        ->count(2)
        ->for(Document::factory()->create(['type' => ItemType::NewspaperArticle]), 'document')
        ->create();
    $matching = Item::factory()
        ->for(Document::factory()->create(['type' => ItemType::Translation]), 'document')
        ->create();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.index', [
        'filter' => ['type' => [ItemType::Translation->value]],
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

    // Sort descending by type.keyword (-type.keyword)
    $response = getJson(route('api.etno.items.index', ['sort' => '-type.keyword']));

    $response->assertStatus(200);
    $data = collect($response->json('data'));

    // By extracting the created ids specifically (ensuring that type order works alphabetically: M, D, A -> Map, Drawing, Audio Recording)
    expect($data->pluck('id')->toArray())->toBe([$itemC->id, $itemB->id, $itemA->id]);
});
