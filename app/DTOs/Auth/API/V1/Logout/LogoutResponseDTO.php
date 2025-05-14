<?php

namespace App\DTOs\Auth\API\V1\Logout;

class LogoutResponseDTO
{
    public function __construct(
        public readonly string $message,
    ) {}

    /**
     * Convert DTO to array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
} 