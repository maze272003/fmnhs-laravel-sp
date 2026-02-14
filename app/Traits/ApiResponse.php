<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait for standardized API responses.
 *
 * Usage:
 * - Use this trait in your controllers or services
 * - Call success(), error(), paginated(), etc. to return consistent JSON responses
 */
trait ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param mixed $data The response data
     * @param string $message Success message
     * @param int $code HTTP status code
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array|null $errors Validation errors or additional error details
     */
    protected function error(string $message = 'Error', int $code = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error response.
     *
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return a not found response.
     *
     * @param string $message Error message
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return an unauthorized response.
     *
     * @param string $message Error message
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden response.
     *
     * @param string $message Error message
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Return a paginated data response.
     *
     * @param mixed $paginator Laravel paginator instance
     * @param string $message Success message
     */
    protected function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Return a created response.
     *
     * @param mixed $data Created resource data
     * @param string $message Success message
     */
    protected function created(mixed $data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a no content response (for deletions).
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a server error response.
     *
     * @param string $message Error message
     * @param array|null $debug Debug information (only in non-production)
     */
    protected function serverError(string $message = 'Internal server error', ?array $debug = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($debug !== null && config('app.debug')) {
            $response['debug'] = $debug;
        }

        return response()->json($response, 500);
    }
}
