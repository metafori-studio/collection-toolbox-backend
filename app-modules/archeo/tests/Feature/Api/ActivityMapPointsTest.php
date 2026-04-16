<?php

use Illuminate\Support\Facades\Cache;
use Metafori\Archeo\Models\Activity;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Cache::forget(Activity::MAP_POINTS_CACHE_KEY);
});

it('includes newly created activity with coordinates in map points', function () {
    $activity = Activity::factory()->create([
        'latitude' => 48.123456,
        'longitude' => 17.123456,
        'localization_degree' => 1,
    ]);

    getJson(url('api/archeo/activities/map-points'))
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => $activity->activity_number,
            'latitude' => 48.123456,
            'longitude' => 17.123456,
            'localization_degree' => 1,
        ]);
});

it('does not include activity without coordinates in map points', function () {
    $activity = Activity::factory()->create([
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = getJson(url('api/archeo/activities/map-points'));
    $response->assertStatus(200);

    expect(collect($response->json('data'))->pluck('id'))->not->toContain($activity->activity_number);
});

it('removes activity from map points when deleted', function () {
    $activity = Activity::factory()->create([
        'latitude' => 48.123456,
        'longitude' => 17.123456,
    ]);

    getJson(url('api/archeo/activities/map-points'));

    $activity->delete();

    $response = getJson(url('api/archeo/activities/map-points'));
    expect(collect($response->json('data'))->pluck('id'))->not->toContain($activity->activity_number);
});
