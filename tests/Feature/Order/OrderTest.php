<?php

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use Mockery;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $biteshipService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        // Mock BiteshipService
        $this->biteshipService = Mockery::mock(BiteshipService::class);
        $this->app->instance(BiteshipService::class, $this->biteshipService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
            ],
            'notes' => 'Test notes'
        ];

        // Mock successful Biteship API response
        $biteshipResponse = [
            'success' => true,
            'message' => 'Order berhasil dibuat di Biteship',
            'data' => [
                'id' => 'biteship-123',
                'tracking_id' => 'TRK123',
                'status' => 'confirmed'
            ]
        ];

        $this->biteshipService
            ->shouldReceive('createOrder')
            ->once()
            ->with(Mockery::on(function ($data) use ($orderData) {
                return $data['sender_name'] === $orderData['sender_name'] &&
                       $data['receiver_name'] === $orderData['receiver_name'];
            }))
            ->andReturn(response()->json($biteshipResponse));

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

        // Assert order was saved to database
        $this->assertDatabaseHas('orders', [
            'shipper_name' => $orderData['sender_name'],
            'shipper_phone' => $orderData['sender_phone'],
            'shipper_address' => $orderData['sender_address'],
            'receiver_name' => $orderData['receiver_name'],
            'receiver_phone' => $orderData['receiver_phone'],
            'receiver_address' => $orderData['receiver_address'],
            'user_id' => $this->user->id,
            'status' => 'confirmed'
        ]);

        // Assert items were saved correctly
        $order = Order::where('shipper_name', $orderData['sender_name'])->first();
        $this->assertNotNull($order);
        $this->assertEquals($orderData['items'], $order->items);
        $this->assertEquals($orderData['notes'], $order->notes);
    }

    public function test_user_cannot_create_order_when_biteship_fails()
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
            ],
            'notes' => 'Test notes'
        ];

        // Mock failed Biteship API response
        $this->biteshipService
            ->shouldReceive('createOrder')
            ->once()
            ->andReturn(response()->json([
                'success' => false,
                'message' => 'Gagal membuat order di Biteship',
                'errors' => ['Invalid address']
            ], 400));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Gagal membuat order di Biteship'
            ]);

        // Assert order was not saved to database
        $this->assertDatabaseMissing('orders', [
            'shipper_name' => $orderData['sender_name']
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