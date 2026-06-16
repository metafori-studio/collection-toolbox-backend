<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\WorkerStarting;
use Prometheus\CollectorRegistry;
use Spatie\Prometheus\Facades\Prometheus;

class MetricsServiceProvider extends ServiceProvider
{
    /** @var array<string, int> hrtime() start per job ID, kept in worker memory */
    private static array $jobStartTimes = [];

    /**
     * Prevents re-entrant DB listener calls.
     *
     * When the cache store is database-backed, every metric observation issues
     * a cache SELECT + INSERT/UPDATE. Those queries fire QueryExecuted, which
     * re-enters this listener, which issues more cache queries → exponential
     * recursion → OOM.
     */
    private static bool $recordingDbMetric = false;

    public function boot(): void
    {
        if (! config('prometheus.enabled')) {
            return;
        }

        $this->bootRuntimeGauges();
        $this->bootDatabaseCollector();
        $this->bootQueueCollector();
        $this->bootOctaneCollector();
    }

    private function bootRuntimeGauges(): void
    {
        $ns = config('prometheus.default_namespace', 'app');

        Prometheus::addGauge('PHP memory usage bytes')
            ->name('php_memory_usage_bytes')
            ->namespace($ns)
            ->helpText('Current PHP memory allocation (real)')
            ->value(fn () => (float) memory_get_usage(true));

        Prometheus::addGauge('PHP memory peak bytes')
            ->name('php_memory_peak_bytes')
            ->namespace($ns)
            ->helpText('Peak PHP memory allocation (real)')
            ->value(fn () => (float) memory_get_peak_usage(true));
    }

    private function bootDatabaseCollector(): void
    {
        $ns = config('prometheus.default_namespace', 'app');
        $buckets = config('prometheus.buckets.database');
        $threshold = config('prometheus.slow_query_threshold', 0.1);

        DB::listen(function ($query) use ($ns, $buckets, $threshold): void {
            if (self::$recordingDbMetric) {
                return;
            }

            self::$recordingDbMetric = true;
            try {
                $registry = $this->app->make(CollectorRegistry::class);
                $connection = $query->connectionName ?? 'default';
                $duration = $query->time / 1000; // ms → s

                $registry
                    ->getOrRegisterCounter($ns, 'db_queries_total', 'Total DB queries', ['connection'])
                    ->inc([$connection]);

                $registry
                    ->getOrRegisterHistogram($ns, 'db_query_duration_seconds', 'DB query duration in seconds', ['connection'], $buckets)
                    ->observe($duration, [$connection]);

                if ($duration >= $threshold) {
                    $registry
                        ->getOrRegisterCounter($ns, 'db_slow_queries_total', 'DB queries exceeding slow threshold', ['connection'])
                        ->inc([$connection]);
                }
            } catch (\Throwable) {
            } finally {
                self::$recordingDbMetric = false;
            }
        });
    }

    private function bootQueueCollector(): void
    {
        $ns = config('prometheus.default_namespace', 'app');
        $buckets = config('prometheus.buckets.queue');

        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event): void {
            self::$jobStartTimes[$event->job->getJobId()] = hrtime(true);
        });

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) use ($ns, $buckets): void {
            try {
                $registry = $this->app->make(CollectorRegistry::class);
                $queue = $event->job->getQueue();
                $job = class_basename($event->job->resolveName());
                $duration = $this->consumeJobDuration($event->job->getJobId());

                $registry
                    ->getOrRegisterCounter($ns, 'queue_jobs_total', 'Total queue jobs by outcome', ['queue', 'job', 'status'])
                    ->inc([$queue, $job, 'processed']);

                if ($duration !== null) {
                    $registry
                        ->getOrRegisterHistogram($ns, 'queue_job_duration_seconds', 'Queue job processing duration', ['queue', 'job'], $buckets)
                        ->observe($duration, [$queue, $job]);
                }
            } catch (\Throwable) {
            }
        });

        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) use ($ns): void {
            try {
                $this->consumeJobDuration($event->job->getJobId()); // cleanup only
                $job = class_basename($event->job->resolveName());
                $this->app->make(CollectorRegistry::class)
                    ->getOrRegisterCounter($ns, 'queue_jobs_total', 'Total queue jobs by outcome', ['queue', 'job', 'status'])
                    ->inc([$event->job->getQueue(), $job, 'failed']);
            } catch (\Throwable) {
            }
        });
    }

    private function consumeJobDuration(string $jobId): ?float
    {
        if (! isset(self::$jobStartTimes[$jobId])) {
            return null;
        }

        $duration = (hrtime(true) - self::$jobStartTimes[$jobId]) / 1e9;
        unset(self::$jobStartTimes[$jobId]);

        return $duration;
    }

    private function bootOctaneCollector(): void
    {
        if (! class_exists(WorkerStarting::class)) {
            return;
        }

        $ns = config('prometheus.default_namespace', 'app');

        $this->app['events']->listen(WorkerStarting::class, function () use ($ns): void {
            try {
                $this->app->make(CollectorRegistry::class)
                    ->getOrRegisterCounter($ns, 'octane_worker_starts_total', 'Total Octane worker start events', [])
                    ->inc([]);
            } catch (\Throwable) {
            }
        });
    }
}
