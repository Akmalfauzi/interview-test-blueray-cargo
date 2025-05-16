<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_user_can_view_own_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'profile_photo_url',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_user_can_update_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'name' => $updateData['name'],
                    'email' => $updateData['email']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email']
        ]);
    }

    public function test_user_cannot_update_profile_with_invalid_data()
    {
        $updateData = [
            'name' => '',
            'email' => 'invalid-email'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_user_can_update_profile_photo()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/profile/photo', [
            'photo' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'profile_photo_url'
                ]
            ]);

        Storage::disk('public')->assertExists('profile-photos/' . $file->hashName());
    }

    public function test_user_cannot_update_profile_photo_with_invalid_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/profile/photo', [
            'photo' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    public function test_user_can_delete_profile_photo()
    {
        Storage::fake('public');
        
        // First upload a photo
        $file = UploadedFile::fake()->image('profile.jpg');
        $this->user->updateProfilePhoto($file);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson('/api/v1/profile/photo');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile photo deleted successfully'
            ]);

        $this->assertNull($this->user->fresh()->profile_photo_path);
    }
} 