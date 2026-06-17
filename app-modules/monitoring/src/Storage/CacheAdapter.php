<?php

namespace Metafori\Monitoring\Storage;

use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\MetricFamilySamples;
use Prometheus\Summary;
use Spatie\Prometheus\Adapters\LaravelCacheAdapter;

/**
 * Works around a bug in spatie/laravel-prometheus 1.6.0.
 *
 * Its LaravelCacheAdapter::collect() calls fetch() for each metric type but
 * discards the result, so it never hydrates the in-memory stores from the cache.
 * A scrape running in a fresh process/request therefore renders nothing, even
 * though the samples are persisted in the cache. We load them before collecting.
 */
class CacheAdapter extends LaravelCacheAdapter
{
    /**
     * @return MetricFamilySamples[]
     */
    public function collect(bool $sortMetrics = true): array
    {
        $this->counters = $this->fetch(Counter::TYPE);
        $this->gauges = $this->fetch(Gauge::TYPE);
        $this->histograms = $this->fetch(Histogram::TYPE);
        $this->summaries = $this->fetch(Summary::TYPE);

        return parent::collect($sortMetrics);
    }
}
