<?php

namespace App\Support\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public static function invalidGrade(float $grade): self
    {
        return new self("Grade value {$grade} must be between 0 and 100.");
    }

    public static function invalidDate(string $date): self
    {
        return new self("Invalid date format: {$date}");
    }

    public static function invalidAttendanceStatus(string $status): self
    {
        return new self("Invalid attendance status: {$status}");
    }

    public static function fileUploadFailed(string $message): self
    {
        return new self("File upload failed: {$message}");
    }

    public static function authenticationFailed(): self
    {
        return new self("Authentication failed. Please check your credentials.");
    }

    public static function authorizationFailed(): self
    {
        return new self("You are not authorized to perform this action.");
    }

    public static function validationFailed(string $message): self
    {
        return new self("Validation failed: {$message}");
    }

    public static function operationFailed(string $operation, string $message): self
    {
        return new self("Failed to {$operation}: {$message}");
    }
}
