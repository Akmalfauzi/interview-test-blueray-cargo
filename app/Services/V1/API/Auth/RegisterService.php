<?php

namespace App\Services\Auth\API\V1;

use App\DTOs\Auth\API\V1\Register\RegisterRequestDTO;
use App\DTOs\Auth\API\V1\Register\RegisterResponseDTO;
use App\Models\User;
use App\Services\Auth\TokenService;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Register a new user and generate token
     *
     * @param RegisterRequestDTO $registerDTO
     * @return RegisterResponseDTO
     */
    public function register(RegisterRequestDTO $registerDTO): RegisterResponseDTO
    {
        $user = User::create([
            'name' => $registerDTO->name,
            'email' => $registerDTO->email,
            'password' => Hash::make($registerDTO->password),
        ]);

        $token = $this->tokenService->generateToken($user);

        return new RegisterResponseDTO(
            message: 'Registration successful',
            user: $user,
            token: $token
        );
    }
} 