<?php

namespace Metafori\Art\Console\Commands;

use Illuminate\Console\Command;
use Metafori\Art\Models\Artwork;
use OpenSearch\Client;

class CreateArtworkSearchIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'art:artworks-index {--force : Drop existing index before creating it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the artworks OpenSearch index with proper mappings';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $indexName = (new Artwork)->searchableAs();

        if ($client->indices()->exists(['index' => $indexName])) {
            if (! $this->option('force')) {
                $this->warn("Index '{$indexName}' already exists. Use --force to recreate it. Keep in mind that recreating the index will require re-importing/syncing all artworks.");

                return self::FAILURE;
            }

            $this->info("Dropping existing index '{$indexName}'...");
            $client->indices()->delete(['index' => $indexName]);
        }

        $this->info("Creating index '{$indexName}' with mappings...");

        $locales = collect(config('app.locales'));

        $client->indices()->create([
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'cs_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'czech',
                            ],
                            'en_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'english',
                            ],
                        ],
                        'analyzer' => [
                            'cs_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'asciifolding',
                                    'cs_stemmer',
                                ],
                            ],
                            'en_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'asciifolding',
                                    'en_stemmer',
                                ],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'slug' => ['type' => 'keyword'],
                        'identifier' => ['type' => 'keyword'],
                        'title' => [
                            'properties' => $locales->mapWithKeys(fn (string $locale) => [
                                $locale => [
                                    'type' => 'text',
                                    'analyzer' => match ($locale) {
                                        'cs' => 'cs_analyzer',
                                        default => 'en_analyzer',
                                    },
                                    'fields' => [
                                        'keyword' => [
                                            'type' => 'keyword',
                                            'normalizer' => 'lowercase',
                                        ],
                                        'suggest' => [
                                            'type' => 'search_as_you_type',
                                        ],
                                    ],
                                ],
                            ])->toArray(),
                        ],
                        'description' => [
                            'properties' => $locales->mapWithKeys(fn (string $locale) => [
                                $locale => [
                                    'type' => 'text',
                                    'analyzer' => match ($locale) {
                                        'cs' => 'cs_analyzer',
                                        default => 'en_analyzer',
                                    },
                                    'fields' => [
                                        'keyword' => [
                                            'type' => 'keyword',
                                            'normalizer' => 'lowercase',
                                        ],
                                    ],
                                ],
                            ])->toArray(),
                        ],
                        'dating' => [
                            'properties' => $locales->mapWithKeys(fn (string $locale) => [
                                $locale => [
                                    'type' => 'text',
                                ],
                            ])->toArray(),
                        ],
                        'work_types' => ['type' => 'keyword'],
                        'object_types' => ['type' => 'keyword'],
                        'mediums' => ['type' => 'keyword'],
                        'techniques' => ['type' => 'keyword'],
                        'acquisition_method' => ['type' => 'keyword'],
                        'creation_date' => [
                            'type' => 'date_range',
                            'format' => 'yyyy-MM-dd',
                        ],
                        'author' => [
                            'properties' => [
                                'person_id' => ['type' => 'keyword'],
                                'label' => ['type' => 'keyword'],
                            ],
                        ],
                        'collection' => [
                            'properties' => [
                                'id' => ['type' => 'keyword'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->info("Index '{$indexName}' created successfully.");

        return self::SUCCESS;
    }
}
