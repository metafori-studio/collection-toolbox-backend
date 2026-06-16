<?php

namespace Metafori\Monitoring\Http\Middleware;

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

        // Record start time before processing the request so we capture the full duration,
        // even if an exception is thrown while handling the request.
        $start = hrtime(true);

        /** @var Response|null $response */
        $response = null;
        $status = '500';
        $caught = null;
        try {
            /** @var Response $response */
            $response = $next($request);
            $status = (string) $response->getStatusCode();
        } catch (\Throwable $e) {
            // Preserve the exception to re‑throw after metrics are recorded.
            $caught = $e;
        } finally {
            $duration = (hrtime(true) - $start) / 1e9;
            try {
                $registry = app(CollectorRegistry::class);
                $ns = config('prometheus.default_namespace', 'app');
                $method = $request->method();
                $route = $this->normalizeRoute($request);

                $registry
                    ->getOrRegisterCounter($ns, 'http_requests_total', 'Total HTTP requests', ['method', 'route', 'status_code'])
                    ->inc([$method, $route, $status]);

                $registry
                    ->getOrRegisterHistogram($ns, 'http_request_duration_seconds', 'HTTP request duration in seconds', ['method', 'route'], config('prometheus.buckets.http'))
                    ->observe($duration, [$method, $route]);
            } catch (\Throwable) {
                // Swallow any metric collection errors to avoid interfering with request flow.
            }

            // Re‑throw the original exception if one was caught.
            if ($caught !== null) {
                throw $caught;
            }
        }

        // At this point $response is guaranteed to be a Response instance.
        return $response;
    }

    private function normalizeRoute(Request $request): string
    {
        $route = $request->route();

        if ($route === null) {
            return 'unmatched';
        }

        // The catch-all fallback route exposes a useless '{fallbackPlaceholder}' URI
        // pattern, collapsing every unmatched path into a single label. Use the real
        // request path so the route is meaningful, with numeric segments collapsed to
        // ':id' to keep label cardinality bounded.
        if ($route->isFallback) {
            return $this->normalizeFallbackPath($request->path());
        }

        // Use the URI pattern to avoid high-cardinality labels from real parameter values.
        return '/'.ltrim($route->uri(), '/');
    }

    /**
     * Collapse numeric path segments (e.g. IDs) to ':id', so '/activities/12345'
     * becomes '/activities/:id' instead of one time series per ID.
     */
    private function normalizeFallbackPath(string $path): string
    {
        $segments = array_map(
            fn (string $segment): string => ctype_digit($segment) ? ':id' : $segment,
            explode('/', trim($path, '/'))
        );

        return '/'.implode('/', $segments);
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
