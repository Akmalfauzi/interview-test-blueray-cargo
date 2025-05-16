<?php

namespace Tests\Unit\Services\V1\API\Order;

use App\Models\Order;
use App\Models\User;
use App\Services\V1\API\Order\OrderService;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private BiteshipService|MockObject $biteshipService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->biteshipService = $this->createMock(BiteshipService::class);
        $this->orderService = new OrderService($this->biteshipService);
        $this->user = User::factory()->create();
    }

    public function test_create_order_successful()
    {
        // Authenticate user
        Auth::login($this->user);

        // Mock Biteship service response
        $biteshipResponse = new JsonResponse([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'id' => 'test-order-id',
                'status' => 'confirmed'
            ]
        ]);

        $this->biteshipService
            ->expects($this->once())
            ->method('createOrder')
            ->willReturn($biteshipResponse);

        // Test order data
        $orderData = [
            'sender_name' => 'John Doe',
            'sender_phone' => '081234567890',
            'sender_address' => 'Jl. Test No. 123',
            'receiver_name' => 'Jane Doe',
            'receiver_phone' => '089876543210',
            'receiver_address' => 'Jl. Test No. 456',
            'items' => [
                [
                    'name' => 'Test Item',
                    'quantity' => 1,
                    'price' => 100000
                ]
            ],
            'notes' => 'Test notes'
        ];

        // Create order
        $order = $this->orderService->createOrder($orderData);

        // Assert order was created
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('John Doe', $order->shipper_name);
        $this->assertEquals('081234567890', $order->shipper_phone);
        $this->assertEquals('Jl. Test No. 123', $order->shipper_address);
        $this->assertEquals('Jane Doe', $order->receiver_name);
        $this->assertEquals('089876543210', $order->receiver_phone);
        $this->assertEquals('Jl. Test No. 456', $order->receiver_address);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('Test notes', $order->notes);
        $this->assertEquals($this->user->id, $order->user_id);
    }

    public function test_create_order_failed()
    {
        // Authenticate user
        Auth::login($this->user);

        // Mock Biteship service error response
        $biteshipResponse = new JsonResponse([
            'success' => false,
            'message' => 'Failed to create order'
        ], 400);

        $this->biteshipService
            ->expects($this->once())
            ->method('createOrder')
            ->willReturn($biteshipResponse);

        // Test order data
        $orderData = [
            'sender_name' => 'John Doe',
            'sender_phone' => '081234567890',
            'sender_address' => 'Jl. Test No. 123',
            'receiver_name' => 'Jane Doe',
            'receiver_phone' => '089876543210',
            'receiver_address' => 'Jl. Test No. 456',
            'items' => [
                [
                    'name' => 'Test Item',
                    'quantity' => 1,
                    'price' => 100000
                ]
            ],
            'notes' => 'Test notes'
        ];

        // Assert exception is thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create order');

        // Create order
        $this->orderService->createOrder($orderData);
    }

    public function test_get_couriers()
    {
        // Mock Biteship service response
        $biteshipResponse = new JsonResponse([
            'success' => true,
            'data' => [
                'couriers' => [
                    ['name' => 'JNE', 'code' => 'jne'],
                    ['name' => 'SiCepat', 'code' => 'sicepat']
                ]
            ]
        ]);

        $this->biteshipService
            ->expects($this->once())
            ->method('getCouriers')
            ->willReturn($biteshipResponse);

        // Get couriers
        $couriers = $this->orderService->getCouriers();

        // Assert couriers were returned
        $this->assertIsArray($couriers);
        $this->assertCount(2, $couriers);
        $this->assertEquals('JNE', $couriers[0]['name']);
        $this->assertEquals('SiCepat', $couriers[1]['name']);
    }

    public function test_get_map_location()
    {
        // Mock Biteship service response
        $biteshipResponse = new JsonResponse([
            'success' => true,
            'data' => [
                'areas' => [
                    ['id' => '1', 'name' => 'Jakarta'],
                    ['id' => '2', 'name' => 'Bandung']
                ]
            ]
        ]);

        $this->biteshipService
            ->expects($this->once())
            ->method('getMapLocation')
            ->willReturn($biteshipResponse);

        // Test location data
        $locationData = [
            'input' => 'Jakarta',
            'type' => 'single'
        ];

        // Get map location
        $locations = $this->orderService->getMapLocation($locationData);

        // Assert locations were returned
        $this->assertIsArray($locations);
        $this->assertCount(2, $locations);
        $this->assertEquals('Jakarta', $locations[0]['name']);
        $this->assertEquals('Bandung', $locations[1]['name']);
    }
} 