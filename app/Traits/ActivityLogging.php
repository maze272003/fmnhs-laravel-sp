<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for activity logging across models and controllers.
 *
 * Usage:
 *   $this->logActivity('created', $student, 'Student account created');
 *   $this->logModelEvent($student, 'updated', $oldData, $newData);
 */
trait ActivityLogging
{
    /**
     * Log an activity to the audit trail.
     *
     * @param string $action The action performed (created, updated, deleted, etc.)
     * @param Model|string $modelOrType The model instance or model type string
     * @param string|null $description Optional description of the activity
     * @param array|null $oldData Old data (for updates)
     * @param array|null $newData New data (for creates/updates)
     * @param string|null $guard The authentication guard
     * @return AuditTrail|null
     */
    protected function logActivity(
        string $action,
        Model|string $modelOrType,
        ?string $description = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $guard = null
    ): ?AuditTrail {
        $modelType = is_string($modelOrType) ? $modelOrType : get_class($modelOrType);
        $modelId = is_string($modelOrType) ? null : $modelOrType->getKey();
        $guard = $guard ?? $this->getDefaultGuard();

        return AuditTrail::log(
            $modelType,
            $modelId,
            $action,
            $description,
            $oldData,
            $newData,
            $guard,
            $this->getAuthId($guard)
        );
    }

    /**
     * Log a model creation event.
     *
     * @param Model $model
     * @param string|null $description
     * @param string|null $guard
     * @return AuditTrail|null
     */
    protected function logModelCreated(Model $model, ?string $description = null, ?string $guard = null): ?AuditTrail
    {
        return $this->logActivity(
            'created',
            $model,
            $description ?? "Created new {$this->getModelName($model)}",
            null,
            $model->toArray(),
            $guard
        );
    }

    /**
     * Log a model update event.
     *
     * @param Model $model
     * @param array $oldData
     * @param string|null $description
     * @param string|null $guard
     * @return AuditTrail|null
     */
    protected function logModelUpdated(Model $model, array $oldData, ?string $description = null, ?string $guard = null): ?AuditTrail
    {
        return $this->logActivity(
            'updated',
            $model,
            $description ?? "Updated {$this->getModelName($model)}",
            $oldData,
            $model->toArray(),
            $guard
        );
    }

    /**
     * Log a model deletion event.
     *
     * @param Model $model
     * @param string|null $description
     * @param string|null $guard
     * @return AuditTrail|null
     */
    protected function logModelDeleted(Model $model, ?string $description = null, ?string $guard = null): ?AuditTrail
    {
        return $this->logActivity(
            'deleted',
            $model,
            $description ?? "Deleted {$this->getModelName($model)}",
            $model->toArray(),
            null,
            $guard
        );
    }

    /**
     * Log a custom action with context.
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @param string|null $guard
     * @return AuditTrail|null
     */
    protected function logCustomAction(string $action, string $description, array $context = [], ?string $guard = null): ?AuditTrail
    {
        $guard = $guard ?? $this->getDefaultGuard();

        return AuditTrail::log(
            'System',
            null,
            $action,
            $description,
            null,
            $context,
            $guard,
            $this->getAuthId($guard)
        );
    }

    /**
     * Log a login event.
     *
     * @param Model $user
     * @param string $guard
     * @return AuditTrail|null
     */
    protected function logLogin(Model $user, string $guard): ?AuditTrail
    {
        return $this->logActivity(
            'login',
            $user,
            "User logged in via {$guard}",
            null,
            ['ip' => request()->ip(), 'user_agent' => request()->userAgent()],
            $guard
        );
    }

    /**
     * Log a logout event.
     *
     * @param Model $user
     * @param string $guard
     * @return AuditTrail|null
     */
    protected function logLogout(Model $user, string $guard): ?AuditTrail
    {
        return $this->logActivity(
            'logout',
            $user,
            "User logged out from {$guard}",
            null,
            null,
            $guard
        );
    }

    /**
     * Get the short name of a model.
     *
     * @param Model $model
     * @return string
     */
    protected function getModelName(Model $model): string
    {
        return strtolower(class_basename($model));
    }

    /**
     * Get the default guard for the current context.
     * Override in controllers to provide context-specific guard.
     *
     * @return string
     */
    protected function getDefaultGuard(): string
    {
        return 'web';
    }

    /**
     * Get the authenticated user's ID for a guard.
     *
     * @param string $guard
     * @return int|null
     */
    protected function getAuthId(string $guard): ?int
    {
        return auth($guard)->id();
    }
}