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
        env('OPENSEARCH_HOST', 'localhost:9200'),
    ],

];
