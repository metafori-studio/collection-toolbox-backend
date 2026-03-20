<?php

use Metafori\Core\Models\Location;
use Metafori\Etno\Models\Item;

use function Pest\Laravel\getJson;

it('can list items', function () {
    Item::factory()
        ->count(2)
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasOriginators(2)
        ->create();

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
    $item = Item::factory()
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasKeywords(2)
        ->hasResearchCollections(2)
        ->hasOriginators(2)
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
        ->assertJsonPath('data.institution.id', $item->institution_id)
        ->assertJsonPath('data.project.id', $item->project_id);
});

it('returns 404 for non-existent item', function () {
    $response = getJson(route('api.etno.items.show', 'invalid-id'));

    $response->assertStatus(404);
});

it('can fetch map points', function () {
    $localityWithCoords = Location::factory()->withCoordinates()->create();
    $localityWithoutCoords = Location::factory()->withoutCoordinates()->create();

    $item = Item::factory()->for($localityWithCoords, 'locality')->create();
    Item::factory()->for($localityWithoutCoords, 'locality')->create();
    Item::factory()->withoutLocality()->create();

    $response = getJson(route('api.etno.items.map-points'));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item->id)
        ->assertJsonPath('data.0.latitude', (float) $localityWithCoords->latitude)
        ->assertJsonPath('data.0.longitude', (float) $localityWithCoords->longitude);
});
