<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\ArtworkAuthor;
use Metafori\Art\Models\Collection;
use Metafori\Art\Models\CollectionCategory;
use Metafori\Art\Repositories\ArtworkRepository;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('paginates and sorts collections with translated details and artwork counts', function () {
    $first = Collection::factory()->create(['title' => ['en' => 'Alpha'], 'description' => ['en' => 'Description'], 'is_featured' => false, 'published_at' => '2026-11-17 12:30:00']);
    Collection::factory()->create(['title' => ['en' => 'Beta']]);
    $first->artworks()->attach(Artwork::factory()->create());

    getJson(route('collections.index', ['sort' => 'title', 'per_page' => 1]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.id', (string) $first->id)
        ->assertJsonPath('data.0.attributes.title', 'Alpha')
        ->assertJsonPath('data.0.attributes.description', 'Description')
        ->assertJsonPath('data.0.attributes.published_at', '2026-11-17T12:30:00.000000Z')
        ->assertJsonPath('data.0.attributes.is_featured', false)
        ->assertJsonPath('data.0.attributes.artworks_count', 1);

    getJson(route('collections.index', ['sort' => 'invalid']))->assertUnprocessable();
});

it('filters and paginates featured and other collections', function () {
    $featured = Collection::factory()->count(2)->create(['is_featured' => true]);
    $other = Collection::factory()->count(2)->create(['is_featured' => false]);

    getJson(route('collections.index', ['per_page' => 1, 'page' => 2, 'filter' => ['is_featured' => 0]]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $other[1]->id)
        ->assertJsonPath('meta.total', 2);

    getJson(route('collections.index', ['per_page' => 1, 'page' => 2, 'filter' => ['is_featured' => 1]]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $featured[1]->id)
        ->assertJsonPath('meta.total', 2);

    getJson(route('collections.index'))->assertSuccessful()->assertJsonPath('meta.total', 4);
    getJson(route('collections.index', ['filter' => ['is_featured' => 'invalid']]))->assertUnprocessable();
});

it('shows a collection and paginates its indexed artworks', function () {
    $collection = Collection::factory()->create(['title' => ['en' => 'Collection'], 'published_at' => '2026-11-17 12:30:00']);
    $first = Artwork::factory()->create(['title' => ['en' => 'Alpha']]);
    $second = Artwork::factory()->create(['title' => ['en' => 'Beta']]);
    $collection->artworks()->attach($first, ['sort_order' => 2]);
    $collection->artworks()->attach($second, ['sort_order' => 1]);
    $calls = 0;
    $repository = Mockery::mock(ArtworkRepository::class);
    $repository->shouldReceive('paginate')
        ->withArgs(function ($filters, $sorts, $locale, $perPage) use ($collection) {
            return $filters === ['collection.id' => [$collection->id]]
                && \in_array($sorts, [[], ['title' => 'asc'], ['title' => 'desc']], true)
                && $locale === 'en' && $perPage === 1;
        })
        ->andReturnUsing(function ($filters, $sorts) use (&$calls, $first, $second) {
            $calls++;
            $artwork = $sorts === ['title' => 'asc'] ? $first : $second;

            return new LengthAwarePaginator(collect([$artwork]), 2, 1);
        });
    app()->instance(ArtworkRepository::class, $repository);

    getJson(route('collections.show', $collection))->assertSuccessful()
        ->assertJsonPath('data.attributes.published_at', '2026-11-17T12:30:00.000000Z')
        ->assertJsonPath('data.attributes.artworks_count', 2);
    getJson(route('collections.artworks', ['collection' => $collection, 'per_page' => 1]))
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.id', (string) $second->id);
    getJson(route('collections.artworks', ['collection' => $collection, 'page' => 0]))
        ->assertUnprocessable()->assertJsonValidationErrors('page');
    getJson(route('collections.artworks', ['collection' => $collection, 'per_page' => 101]))
        ->assertUnprocessable()->assertJsonValidationErrors('per_page');
    getJson(route('collections.artworks', ['collection' => $collection, 'sort' => 'title', 'per_page' => 1]))
        ->assertSuccessful()->assertJsonPath('data.0.id', (string) $first->id);
    getJson(route('collections.artworks', ['collection' => $collection, 'sort' => '-title', 'per_page' => 1]))
        ->assertSuccessful()->assertJsonPath('data.0.id', (string) $second->id);
    getJson(route('collections.artworks', ['collection' => $collection, 'sort' => 'invalid']))
        ->assertUnprocessable()->assertJsonValidationErrors('sort');
    getJson(route('collections.show', 99999))->assertNotFound();
    getJson(route('collections.artworks', 99999))->assertNotFound();
    expect($calls)->toBe(3);
});

it('includes artwork images in collections and artwork authors and media', function () {
    Storage::fake('local');
    $collection = Collection::factory()->create();
    $artwork = Artwork::factory()->create();
    $collection->artworks()->attach($artwork);
    ArtworkAuthor::factory()->create(['artwork_id' => $artwork->id, 'label' => 'Artist']);
    $artwork->addMedia(UploadedFile::fake()->image('artwork.jpg'))->toMediaCollection('images', 'local');
    $repository = Mockery::mock(ArtworkRepository::class);
    $repository->shouldReceive('paginate')
        ->withArgs(fn ($filters, $sorts, $locale, $perPage) => $filters === ['collection.id' => [$collection->id]]
            && $sorts === []
            && $locale === 'en'
            && $perPage === 15)
        ->andReturn(new LengthAwarePaginator(collect([$artwork->load('authors.person', 'media')]), 1, 15));
    app()->instance(ArtworkRepository::class, $repository);

    getJson(route('collections.show', $collection))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.attributes.images');
    getJson(route('collections.artworks', ['collection' => $collection, 'include' => 'authors.person,media']))
        ->assertSuccessful()
        ->assertJsonPath('included.0.attributes.label', 'Artist')
        ->assertJsonPath('included.1.attributes.file_name', 'artwork.jpg');
});

it('returns previews in collection order independently for each collection', function () {
    Storage::fake('local');
    $firstCollection = Collection::factory()->create();
    $secondCollection = Collection::factory()->create();
    $first = Artwork::factory()->create();
    $second = Artwork::factory()->create();
    $withoutImage = Artwork::factory()->create();
    $first->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('images', 'local');
    $second->addMedia(UploadedFile::fake()->image('second.jpg'))->toMediaCollection('images', 'local');
    $firstCollection->artworks()->attach($first, ['sort_order' => 2]);
    $firstCollection->artworks()->attach($second, ['sort_order' => 1]);
    $firstCollection->artworks()->attach($withoutImage, ['sort_order' => 0]);
    $secondCollection->artworks()->attach($first, ['sort_order' => 1]);
    $secondCollection->artworks()->attach($second, ['sort_order' => 2]);

    $response = getJson(route('collections.index'))->assertSuccessful();

    expect($response->json('data.0.attributes.images'))->toHaveCount(2)
        ->and($response->json('data.0.attributes.images.0'))->toContain('second.jpg')
        ->and($response->json('data.0.attributes.images.1'))->toContain('first.jpg')
        ->and($response->json('data.1.attributes.images'))->toHaveCount(2)
        ->and($response->json('data.1.attributes.images.0'))->toContain('first.jpg')
        ->and($response->json('data.1.attributes.images.1'))->toContain('second.jpg');

    $detail = getJson(route('collections.show', $firstCollection))->assertSuccessful();

    expect($detail->json('data.attributes.images.0'))->toContain('second.jpg')
        ->and($detail->json('data.attributes.images.1'))->toContain('first.jpg');
});

it('computes collection datespan from min and max artwork creation years', function () {
    $spanning = Collection::factory()->create(['title' => ['en' => 'Spanning']]);
    $spanning->artworks()->attach([
        Artwork::factory()->create(['creation_date' => FuzzyDate::range('1918-01-01', DatePrecision::Year, '1925-12-31', DatePrecision::Year)])->id,
        Artwork::factory()->create(['creation_date' => FuzzyDate::single('1980-06-01', DatePrecision::Year)])->id,
        Artwork::factory()->create(['creation_date' => null])->id,
    ]);

    $singleYear = Collection::factory()->create(['title' => ['en' => 'Single']]);
    $singleYear->artworks()->attach(
        Artwork::factory()->create(['creation_date' => FuzzyDate::single('1920-05-01', DatePrecision::Year)])->id
    );

    $empty = Collection::factory()->create(['title' => ['en' => 'Empty']]);
    $empty->artworks()->attach(
        Artwork::factory()->create(['creation_date' => null])->id
    );

    $openEnded = Collection::factory()->create(['title' => ['en' => 'Open']]);
    $openEnded->artworks()->attach(
        Artwork::factory()->create(['creation_date' => FuzzyDate::range('1950-01-01', DatePrecision::Year, null, DatePrecision::Year)])->id
    );

    getJson(route('collections.show', $spanning))
        ->assertSuccessful()
        ->assertJsonPath('data.attributes.datespan', ['is_range' => true, 'start' => ['date' => '1918-01-01', 'precision' => 'year'], 'end' => ['date' => '1980-01-01', 'precision' => 'year']]);

    getJson(route('collections.show', $singleYear))
        ->assertSuccessful()
        ->assertJsonPath('data.attributes.datespan', ['is_range' => false, 'date' => '1920-01-01', 'precision' => 'year']);

    getJson(route('collections.show', $empty))
        ->assertSuccessful()
        ->assertJsonPath('data.attributes.datespan', null);

    getJson(route('collections.show', $openEnded))
        ->assertSuccessful()
        ->assertJsonPath('data.attributes.datespan', ['is_range' => false, 'date' => '1950-01-01', 'precision' => 'year']);

    expect(Collection::query()->withDatespan()->findOrFail($spanning->id)->datespan->toArray())
        ->toBe(['is_range' => true, 'start' => ['date' => '1918-01-01', 'precision' => 'year'], 'end' => ['date' => '1980-01-01', 'precision' => 'year']])
        ->and(Collection::query()->withDatespan()->findOrFail($empty->id)->datespan)->toBeNull();
});

it('includes translated collection category relationship', function () {
    $category = CollectionCategory::factory()->create([
        'title' => ['en' => 'Exhibitions', 'cs' => 'Výstavy'],
    ]);
    $collection = Collection::factory()->create([
        'collection_category_id' => $category->id,
    ]);

    getJson(route('collections.index', ['include' => 'category']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.relationships.category.data.id', (string) $category->id)
        ->assertJsonPath('included.0.type', 'collection_categories')
        ->assertJsonPath('included.0.attributes.title', 'Exhibitions');

    getJson(route('collections.show', ['collection' => $collection, 'include' => 'category']))
        ->assertSuccessful()
        ->assertJsonPath('data.relationships.category.data.id', (string) $category->id)
        ->assertJsonPath('included.0.type', 'collection_categories')
        ->assertJsonPath('included.0.attributes.title', 'Exhibitions');
});

it('excludes unpublished collections from index, show, artworks, and artwork search indexing', function () {
    $published = Collection::factory()->create(['published_at' => now()->subDay()]);
    $draft = Collection::factory()->unpublished()->create();
    $scheduled = Collection::factory()->create(['published_at' => now()->addDay()]);

    $artwork = Artwork::factory()->create();
    $artwork->collections()->attach([$published->id, $draft->id, $scheduled->id]);

    getJson(route('collections.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $published->id);

    getJson(route('collections.show', $draft))->assertNotFound();
    getJson(route('collections.show', $scheduled))->assertNotFound();
    getJson(route('collections.artworks', $draft))->assertNotFound();
    getJson(route('collections.artworks', $scheduled))->assertNotFound();

    expect($artwork->fresh()->toSearchableArray()['collection']['id'])->toBe([$published->id]);

    $published->update(['published_at' => null]);

    expect($artwork->fresh()->toSearchableArray()['collection']['id'])->toBe([]);
});
