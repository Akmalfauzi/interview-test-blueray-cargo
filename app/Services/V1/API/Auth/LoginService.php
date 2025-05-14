<?php

namespace App\Services\V1\API\Auth;

use App\DTOs\V1\Login\LoginRequestDTO;
use App\DTOs\V1\Login\LoginResponseDTO;
use App\Services\V1\API\Auth\TokenService;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Attempt to authenticate a user and generate token
     *
     * @param LoginRequestDTO $loginDTO
     * @return LoginResponseDTO|null
     */
    public function login(LoginRequestDTO $loginDTO): ?LoginResponseDTO
    {
        if (!Auth::attempt($loginDTO->toArray())) {
            return null;
        }

        $user = Auth::user();
        $token = $this->tokenService->generateToken($user);

        return new LoginResponseDTO(
            message: 'Login successful',
            user: $user,
            token: $token
        );
    }
} 