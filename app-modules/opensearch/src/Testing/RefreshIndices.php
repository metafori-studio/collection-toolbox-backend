<?php

namespace Metafori\Opensearch\Testing;

use OpenSearch\Client;

trait RefreshIndices
{
    /**
     * Define hooks to refresh indices before each test.
     */
    public function setUpRefreshIndices(): void
    {
        $client = app(Client::class);
        $prefix = config('scout.prefix', '');
        $pattern = $prefix !== '' ? $prefix.'*' : '*';

        try {
            $client->deleteByQuery([
                'index' => $pattern,
                'body' => [
                    'query' => [
                        'match_all' => (object) [],
                    ],
                ],
                'refresh' => true,
                'ignore_unavailable' => true,
                'allow_no_indices' => true,
            ]);
        } catch (\Exception $e) {
            // Ignore any errors while clearing indices
        }
    }
}
