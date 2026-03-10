<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenSearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your OpenSearch settings. OpenSearch is an open
    | source search engine derived from Elasticsearch.
    |
    */

    'hosts' => [
        env('OPENSEARCH_URL', 'http://localhost:9200'),
    ],

    'username' => env('OPENSEARCH_USERNAME'),

    'password' => env('OPENSEARCH_PASSWORD'),

];
