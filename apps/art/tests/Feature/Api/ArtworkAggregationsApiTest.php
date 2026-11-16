<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Art\Enums\WorkType;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\ArtworkAuthor;
use Metafori\Art\Models\Collection;
use Metafori\Art\Repositories\ArtworkRepository;
use Metafori\Core\Models\Person;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class, RefreshIndices::class);

it('returns the localized collection title in aggregations', function () {
    $collection = Collection::factory()->create(['title' => ['en' => 'Modern art', 'cs' => 'Moderní umění']]);
    $artwork = Artwork::factory()->create();
    $artwork->collections()->attach($collection);
    $artwork->searchable();
    app(ArtworkRepository::class)->refreshIndex();

    $response = withHeader('Accept-Language', 'cs')->getJson(route('artworks.aggregations'));

    $response->assertOk();
    expect(collect($response->json('data')['collection.id'])->firstWhere('value', (string) $collection->id))
        ->toMatchArray(['label' => 'Moderní umění', 'count' => 1]);
});

it('can retrieve filter aggregations with counts and labels', function () {
    $artwork1 = Artwork::factory()->create([
        'work_types' => [WorkType::Painting],
    ]);
    $artwork2 = Artwork::factory()->create([
        'work_types' => [WorkType::Painting],
    ]);
    $artwork3 = Artwork::factory()->create([
        'work_types' => [WorkType::Sculpture],
    ]);

    app(ArtworkRepository::class)->refreshIndex();

    $response = getJson(route('artworks.aggregations'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'work_types',
                'acquisition_method',
            ],
        ]);

    $workTypes = collect($response->json('data.work_types'));
    expect($workTypes->firstWhere('value', WorkType::Painting->value)['count'])->toBe(2)
        ->and($workTypes->firstWhere('value', WorkType::Sculpture->value)['count'])->toBe(1);
});

it('returns author labels and applies combined author filters to other facets', function () {
    $person = Person::factory()->create();
    $personArtwork = Artwork::factory()->create(['work_types' => [WorkType::Painting]]);
    ArtworkAuthor::factory()->create(['artwork_id' => $personArtwork->id, 'person_id' => $person->id, 'label' => null]);
    $labelArtwork = Artwork::factory()->create(['work_types' => [WorkType::Sculpture]]);
    ArtworkAuthor::factory()->create(['artwork_id' => $labelArtwork->id, 'person_id' => null, 'label' => 'Unknown artist']);
    $otherArtwork = Artwork::factory()->create(['work_types' => [WorkType::Painting]]);
    ArtworkAuthor::factory()->create(['artwork_id' => $otherArtwork->id, 'person_id' => null, 'label' => 'Other artist']);

    app(ArtworkRepository::class)->refreshIndex();

    $response = getJson(route('artworks.aggregations', ['filter' => [
        'author.person_id' => [$person->id],
        'author.label' => ['Unknown artist'],
    ]]));

    $response->assertOk();
    $labels = collect($response->json('data')['author.label']);
    expect($labels->firstWhere('value', 'Unknown artist'))->toMatchArray(['label' => 'Unknown artist', 'count' => 1])
        ->and($labels->firstWhere('value', 'Other artist'))->not->toBeNull();
    $people = collect($response->json('data')['author.person_id']);
    expect($people->firstWhere('value', (string) $person->id)['label'])->toBe((string) $person);
    $workTypes = collect($response->json('data.work_types'));
    expect($workTypes->firstWhere('value', WorkType::Painting->value)['count'])->toBe(1)
        ->and($workTypes->firstWhere('value', WorkType::Sculpture->value)['count'])->toBe(1);
});
