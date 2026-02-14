<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\EagerLoadsRelations;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller with shared functionality for all controllers.
 *
 * This controller provides:
 * - Standardized API responses via ApiResponse trait
 * - Eager loading helpers via EagerLoadsRelations trait
 * - Authorization and validation helpers
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse, EagerLoadsRelations;

    /**
     * Default pagination limit.
     */
    protected int $perPage = 15;

    /**
     * Get the authenticated user for a specific guard.
     *
     * @param string $guard
     * @return mixed
     */
    protected function getAuthUser(string $guard = 'web'): mixed
    {
        return auth($guard)->user();
    }

    /**
     * Get the authenticated user's ID for a specific guard.
     *
     * @param string $guard
     * @return int|null
     */
    protected function getAuthId(string $guard = 'web'): ?int
    {
        return auth($guard)->id();
    }

    /**
     * Check if user is authenticated for a specific guard.
     *
     * @param string $guard
     * @return bool
     */
    protected function isAuthenticated(string $guard = 'web'): bool
    {
        return auth($guard)->check();
    }

    /**
     * Redirect back with a success message.
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBackWithSuccess(string $message)
    {
        return redirect()->back()->with('success', $message);
    }

    /**
     * Redirect back with an error message.
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBackWithError(string $message)
    {
        return redirect()->back()->with('error', $message);
    }

    /**
     * Redirect to a named route with a success message.
     *
     * @param string $route
     * @param array $parameters
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToRouteWithSuccess(string $route, array $parameters = [], string $message = '')
    {
        $redirect = redirect()->route($route, $parameters);
        
        if ($message) {
            $redirect->with('success', $message);
        }
        
        return $redirect;
    }

    /**
     * Log an action for audit trail.
     *
     * @param string $action
     * @param string $modelType
     * @param int $modelId
     * @param array|null $oldData
     * @param array|null $newData
     */
    protected function logAuditTrail(string $action, string $modelType, int $modelId, ?array $oldData = null, ?array $newData = null): void
    {
        \App\Models\AuditTrail::log(
            $modelType,
            $modelId,
            $action,
            null,
            $oldData,
            $newData,
            $this->getGuardName(),
            $this->getAuthId($this->getGuardName())
        );
    }

    /**
     * Get the current guard name based on the controller context.
     * Override in child controllers.
     *
     * @return string
     */
    protected function getGuardName(): string
    {
        return 'web';
    }
}