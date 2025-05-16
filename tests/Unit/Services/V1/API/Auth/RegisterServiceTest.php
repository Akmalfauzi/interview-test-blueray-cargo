<?php

namespace Tests\Unit\Services\V1\API\Auth;

use App\DTOs\V1\Register\RegisterRequestDTO;
use App\DTOs\V1\Register\RegisterResponseDTO;
use App\Models\User;
use App\Services\V1\API\Auth\RegisterService;
use App\Services\V1\API\Auth\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegisterService $registerService;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = $this->createMock(TokenService::class);
        $this->registerService = new RegisterService($this->tokenService);
    }

    public function test_register_successful()
    {
        // Create user role
        $userRole = Role::create(['name' => 'user']);

        // Create register DTO
        $registerDTO = new RegisterRequestDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'password123'
        );

        // Mock token generation
        $this->tokenService
            ->expects($this->once())
            ->method('generateToken')
            ->willReturn('test-token');

        // Attempt registration
        $response = $this->registerService->register($registerDTO);

        // Assert response
        $this->assertInstanceOf(RegisterResponseDTO::class, $response);
        $this->assertEquals('Registration successful', $response->message);
        $this->assertEquals('test-token', $response->token);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Assert user has role
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_register_without_user_role()
    {
        // Create register DTO
        $registerDTO = new RegisterRequestDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'password123'
        );

        // Mock token generation
        $this->tokenService
            ->expects($this->once())
            ->method('generateToken')
            ->willReturn('test-token');

        // Attempt registration
        $response = $this->registerService->register($registerDTO);

        // Assert response
        $this->assertInstanceOf(RegisterResponseDTO::class, $response);
        $this->assertEquals('Registration successful', $response->message);
        $this->assertEquals('test-token', $response->token);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Assert user exists but has no role
        $user = User::where('email', 'test@example.com')->first();
        $this->assertFalse($user->hasRole('user'));
    }
} 