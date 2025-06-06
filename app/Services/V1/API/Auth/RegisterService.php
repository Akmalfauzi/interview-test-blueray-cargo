<?php

namespace App\Services\V1\API\Auth;

use App\DTOs\V1\Register\RegisterRequestDTO;
use App\DTOs\V1\Register\RegisterResponseDTO;
use App\Models\User;
use App\Services\V1\API\Auth\TokenService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

        // Assign default user role
        $userRole = Role::where('name', 'like', '%user%')->first();
        if ($userRole) {
            $user->assignRole($userRole);
        }

        $token = $this->tokenService->generateToken($user);

        return new RegisterResponseDTO(
            message: 'Registration successful',
            user: $user,
            token: $token
        );
    }
} 