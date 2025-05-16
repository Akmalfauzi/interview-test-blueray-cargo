<?php

namespace Tests\Feature\Tracking;

use App\Models\User;
use App\Models\Order;
use App\Models\Tracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
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

    public function test_user_can_track_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'raw_biteship_payload' => [
                'courier' => [
                    'tracking_id' => 'TEST123'
                ]
            ]
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tracking/TEST123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                    'courier' => [
                        'company',
                        'tracking_id',
                        'waybill_id',
                        'link'
                    ],
                    'history' => [
                        '*' => [
                            'status',
                            'note',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    public function test_user_cannot_track_nonexistent_order()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tracking/NONEXISTENT123');

        $response->assertStatus(404);
    }

    public function test_user_can_view_tracking_history()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        Tracking::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tracking/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'order_id',
                            'raw_biteship_payload',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'total',
                    'per_page'
                ]
            ]);
    }

    public function test_user_can_search_tracking_history()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        Tracking::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => [
                'status' => 'delivered',
                'courier' => [
                    'name' => 'JNE',
                    'tracking_id' => 'TEST123'
                ]
            ]
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/tracking/history?search=delivered');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'total',
                    'per_page'
                ]
            ]);
    }
} 