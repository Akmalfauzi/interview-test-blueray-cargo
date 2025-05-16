<?php

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
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

    public function test_user_can_create_order()
    {
        $orderData = [
            'sender_name' => 'John Doe',
            'sender_phone' => '081234567890',
            'sender_address' => 'Jl. Test No. 123',
            'receiver_name' => 'Jane Doe',
            'receiver_phone' => '089876543210',
            'receiver_address' => 'Jl. Test No. 456',
            'courier_code' => 'jne',
            'courier_name' => 'JNE',
            'service_type' => 'REG',
            'items' => [
                [
                    'name' => 'Test Item',
                    'quantity' => 1,
                    'weight' => 1.0,
                    'price' => 100000
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'shipper_name',
                    'shipper_phone',
                    'shipper_address',
                    'receiver_name',
                    'receiver_phone',
                    'receiver_address',
                    'items',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'shipper_name' => $orderData['sender_name'],
            'shipper_phone' => $orderData['sender_phone'],
            'receiver_name' => $orderData['receiver_name'],
            'receiver_phone' => $orderData['receiver_phone']
        ]);
    }

    public function test_user_cannot_create_order_with_invalid_data()
    {
        $orderData = [
            'sender_name' => '',
            'sender_phone' => '',
            'sender_address' => '',
            'receiver_name' => '',
            'receiver_phone' => '',
            'receiver_address' => '',
            'courier_code' => '',
            'courier_name' => '',
            'service_type' => '',
            'items' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'sender_name',
                'sender_phone',
                'sender_address',
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'courier_code',
                'courier_name',
                'service_type',
                'items'
            ]);
    }

    public function test_user_can_view_orders()
    {
        Order::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'shipper_name',
                            'shipper_phone',
                            'shipper_address',
                            'receiver_name',
                            'receiver_phone',
                            'receiver_address',
                            'items',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'total',
                    'per_page'
                ]
            ]);
    }

    public function test_user_can_view_specific_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'shipper_name',
                    'shipper_phone',
                    'shipper_address',
                    'receiver_name',
                    'receiver_phone',
                    'receiver_address',
                    'items',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_user_cannot_view_nonexistent_order()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/orders/99999');

        $response->assertStatus(404);
    }

    public function test_user_can_delete_confirmed_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'confirmed'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson('/api/v1/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id
        ]);
    }

    public function test_user_cannot_delete_non_confirmed_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson('/api/v1/orders/' . $order->id);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Order tidak dapat dihapus karena statusnya pending'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id
        ]);
    }
} 