<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Metafori\Art\Enums\Medium;
use Metafori\Art\Enums\ObjectType;
use Metafori\Art\Enums\Technique;
use Metafori\Art\Enums\WorkType;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\ArtworkAuthor;
use Metafori\Art\Models\Collection;
use Metafori\Art\Repositories\ArtworkRepository;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Models\Person;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class, RefreshIndices::class);

it('paginates artworks via opensearch index and returns JSON:API structure', function () {
    Artwork::factory()->count(15)->create();

    app(ArtworkRepository::class)->refreshIndex();

    $response = getJson(route('artworks.index', ['per_page' => 5, 'page' => 2]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 15)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'attributes' => [
                        'identifier',
                        'slug',
                        'title',
                        'description',
                        'dating',
                        'creation_date',
                        'work_types',
                        'object_types',
                        'mediums',
                        'techniques',
                        'acquisition_method',
                        'acquisition_date',
                        'measurements',
                    ],
                ],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
});

it('supports including authors, collections, and media in listing', function () {
    Storage::fake('local');

    $artwork = Artwork::factory()->create([
        'slug' => 'starry-night',
        'title' => ['en' => 'Starry Night'],
    ]);

    $authorPerson = Person::factory()->create([
        'given_name' => 'Vincent',
        'family_name' => 'van Gogh',
    ]);

    ArtworkAuthor::factory()->create([
        'artwork_id' => $artwork->id,
        'person_id' => $authorPerson->id,
        'label' => 'Vincent van Gogh',
    ]);

    $collection = Collection::factory()->create([
        'title' => ['en' => 'Masterpieces'],
    ]);
    $artwork->collections()->attach($collection);

    $file = UploadedFile::fake()->image('painting.jpg', 600, 400);
    $artwork->addMedia($file)->toMediaCollection('images', 'local');

    app(ArtworkRepository::class)->refreshIndex();

    $response = withHeader('Accept-Language', 'en')
        ->getJson(route('artworks.index', [
            'include' => 'authors.person,collections,media',
        ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $artwork->id)
        ->assertJsonCount(1, 'data.0.relationships.authors.data')
        ->assertJsonCount(1, 'data.0.relationships.collections.data')
        ->assertJsonCount(1, 'data.0.relationships.media.data')
        ->assertJsonPath('included.0.type', 'artwork_authors')
        ->assertJsonPath('included.0.attributes.label', 'Vincent van Gogh')
        ->assertJsonPath('included.1.type', 'collections')
        ->assertJsonPath('included.1.attributes.title', 'Masterpieces')
        ->assertJsonPath('included.2.type', 'media')
        ->assertJsonPath('included.2.attributes.file_name', 'painting.jpg')
        ->assertJsonPath('included.3.type', 'people')
        ->assertJsonPath('included.3.attributes.display_name', 'van Gogh, Vincent');
});

it('can filter artworks by enum properties', function (string $property, string $enumClass) {
    $cases = collect($enumClass::cases());
    $matchingValue = $cases->first();
    $otherValue = $cases->last();

    $matchingArtwork = Artwork::factory()->create([
        $property => [$matchingValue],
    ]);

    Artwork::factory()->count(2)->create([
        $property => [$otherValue],
    ]);

    app(ArtworkRepository::class)->refreshIndex();

    $response = getJson(route('artworks.index', [
        'filter' => [$property => [$matchingValue->value]],
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $matchingArtwork->id);
})->with([
    'work_types' => ['work_types', WorkType::class],
    'object_types' => ['object_types', ObjectType::class],
    'mediums' => ['mediums', Medium::class],
    'techniques' => ['techniques', Technique::class],
]);

it('can filter artworks by author and collection relations', function () {
    $person1 = Person::factory()->create();
    $person2 = Person::factory()->create();

    $artwork1 = Artwork::factory()->create();
    ArtworkAuthor::factory()->create(['artwork_id' => $artwork1->id, 'person_id' => $person1->id]);

    $artwork2 = Artwork::factory()->create();
    ArtworkAuthor::factory()->create(['artwork_id' => $artwork2->id, 'person_id' => $person2->id]);

    $collection = Collection::factory()->create();
    $artwork1->collections()->attach($collection);
    $artwork1->searchable();

    app(ArtworkRepository::class)->refreshIndex();

    // Filter by author person_id
    $responseAuthor = getJson(route('artworks.index', [
        'filter' => ['author.person_id' => [$person1->id]],
    ]));
    $responseAuthor->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $artwork1->id);

    // Filter by collection id
    $responseCollection = getJson(route('artworks.index', [
        'filter' => ['collection.id' => [$collection->id]],
    ]));
    $responseCollection->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $artwork1->id);
});

it('can filter artworks by an author person or standalone label', function () {
    $person = Person::factory()->create();
    $personArtwork = Artwork::factory()->create();
    ArtworkAuthor::factory()->create(['artwork_id' => $personArtwork->id, 'person_id' => $person->id, 'label' => 'Old label']);
    $labelArtwork = Artwork::factory()->create();
    ArtworkAuthor::factory()->create(['artwork_id' => $labelArtwork->id, 'person_id' => null, 'label' => 'Unknown artist']);
    Artwork::factory()->create();

    app(ArtworkRepository::class)->refreshIndex();

    getJson(route('artworks.index', ['filter' => ['author.label' => ['Unknown artist']]]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', (string) $labelArtwork->id);

    getJson(route('artworks.index', ['filter' => ['author.label' => ['Old label']]]))
        ->assertOk()->assertJsonCount(0, 'data');

    getJson(route('artworks.index', ['filter' => ['author.person_id' => [$person->id], 'author.label' => ['Unknown artist']]]))
        ->assertOk()->assertJsonCount(2, 'data');
});

it('can filter artworks by creation date range', function () {
    $earlyArtwork = Artwork::factory()->create([
        'creation_date' => FuzzyDate::single('1920-05-01', DatePrecision::Year),
    ]);
    $midArtwork = Artwork::factory()->create([
        'creation_date' => FuzzyDate::single('1950-01-01', DatePrecision::Year),
    ]);
    $lateArtwork = Artwork::factory()->create([
        'creation_date' => FuzzyDate::single('1980-01-01', DatePrecision::Year),
    ]);

    app(ArtworkRepository::class)->refreshIndex();

    $response = getJson(route('artworks.index', [
        'filter' => [
            'creation_date_from' => '1940',
            'creation_date_to' => '1960',
        ],
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $midArtwork->id);
});
