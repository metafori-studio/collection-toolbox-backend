<?php

namespace Metafori\Monitoring\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

            // HTTP exceptions (e.g. 4xx) carry their real status code; record it
            // instead of defaulting to 500 so they aren't mislabeled as server errors.
            if ($e instanceof HttpExceptionInterface) {
                $status = (string) $e->getStatusCode();
            }
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
     * Collapse high-cardinality path segments to placeholders so the unmatched
     * fallback route doesn't spawn one time series per identifier. For example
     * '/activities/12345' becomes '/activities/:id' and
     * '/users/550e8400-e29b-41d4-a716-446655440000' becomes '/users/:uuid'.
     */
    private function normalizeFallbackPath(string $path): string
    {
        $segments = array_map(
            $this->normalizeSegment(...),
            explode('/', trim($path, '/'))
        );

        return '/'.implode('/', $segments);
    }

    private function normalizeSegment(string $segment): string
    {
        if (ctype_digit($segment)) {
            return ':id';
        }

        // RFC 4122 UUIDs.
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $segment) === 1) {
            return ':uuid';
        }

        // Long hex strings (e.g. hashes, tokens, ObjectIds).
        if (strlen($segment) >= 16 && ctype_xdigit($segment)) {
            return ':hex';
        }

        return $segment;
    }

    private function shouldRecord(Request $request): bool
    {
        if (! config('prometheus.enabled')) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');

        foreach (config('prometheus.ignored_paths', []) as $prefix) {
            $prefix = rtrim($prefix, '/');

            // Match on route boundaries so '/up' ignores '/up' and '/up/*' but
            // not '/upload'.
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return false;
            }
        }

        return true;
    }
}
