<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService extends BaseService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function login(string $email, string $password, string $guard): array
    {
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user || !Hash::check($password, $user->password)) {
                throw ServiceException::authenticationFailed('Invalid credentials');
            }

            Auth::guard($guard)->login($user);

            $this->logInfo("User logged in", ['email' => $email, 'guard' => $guard]);

            return [
                'user' => $user,
                'guard' => $guard,
            ];
        } catch (\Exception $e) {
            $this->handleException($e, 'login');
        }
    }

    public function logout(string $guard): void
    {
        try {
            $user = Auth::guard($guard)->user();

            Auth::guard($guard)->logout();

            $this->logInfo("User logged out", ['guard' => $guard]);
        } catch (\Exception $e) {
            $this->handleException($e, 'logout');
        }
    }

    public function refresh(string $guard): string
    {
        try {
            $this->logInfo("Session refreshed", ['guard' => $guard]);
            return $guard;
        } catch (\Exception $e) {
            $this->handleException($e, 'refresh');
        }
    }

    public function me(string $guard): array
    {
        try {
            $user = Auth::guard($guard)->user();

            if (!$user) {
                throw ServiceException::authenticationFailed('User not authenticated');
            }

            return [
                'user' => $user,
                'guard' => $guard,
            ];
        } catch (\Exception $e) {
            $this->handleException($e, 'me');
        }
    }

    public function changePassword(string $currentPassword, string $newPassword, string $guard): bool
    {
        try {
            $user = Auth::guard($guard)->user();

            if (!$user) {
                throw ServiceException::authenticationFailed('User not authenticated');
            }

            if (!Hash::check($currentPassword, $user->password)) {
                throw ServiceException::validationFailed('Current password is incorrect');
            }

            $this->userRepository->update($user->id, [
                'password' => Hash::make($newPassword),
            ]);

            $this->logInfo("Password changed", ['user_id' => $user->id]);

            return true;
        } catch (\Exception $e) {
            $this->handleException($e, 'changePassword');
        }
    }
}

            Auth::guard($guard)->login($user);

            $this->logInfo("User logged in", ['email' => $email, 'guard' => $guard]);

            return [
                'user' => $user,
                'guard' => $guard,
            ];
        } catch (\Exception $e) {
            $this->handleException($e, 'login');
        }
    }

    public function logout(string $guard): void
    {
        try {
            $user = Auth::guard($guard)->user();
            
            Auth::guard($guard)->logout();

            $this->logInfo("User logged out", ['guard' => $guard]);
        } catch (\Exception $e) {
            $this->handleException($e, 'logout');
        }
    }

    public function refresh(string $guard): string
    {
        try {
            $this->logInfo("Session refreshed", ['guard' => $guard]);
            return $guard;
        } catch (\Exception $e) {
            $this->handleException($e, 'refresh');
        }
    }

    public function me(string $guard): array
    {
        try {
            $user = Auth::guard($guard)->user();

            if (!$user) {
                throw ServiceException::authenticationFailed('User not authenticated');
            }

            return [
                'user' => $user,
                'guard' => $guard,
            ];
        } catch (\Exception $e) {
            $this->handleException($e, 'me');
        }
    }

    public function changePassword(string $currentPassword, string $newPassword, string $guard): bool
    {
        try {
            $user = Auth::guard($guard)->user();

            if (!$user) {
                throw ServiceException::authenticationFailed('User not authenticated');
            }

            if (!Hash::check($currentPassword, $user->password)) {
                throw ServiceException::validationFailed('Current password is incorrect');
            }

            $this->userRepository->update($user->id, [
                'password' => Hash::make($newPassword),
            ]);

            $this->logInfo("Password changed", ['user_id' => $user->id]);

            return true;
        } catch (\Exception $e) {
            $this->handleException($e, 'changePassword');
        }
    }
}
