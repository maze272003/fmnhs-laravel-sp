<?php

namespace App\Services;

use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected function handleException(\Exception $e, string $context = ''): void
    {
        $message = $context ? "[$context] {$e->getMessage()}" : $e->getMessage();
        
        Log::error($message, [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if ($e instanceof ServiceException) {
            throw $e;
        }

        throw ServiceException::operationFailed($message);
    }

    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge(['service' => static::class], $context));
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge(['service' => static::class], $context));
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, array_merge(['service' => static::class], $context));
    }

    protected function validateRequired(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw ServiceException::validationFailed("Field '{$field}' is required");
            }
        }
    }

    protected function validateRange(float $value, float $min, float $max, string $fieldName): void
    {
        if ($value < $min || $value > $max) {
            throw ServiceException::validationFailed("{$fieldName} must be between {$min} and {$max}");
        }
    }
}
