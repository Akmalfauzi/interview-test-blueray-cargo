<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class TokenService
{
    /**
     * Generate authentication token for user
     *
     * @param User $user
     * @param string $tokenName
     * @return string
     */
    public function generateToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke all tokens for the user
     *
     * @param Authenticatable $user
     * @return void
     */
    public function revokeToken(Authenticatable $user): void
    {
        $user->tokens()->delete();
    }
} 