<?php

namespace App\Services\Auth\API\V1;

use App\DTOs\Auth\API\V1\Logout\LogoutResponseDTO;
use App\Services\Auth\TokenService;
use Illuminate\Contracts\Auth\Authenticatable;

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

        return new LogoutResponseDTO(
            message: 'Successfully logged out'
        );
    }
} 