<?php

namespace App\Contracts\Services;

interface AuthServiceInterface
{
    public function login(string $email, string $password, string $guard): array;
    public function logout(string $guard): void;
    public function refresh(string $guard): string;
    public function me(string $guard): array;
    public function changePassword(string $currentPassword, string $newPassword, string $guard): bool;
}
