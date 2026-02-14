<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for rate limiting sensitive endpoints.
 *
 * Usage in routes:
 *   Route::middleware('sensitive-rate-limiter:10,1')->post('/sensitive-action', ...);
 *   This limits to 10 requests per minute.
 */
class SensitiveRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param int $maxAttempts Maximum attempts per decay minutes
     * @param int $decayMinutes Time window in minutes
     * @return Response
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        $key = $this->resolveKey($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response;
    }

    /**
     * Resolve the rate limiting key for the request.
     *
     * @param Request $request
     * @return string
     */
    protected function resolveKey(Request $request): string
    {
        // Use user ID if authenticated, otherwise IP address
        $identifier = $request->user()?->id ?? $request->ip();

        // Include route name for more granular limiting
        $routeName = $request->route()?->getName() ?? $request->path();

        return strtolower('sensitive:' . $routeName . ':' . $identifier);
    }

    /**
     * Build the response for too many attempts.
     *
     * @param string $key
     * @param int $maxAttempts
     * @return Response
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Too many attempts. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }
}