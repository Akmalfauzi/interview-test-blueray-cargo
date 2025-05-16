<?php

namespace Tests\Unit\Services\V1\API\Auth;

use App\Models\User;
use App\Services\V1\API\Auth\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
    }

    public function test_generate_token()
    {
        // Create a test user
        $user = User::factory()->create();

        // Generate token
        $token = $this->tokenService->generateToken($user);

        // Assert token was generated
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Assert token exists in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_generate_token_with_custom_name()
    {
        // Create a test user
        $user = User::factory()->create();

        // Generate token with custom name
        $token = $this->tokenService->generateToken($user, 'custom_token');

        // Assert token was generated
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Assert token exists in database with custom name
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'custom_token'
        ]);
    }

    public function test_revoke_token()
    {
        // Create a test user
        $user = User::factory()->create();

        // Generate token
        $token = $this->tokenService->generateToken($user);

        // Assert token was generated
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);

        // Revoke token
        $this->tokenService->revokeToken($user);

        // Assert token was revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }
} 