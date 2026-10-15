<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Metafori\Art\Data\Measurement;
use Metafori\Art\Enums\AcquisitionMethod;
use Metafori\Art\Enums\MeasurementType;
use Metafori\Art\Enums\MeasurementUnit;
use Metafori\Art\Enums\Medium;
use Metafori\Art\Enums\ObjectType;
use Metafori\Art\Enums\Technique;
use Metafori\Art\Enums\WorkType;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\ArtworkAuthor;
use Metafori\Art\Models\Collection;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Models\Person;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class);

it('retrieves artwork detail by slug with included relations', function () {
    $artwork = Artwork::factory()->create([
        'slug' => 'starry-night',
        'identifier' => 'ART-001',
        'title' => ['en' => 'Starry Night', 'cs' => 'Hvězdná noc'],
        'dating' => ['en' => 'circa 1889', 'cs' => 'kolem 1889'],
        'description' => ['en' => 'A famous painting', 'cs' => 'Slavný obraz'],
        'work_types' => [WorkType::Painting],
        'object_types' => [ObjectType::Positive],
        'mediums' => [Medium::Canvas],
        'techniques' => [Technique::Oil],
        'acquisition_method' => AcquisitionMethod::Donation,
        'acquisition_date' => FuzzyDate::single('1941-01-01', DatePrecision::Year),
        'creation_date' => FuzzyDate::single('1889-06-01', DatePrecision::Year),
        'measurements' => [
            new Measurement(['type' => MeasurementType::Height, 'value' => 73.7, 'unit' => MeasurementUnit::Cm]),
            new Measurement(['type' => MeasurementType::Width, 'value' => 92.1, 'unit' => MeasurementUnit::Cm]),
        ],
    ]);

    $authorPerson = Person::factory()->create([
        'given_name' => 'Vincent',
        'family_name' => 'van Gogh',
    ]);

    ArtworkAuthor::factory()->create([
        'artwork_id' => $artwork->id,
        'person_id' => $authorPerson->id,
        'label' => 'Vincent van Gogh',
        'sort_order' => 1,
    ]);

    $collection = Collection::factory()->create([
        'title' => ['en' => 'Post-Impressionism Masterpieces', 'cs' => 'Postimpresionistická díla'],
    ]);

    $artwork->collections()->attach($collection);

    $response = withHeader('Accept-Language', 'en')
        ->getJson(route('artworks.show', ['artwork' => 'starry-night', 'include' => 'authors.person,collections']));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.id', (string) $artwork->id)
        ->assertJsonPath('data.type', 'artworks')
        ->assertJsonPath('data.attributes.slug', 'starry-night')
        ->assertJsonPath('data.attributes.identifier', 'ART-001')
        ->assertJsonPath('data.attributes.title', 'Starry Night')
        ->assertJsonPath('data.attributes.dating', 'circa 1889')
        ->assertJsonPath('data.attributes.description', 'A famous painting')
        ->assertJsonPath('data.attributes.work_types.0', 'painting')
        ->assertJsonPath('data.attributes.object_types.0', 'positive')
        ->assertJsonPath('data.attributes.mediums.0', 'canvas')
        ->assertJsonPath('data.attributes.techniques.0', 'oil')
        ->assertJsonPath('data.attributes.acquisition_method', 'donation')
        ->assertJsonPath('data.attributes.acquisition_date.date', '1941-01-01')
        ->assertJsonPath('data.attributes.acquisition_date.precision', 'year')
        ->assertJsonPath('data.attributes.creation_date.date', '1889-01-01')
        ->assertJsonPath('data.attributes.creation_date.precision', 'year')
        ->assertJsonPath('data.attributes.measurements.0.type', MeasurementType::Height->value)
        ->assertJsonPath('data.attributes.measurements.0.value', 73.7)
        ->assertJsonPath('data.attributes.measurements.0.unit', MeasurementUnit::Cm->value)
        ->assertJsonPath('data.attributes.measurements.1.type', MeasurementType::Width->value)
        ->assertJsonPath('data.attributes.measurements.1.value', 92.1)
        ->assertJsonPath('data.attributes.measurements.1.unit', MeasurementUnit::Cm->value)
        ->assertJsonCount(1, 'data.relationships.authors.data')
        ->assertJsonCount(1, 'data.relationships.collections.data')
        ->assertJsonPath('included.0.type', 'artwork_authors')
        ->assertJsonPath('included.0.attributes.label', 'Vincent van Gogh')
        ->assertJsonPath('included.1.type', 'collections')
        ->assertJsonPath('included.1.id', (string) $collection->id)
        ->assertJsonPath('included.1.attributes.title', 'Post-Impressionism Masterpieces')
        ->assertJsonPath('included.2.type', 'people')
        ->assertJsonPath('included.2.id', (string) $authorPerson->id)
        ->assertJsonPath('included.2.attributes.display_name', 'van Gogh, Vincent');
});

it('returns 404 when artwork does not exist', function () {
    $response = getJson(route('artworks.show', ['artwork' => 'non-existent-artwork']));

    $response->assertNotFound();
});

it('returns localized attributes based on Accept-Language header', function () {
    Artwork::factory()->create([
        'slug' => 'czech-artwork',
        'title' => ['en' => 'English Title', 'cs' => 'Český název'],
        'description' => ['en' => 'English Description', 'cs' => 'Český popis'],
        'dating' => ['en' => 'English Dating', 'cs' => 'Česká datace'],
    ]);

    $response = withHeader('Accept-Language', 'cs')
        ->getJson(route('artworks.show', ['artwork' => 'czech-artwork']));

    $response->assertOk()
        ->assertJsonPath('data.attributes.title', 'Český název')
        ->assertJsonPath('data.attributes.description', 'Český popis')
        ->assertJsonPath('data.attributes.dating', 'Česká datace');
});

it('includes attached media in response', function () {
    Storage::fake('public');

    $artwork = Artwork::factory()->create([
        'slug' => 'artwork-with-image',
    ]);

    $file = UploadedFile::fake()->image('painting.jpg', 1000, 1000);
    $artwork->addMedia($file)->toMediaCollection('images', 'public');

    $response = getJson(route('artworks.show', ['artwork' => 'artwork-with-image', 'include' => 'media']));

    $response->assertOk()
        ->assertJsonCount(1, 'data.relationships.media.data')
        ->assertJsonPath('included.0.type', 'media')
        ->assertJsonPath('included.0.attributes.file_name', 'painting.jpg');
});
