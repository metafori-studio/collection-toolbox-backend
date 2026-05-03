<?php

use Spatie\Prometheus\Actions\RenderCollectorsAction;
use Spatie\Prometheus\Http\Middleware\AllowIps;

return [

    'enabled' => env('PROMETHEUS_ENABLED', true),

    'urls' => [
        'default' => env('PROMETHEUS_ROUTE', 'metrics'),
    ],

    /*
     * Only these IPs will be allowed. Leave empty to allow all.
     * In production, restrict to your Prometheus scraper IP(s).
     */
    'allowed_ips' => array_filter(explode(',', env('PROMETHEUS_ALLOWED_IPS', ''))),

    'default_namespace' => env('PROMETHEUS_NAMESPACE', 'app'),

    'middleware' => [
        AllowIps::class,
    ],

    'actions' => [
        'render_collectors' => RenderCollectorsAction::class,
    ],

    'wipe_storage_after_rendering' => false,

    /*
     * Storage backend for metrics data:
     *   null       → in-memory only (data lost between requests; useful for local dev without any cache)
     *   'database' → Laravel DB cache (works out of the box, no extra deps)
     *   'redis'    → Redis cache (recommended for production — better throughput)
     */
    'cache' => env('PROMETHEUS_CACHE_STORE', 'database'),

    /*
     * Histogram bucket boundaries per collector type.
     */
    'buckets' => [
        'http' => [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0, 10.0],
        'database' => [0.001, 0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1.0],
        'queue' => [0.1, 0.5, 1.0, 2.5, 5.0, 10.0, 30.0, 60.0, 120.0],
    ],

    /*
     * Requests to these path prefixes are not recorded.
     */
    'ignored_paths' => explode(',', env('PROMETHEUS_IGNORED_PATHS', '/metrics,/up,/telescope,/_debugbar,/horizon')),

    /*
     * DB queries slower than this (in seconds) are also tallied as slow queries.
     */
    'slow_query_threshold' => (float) env('PROMETHEUS_SLOW_QUERY_THRESHOLD', 0.1),

];
