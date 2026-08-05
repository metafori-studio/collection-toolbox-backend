<?php

use Metafori\Core\Enums\Language;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Http\Resources\PrecisionDateResource;
use Metafori\Etno\Models\Document;

use function Pest\Laravel\getJson;

it('can show a complete document with all relations', function () {
    $document = Document::factory()
        ->published()
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasKeywords(2)
        ->hasResearchCollections(2)
        ->hasOriginators(2)
        ->for(MunicipalityPart::factory(), 'locality')
        ->create();

    $response = getJson(route('api.etno.documents.show', $document->id));

    $document->load('originators.person');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.authors')
        ->assertJsonCount(2, 'data.researchers')
        ->assertJsonCount(2, 'data.originators')
        ->assertJsonCount(2, 'data.keywords')
        ->assertJsonCount(2, 'data.research_collections')
        ->assertJsonStructure([
            'data' => [
                'id',
                'doi',
                'title',
                'subtitle',
                'abstract',
                'general_note',
                'terms_of_use',
                'location_note',
                'content_note',
                'technical_note',
                'type',
                'languages',
                'accrual_method',
                'collection_method',
                'access_rights',
                'license',
                'production_methods',
                'time_period' => [
                    'start',
                    'end',
                ],
                'submission_date' => [
                    'start',
                    'end',
                ],
                'publication_date' => [
                    'start',
                    'end',
                ],
                'how_to_cite',
                'institution' => [
                    'id',
                    'name',
                    'ror_id',
                ],
                'project' => [
                    'id',
                    'title',
                ],
                'locality' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                    ],
                ],
                'authors' => [
                    '*' => [
                        'id',
                        'given_name',
                        'family_name',
                        'display_name',
                        'orcid',
                    ],
                ],
                'researchers' => [
                    '*' => [
                        'id',
                        'given_name',
                        'family_name',
                        'display_name',
                        'orcid',
                    ],
                ],
                'originators' => [
                    '*' => [
                        'id',
                        'label',
                        'person' => [
                            'id',
                            'given_name',
                            'family_name',
                            'display_name',
                            'orcid',
                        ],
                    ],
                ],
                'keywords' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'research_collections' => [
                    '*' => [
                        'id',
                        'title',
                    ],
                ],
                'extents' => [
                    '*' => [
                        'value',
                        'unit',
                    ],
                ],
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $document->id,
                'doi' => $document->doi,
                'title' => $document->title,
                'subtitle' => $document->subtitle,
                'abstract' => $document->abstract,
                'general_note' => $document->general_note,
                'terms_of_use' => $document->terms_of_use,
                'location_note' => $document->location_note,
                'content_note' => $document->content_note,
                'technical_note' => $document->technical_note,
                'how_to_cite' => $document->how_to_cite,
                'type' => $document->type?->value,
                'extents' => collect($document->extents)->toArray(),
                'languages' => collect($document->languages)
                    ->map(fn (Language $lang) => $lang->value)
                    ->toArray(),
                'accrual_method' => $document->accrual_method?->value,
                'collection_method' => $document->collection_method?->value,
                'access_rights' => $document->access_rights?->value,
                'license' => $document->license?->value,
                'production_methods' => collect($document->production_methods)
                    ->map(fn (ProductionMethod $method) => $method->value)
                    ->toArray(),
                'time_period' => PrecisionDateResource::make($document->time_period)?->resolve(),
                'submission_date' => PrecisionDateResource::make($document->submission_date)?->resolve(),
                'publication_date' => PrecisionDateResource::make($document->publication_date)?->resolve(),
                'institution' => [
                    'id' => $document->institution->id,
                    'name' => $document->institution->name,
                    'ror_id' => $document->institution->ror_id,
                ],
                'project' => [
                    'id' => $document->project->id,
                    'title' => $document->project->title,
                ],
                'locality' => [
                    [
                        'id' => $document->locality->id,
                        'name' => $document->locality->name,
                        'type' => 'municipality_part',
                    ],
                    [
                        'id' => $document->locality->municipality->id,
                        'name' => $document->locality->municipality->name,
                        'type' => 'municipality',
                    ],
                    [
                        'id' => $document->locality->municipality->district->id,
                        'name' => $document->locality->municipality->district->name,
                        'type' => 'district',
                    ],
                    [
                        'id' => $document->locality->municipality->district->region->id,
                        'name' => $document->locality->municipality->district->region->name,
                        'type' => 'region',
                    ],
                    [
                        'id' => $document->locality->municipality->district->region->country->id,
                        'name' => $document->locality->municipality->district->region->country->name,
                        'type' => 'country',
                    ],
                ],
                'originators' => $document->originators->map(fn ($originator) => [
                    'id' => $originator->id,
                    'label' => $originator->label,
                    'person' => [
                        'id' => $originator->person->id,
                        'given_name' => $originator->person->given_name,
                        'family_name' => $originator->person->family_name,
                        'display_name' => $originator->person->display_name,
                        'orcid' => $originator->person->orcid,
                    ],
                ])->toArray(),
            ],
        ]);
});

it('returns 404 for non-existent document', function () {
    $response = getJson(route('api.etno.documents.show', 'invalid-id'));

    $response->assertStatus(404);
});

it('returns 404 for unpublished document', function () {
    $document = Document::factory()
        ->published(false)
        ->create();

    $response = getJson(route('api.etno.documents.show', $document->id));

    $response->assertStatus(404);
});
