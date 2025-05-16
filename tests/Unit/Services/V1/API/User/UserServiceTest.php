<?php

namespace Tests\Unit\Services\V1\API\User;

use App\Models\User;
use App\Services\V1\API\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function test_get_all_users()
    {
        // Create test users
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Doe']);
        $user3 = User::factory()->create(['name' => 'Bob Smith']);

        // Get all users
        $users = $this->userService->getAllUsers();

        // Assert users were returned
        $this->assertCount(3, $users);
        $this->assertEquals('John Doe', $users[0]->name);
        $this->assertEquals('Jane Doe', $users[1]->name);
        $this->assertEquals('Bob Smith', $users[2]->name);
    }
} 