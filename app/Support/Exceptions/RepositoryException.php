<?php

namespace App\Support\Exceptions;

use Exception;

class RepositoryException extends Exception
{
    public static function modelNotFound(string $model, int $id): self
    {
        return new self("Model {$model} with ID {$id} not found.");
    }

    public static function createFailed(string $model, string $message): self
    {
        return new self("Failed to create {$model}: {$message}");
    }

    public static function updateFailed(string $model, int $id, string $message): self
    {
        return new self("Failed to update {$model} with ID {$id}: {$message}");
    }

    public static function deleteFailed(string $model, int $id, string $message): self
    {
        return new self("Failed to delete {$model} with ID {$id}: {$message}");
    }
}
