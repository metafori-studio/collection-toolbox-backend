<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;

final class RecordHttpMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldRecord($request)) {
            return $next($request);
        }

        $start = hrtime(true);

        /** @var Response $response */
        $response = $next($request);

        $duration = (hrtime(true) - $start) / 1e9;

        try {
            $registry = app(CollectorRegistry::class);
            $ns = config('prometheus.default_namespace', 'app');
            $method = $request->method();
            $route = $this->normalizeRoute($request);
            $status = (string) $response->getStatusCode();

            $registry
                ->getOrRegisterCounter($ns, 'http_requests_total', 'Total HTTP requests', ['method', 'route', 'status_code'])
                ->inc([$method, $route, $status]);

            $registry
                ->getOrRegisterHistogram($ns, 'http_request_duration_seconds', 'HTTP request duration in seconds', ['method', 'route'], config('prometheus.buckets.http'))
                ->observe($duration, [$method, $route]);
        } catch (\Throwable) {
        }

        return $response;
    }

    private function normalizeRoute(Request $request): string
    {
        $route = $request->route();

        if ($route === null) {
            return 'unmatched';
        }

        // Use the URI pattern to avoid high-cardinality labels from real parameter values.
        return '/'.ltrim($route->uri(), '/');
    }

    private function shouldRecord(Request $request): bool
    {
        if (! config('prometheus.enabled')) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');

        foreach (config('prometheus.ignored_paths', []) as $prefix) {
            if (str_starts_with($path, rtrim($prefix, '/'))) {
                return false;
            }
        }

        return true;
    }
}
