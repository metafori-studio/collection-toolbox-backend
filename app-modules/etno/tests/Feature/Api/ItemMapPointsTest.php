<?php

use Metafori\Core\Models\Location;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

use function Pest\Laravel\getJson;

beforeEach(function () {
    app(ItemRepository::class)->invalidateMapPointsCache();
});

it('includes newly created item with locality in map points', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    getJson(route('api.etno.items.map-points'))
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $item->identifier]);
});

it('does not include item without locality in map points', function () {
    $document = Document::factory()->withoutLocality();
    $item = Item::factory()->for($document, 'document')->create();

    $response = getJson(route('api.etno.items.map-points'));

    expect(collect($response->json('data'))->pluck('id'))->not->toContain($item->identifier);
});

it('updates map points when item locality is updated', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    // fetch to populate cache
    getJson(route('api.etno.items.map-points'));

    $newLocalityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item->locality()->associate($newLocalityWithCoordinates);

    $overrides = $item->document_overrides ?? [];
    if (! in_array('locality', $overrides)) {
        $overrides[] = 'locality';
        $item->document_overrides = $overrides;
    }

    $item->save();

    $response = getJson(route('api.etno.items.map-points'));

    $point = collect($response->json('data'))->firstWhere('id', $item->identifier);
    expect($point['latitude'] ?? null)->toEqual($newLocalityWithCoordinates->latitude)
        ->and($point['longitude'] ?? null)->toEqual($newLocalityWithCoordinates->longitude);
});

it('removes item from map points when item with locality is deleted', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    getJson(route('api.etno.items.map-points'));

    $item->delete();

    $response = getJson(route('api.etno.items.map-points'));
    expect(collect($response->json('data'))->pluck('id'))->not->toContain($item->identifier);
});

it('updates map points when locality coordinates are updated', function () {
    $locality = Location::factory()->create([
        'latitude' => fake()->unique(reset: true)->latitude(),
        'longitude' => fake()->unique()->longitude(),
    ]);
    $document = Document::factory()->for($locality, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    getJson(route('api.etno.items.map-points'));

    $newLat = fake()->unique()->latitude();
    $newLng = fake()->unique()->longitude();

    $locality->update([
        'latitude' => $newLat,
        'longitude' => $newLng,
    ]);

    $response = getJson(route('api.etno.items.map-points'));
    $point = collect($response->json('data'))->firstWhere('id', $item->identifier);

    expect($point['latitude'] ?? null)->toEqual($newLat)
        ->and($point['longitude'] ?? null)->toEqual($newLng);
});

it('removes item from map points when locality is deleted', function () {
    $locality = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($locality, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    getJson(route('api.etno.items.map-points'));

    $locality->delete();

    $response = getJson(route('api.etno.items.map-points'));
    expect(collect($response->json('data'))->pluck('id'))->not->toContain($item->identifier);
});

it('includes item in map points when item with locality is restored', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();
    $item->delete();

    getJson(route('api.etno.items.map-points'));

    $item->restore();

    $response = getJson(route('api.etno.items.map-points'));
    expect(collect($response->json('data'))->pluck('id'))->toContain($item->identifier);
});

it('does not include item in map points when document is deleted', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();

    getJson(route('api.etno.items.map-points'));

    $item->document->delete();

    $response = getJson(route('api.etno.items.map-points'));
    expect(collect($response->json('data'))->pluck('id'))->not->toContain($item->identifier);
});

it('returns map points as a sequential array when items are filtered out', function () {
    $locality1 = Location::factory()->withCoordinates()->create();
    $document1 = Document::factory()->for($locality1, 'locality');
    $item1 = Item::factory()->for($document1, 'document')->create();

    $document2 = Document::factory()->withoutLocality();
    $item2 = Item::factory()->for($document2, 'document')->create();

    $locality3 = Location::factory()->withCoordinates()->create();
    $document3 = Document::factory()->for($locality3, 'locality');
    $item3 = Item::factory()->for($document3, 'document')->create();

    $response = getJson(route('api.etno.items.map-points'));
    $response->assertStatus(200);

    $data = collect($response->json('data'));

    expect($data)->toHaveCount(2)
        ->and($data->pluck('id'))->toContain($item1->identifier, $item3->identifier)
        ->and($data->pluck('id'))->not->toContain($item2->identifier);

    $response->assertJsonPath('data.0.id', $item1->identifier);
    $response->assertJsonPath('data.1.id', $item3->identifier);
});

it('includes item in map points when document is restored', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $document = Document::factory()->for($localityWithCoordinates, 'locality');
    $item = Item::factory()->for($document, 'document')->create();
    $item->document->delete();

    getJson(route('api.etno.items.map-points'));

    $item->document->restore();

    $response = getJson(route('api.etno.items.map-points'));
    expect(collect($response->json('data'))->pluck('id'))->toContain($item->identifier);
});
