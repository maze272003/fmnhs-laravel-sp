<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RedisResponseCache
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (! $this->shouldCacheRequest($request)) {
            return $next($request);
        }

        try {
            $store = Cache::store('redis');
            // Force a connection attempt early so we can catch failures cleanly
            $store->get('redis_response_cache:__ping__');
        } catch (\Throwable $e) {
            return $next($request); // fallback: app still works without redis
        }

        $cacheKey = $this->cacheKey($request);

        $cached = $store->get($cacheKey);

        if (is_array($cached) && isset($cached['content'], $cached['status'], $cached['headers'])) {
            $response = response($cached['content'], (int) $cached['status']);

            foreach ((array) $cached['headers'] as $name => $values) {
                foreach ((array) $values as $value) {
                    $response->headers->set($name, $value, false);
                }
            }

            $response->headers->set('X-Redis-Cache', 'HIT');

            return $response;
        }

        $response = $next($request);

        if (! $this->shouldCacheResponse($response)) {
            return $response;
        }

        $ttl = max((int) config('performance.redis_response_cache_ttl', 30), 1);

        try {
            $store->put($cacheKey, [
                'status' => $response->getStatusCode(),
                'headers' => $this->cacheableHeaders($response),
                'content' => $response->getContent(),
            ], now()->addSeconds($ttl));
        } catch (\Throwable $e) {
            return $response; // if redis dies mid-request, still don’t break app
        }

        $response->headers->set('X-Redis-Cache', 'MISS');

        return $response;
    }


    private function shouldCacheRequest(Request $request): bool
    {
        if (! (bool) config('performance.redis_response_cache_enabled', true)) {
            return false;
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        $except = (array) config('performance.redis_response_cache_except', []);

        foreach ($except as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        if ($request->header('Cache-Control') && str_contains((string) $request->header('Cache-Control'), 'no-cache')) {
            return false;
        }

        return true;
    }

    private function shouldCacheResponse(SymfonyResponse $response): bool
    {
        if (! $response instanceof Response) {
            return false;
        }

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($response->headers->has('Set-Cookie')) {
            return false;
        }

        return true;
    }

    private function cacheableHeaders(Response $response): array
    {
        $excluded = [
            'cache-control',
            'date',
            'etag',
            'expires',
            'set-cookie',
            'x-redis-cache',
        ];

        $headers = [];

        foreach ($response->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            if (in_array(strtolower($name), $excluded, true)) {
                continue;
            }

            $headers[$name] = $values;
        }

        return $headers;
    }

    private function cacheKey(Request $request): string
    {
        $payload = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'accept' => (string) $request->header('Accept'),
            'lang' => (string) $request->header('Accept-Language'),
            'authorization' => (string) $request->header('Authorization'),
            'session' => (string) $request->cookie(config('session.cookie', 'laravel_session')),
            'cookies' => $request->cookies->all(),
        ];

        return 'resp-cache:'.sha1(json_encode($payload));
    }
}
