<?php

namespace App\DTOs\Auth\API\V1\Register;

use App\Models\User;

class RegisterResponseDTO
{
    public function __construct(
        public readonly string $message,
        public readonly User $user,
        public readonly string $token,
    ) {}

    /**
     * Convert DTO to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'user' => $this->user,
            'token' => $this->token,
        ];
    }
} 