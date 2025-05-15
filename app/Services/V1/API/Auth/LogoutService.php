<?php

namespace App\Services\V1\API\Auth;

use App\DTOs\V1\Logout\LogoutResponseDTO;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class LogoutService
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Logout the current user by revoking their token
     *
     * @param Authenticatable $user
     * @return LogoutResponseDTO
     */
    public function logout(Authenticatable $user): LogoutResponseDTO
    {
        $this->tokenService->revokeToken($user);

        Auth::guard('web')->logout();

        return new LogoutResponseDTO(
            message: 'Successfully logged out'
        );
    }
} 