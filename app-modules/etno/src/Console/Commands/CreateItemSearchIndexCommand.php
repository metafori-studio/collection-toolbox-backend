<?php

namespace Metafori\Etno\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Metafori\Etno\Models\Item;
use OpenSearch\Client;

class CreateItemSearchIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etno:items-index {--force : Drop existing index before creating it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the etno_items OpenSearch index with proper mappings';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $indexName = (new Item)->searchableAs();

        if ($client->indices()->exists(['index' => $indexName])) {
            if (! $this->option('force')) {
                $this->warn("Index '{$indexName}' already exists. Use --force to recreate it. Keep in mind that recreating the index will require re-importing all items.");

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
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'document_id' => ['type' => 'keyword'],

                        // Translatable fields
                        'title' => [
                            'properties' => $locales->mapWithKeys(fn (string $locale) => [
                                $locale => [
                                    'type' => 'text',
                                    'fields' => [
                                        'keyword' => [
                                            'type' => 'keyword',
                                            'ignore_above' => 256,
                                        ],
                                    ],
                                ],
                            ])->toArray(),
                        ],
                        ...collect([
                            'subtitle',
                            'abstract',
                            'general_note',
                            'terms_of_use',
                            'location_note',
                            'content_note',
                            'technical_note',
                        ])->mapWithKeys(fn (string $field) => [
                            $field => [
                                'properties' => $locales->mapWithKeys(fn (string $locale) => [
                                    $locale => ['type' => 'text'],
                                ])->toArray(),
                            ],
                        ]),

                        // Exact match enum-like fields
                        'type' => ['type' => 'keyword'],
                        'language' => ['type' => 'keyword'],
                        'accrual_method' => ['type' => 'keyword'],
                        'collection_method' => ['type' => 'keyword'],
                        'access_rights' => ['type' => 'keyword'],
                        'license' => ['type' => 'keyword'],
                        'production_methods' => ['type' => 'keyword'],

                        // Ranges
                        'time_period' => ['type' => 'date_range'],

                        // Localities
                        'country' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'region' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'district' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'municipality' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'municipality_part' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'location' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],

                        // Relations mapped as sub-objects with typed IDs
                        'institution' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'project' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'keyword' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'research_collection' => [
                            'properties' => ['id' => ['type' => 'keyword']],
                        ],
                        'author' => [
                            'properties' => ['person_id' => ['type' => 'keyword']],
                        ],
                        'researcher' => [
                            'properties' => ['person_id' => ['type' => 'keyword']],
                        ],
                        'originator' => [
                            'properties' => ['person_id' => ['type' => 'keyword']],
                        ],
                    ],
                ],
            ],
        ]);

        $this->info("Index '{$indexName}' created successfully.");

        $this->info('Importing items...');
        Artisan::call('scout:import', ['model' => Item::class], $this->getOutput());
        $this->info('Items imported successfully.');

        return self::SUCCESS;
    }
}
