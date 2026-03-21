<?php

use Metafori\Core\Models\Location;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

beforeEach(function () {
    $this->repository = app(ItemRepository::class);
    $this->repository->invalidateMapPointsCache();
});

it('includes newly created item with locality in map points', function () {
    $this->repository->mapPoints(); // populate cache

    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    expect($this->repository->mapPoints()->pluck('id'))->toContain($item->id);
});

it('does not include item without locality in map points', function () {
    $this->repository->mapPoints(); // populate cache

    $item = Item::factory()->withoutLocality()->create();

    expect($this->repository->mapPoints()->pluck('id'))->not->toContain($item->id);
});

it('updates map points when item locality is updated', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    $this->repository->mapPoints(); // populate cache

    $newLocalityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item->locality()->associate($newLocalityWithCoordinates)->save();

    expect($this->repository->mapPoints()->firstWhere('id', $item->id)->locality_id)->toBe($newLocalityWithCoordinates->id);
});

it('removes item from map points when item with locality is deleted', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    $this->repository->mapPoints(); // populate cache

    $item->delete();

    expect($this->repository->mapPoints()->pluck('id'))->not->toContain($item->id);
});

it('updates map points when locality coordinates are updated', function () {
    $locality = Location::factory()->create([
        'latitude' => fake()->unique(reset: true)->latitude(),
        'longitude' => fake()->unique()->longitude(),
    ]);
    $item = Item::factory()->for($locality, 'locality')->create();

    $this->repository->mapPoints(); // populate cache

    $newLat = fake()->unique()->latitude();
    $newLng = fake()->unique()->longitude();

    $locality->update([
        'latitude' => $newLat,
        'longitude' => $newLng,
    ]);

    $mapPointLocality = $this->repository->mapPoints()->firstWhere('id', $item->id)->locality;

    expect($mapPointLocality->latitude)->toEqual($newLat)
        ->and($mapPointLocality->longitude)->toEqual($newLng);
});

it('removes item from map points when locality is deleted', function () {
    $locality = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($locality, 'locality')->create();

    $this->repository->mapPoints(); // populate cache

    $locality->delete();

    expect($this->repository->mapPoints()->pluck('id'))->not->toContain($item->id);
});

it('includes item in map points when item with locality is restored', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();
    $item->delete();

    $this->repository->mapPoints(); // populate cache

    $item->restore();

    expect($this->repository->mapPoints()->pluck('id'))->toContain($item->id);
});
