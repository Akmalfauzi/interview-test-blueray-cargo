<?php

namespace Tests\Unit\Services\V1\API\Auth;

use App\DTOs\V1\Login\LoginRequestDTO;
use App\DTOs\V1\Login\LoginResponseDTO;
use App\Models\User;
use App\Services\V1\API\Auth\LoginService;
use App\Services\V1\API\Auth\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoginService $loginService;
    private TokenService|MockObject $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = $this->createMock(TokenService::class);
        $this->loginService = new LoginService($this->tokenService);
    }

    public function test_login_successful()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Create login DTO
        $loginDTO = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'password123',
            remember: false
        );

        // Mock token generation
        $this->tokenService
            ->expects($this->once())
            ->method('generateToken')
            ->with($this->callback(function ($user) {
                return $user instanceof User && $user->email === 'test@example.com';
            }))
            ->willReturn('test-token');

        // Attempt login
        $response = $this->loginService->login($loginDTO);

        // Assert response
        $this->assertInstanceOf(LoginResponseDTO::class, $response);
        $this->assertEquals('Login successful', $response->message);
        $this->assertEquals($user->id, $response->user->id);
        $this->assertEquals('test-token', $response->token);
    }

    public function test_login_failed_with_invalid_credentials()
    {
        // Create login DTO with invalid credentials
        $loginDTO = new LoginRequestDTO(
            email: 'wrong@example.com',
            password: 'wrongpassword',
            remember: false
        );

        // Attempt login
        $response = $this->loginService->login($loginDTO);

        // Assert response is null
        $this->assertNull($response);
    }

    public function test_login_with_remember_me()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Create login DTO with remember me
        $loginDTO = new LoginRequestDTO(
            email: 'test@example.com',
            password: 'password123',
            remember: true
        );

        // Mock token generation
        $this->tokenService
            ->expects($this->once())
            ->method('generateToken')
            ->with($this->callback(function ($user) {
                return $user instanceof User && $user->email === 'test@example.com';
            }))
            ->willReturn('test-token');

        // Attempt login
        $response = $this->loginService->login($loginDTO);

        // Assert response
        $this->assertInstanceOf(LoginResponseDTO::class, $response);
        $this->assertEquals('Login successful', $response->message);
        $this->assertEquals($user->id, $response->user->id);
        $this->assertEquals('test-token', $response->token);
    }
} 