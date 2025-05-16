<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'password' => Hash::make('password')
        ]);
    }

    public function test_user_can_view_profile_edit_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertStatus(200)
            ->assertViewIs('backend.v1.profile.edit');
    }

    public function test_user_can_update_profile_with_valid_data()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '081234567890',
            'address' => 'Updated Address'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email'],
            'phone' => $updateData['phone'],
            'address' => $updateData['address']
        ]);
    }

    public function test_user_cannot_update_profile_with_invalid_data()
    {
        $updateData = [
            'name' => '',
            'email' => 'invalid-email',
            'address' => str_repeat('a', 501) // Exceeds max length of 500
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'address']);
    }

    public function test_user_can_update_profile_with_photo()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.jpg');

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'profile_photo' => $file
        ];

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile berhasil diperbarui'
            ]);

        $this->assertTrue(Storage::disk('public')->exists('profile-photos/' . $file->hashName()));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email']
        ]);
    }

    public function test_user_cannot_update_profile_with_invalid_photo()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'profile_photo' => $file
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_photo']);
    }

    public function test_user_can_update_password()
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile berhasil diperbarui'
            ]);

        // Verify the password was updated by checking the database
        $this->assertTrue(
            Hash::check('newpassword123', $this->user->fresh()->password)
        );
    }

    public function test_user_cannot_update_password_with_invalid_current_password()
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password saat ini tidak sesuai'
            ]);
    }

    public function test_user_cannot_update_password_with_mismatched_confirmation()
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
} 