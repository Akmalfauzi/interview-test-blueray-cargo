<?php

namespace Tests\Unit\Services\V1\API\Tracking;

use App\Models\Order;
use App\Models\Tracking;
use App\Models\User;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use App\Services\V1\API\Tracking\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class TrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrackingService $trackingService;
    private BiteshipService|MockObject $biteshipService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->biteshipService = $this->createMock(BiteshipService::class);
        $this->trackingService = new TrackingService($this->biteshipService);
        $this->user = User::factory()->create();
    }

    public function test_get_tracking_info_successful()
    {
        // Authenticate user
        Auth::login($this->user);

        // Create test order with tracking_id in courier
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'raw_biteship_payload' => [
                'courier' => [
                    'tracking_id' => 'FoCLoWMfrFQUPNnMbWeSA1Hf'
                ]
            ]
        ]);

        // Mock Biteship service response (realistic)
        $biteshipResponse = new JsonResponse([
            'success' => true,
            'message' => 'Data tracking berhasil diambil',
            'data' => [
                'success' => true,
                'message' => 'Success to get tracking data',
                'object' => 'tracking',
                'id' => 'FoCLoWMfrFQUPNnMbWeSA1Hf',
                'waybill_id' => 'WYB-1747365552829',
                'courier' => [
                    'company' => 'jne',
                    'name' => 'John Doe',
                    'phone' => '08123456789',
                    'driver_name' => 'John Doe',
                    'driver_phone' => '08123456789'
                ],
                'destination' => [
                    'contact_name' => 'Fauzi',
                    'address' => 'Mustika Jaya, Bekasi, Jawa Barat. 17157'
                ],
                'history' => [
                    [
                        'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747365552829',
                        'service_type' => 'reg',
                        'status' => 'confirmed',
                        'updated_at' => '2025-05-16T10:19:12+07:00'
                    ],
                    [
                        'note' => 'Courier is allocated and ready to pick up',
                        'service_type' => 'reg',
                        'status' => 'allocated',
                        'updated_at' => '2025-05-16T10:29:57+07:00'
                    ]
                ],
                'link' => 'https://track.biteship.com/FoCLoWMfrFQUPNnMbWeSA1Hf?environment=development',
                'order_id' => '6826aeb0f4648c0012ede626',
                'origin' => [
                    'contact_name' => 'Akmal',
                    'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40112'
                ],
                'status' => 'allocated'
            ]
        ]);

        $this->biteshipService
            ->expects($this->once())
            ->method('getTracking')
            ->with('FoCLoWMfrFQUPNnMbWeSA1Hf')
            ->willReturn($biteshipResponse);

        // Get tracking info
        $trackingInfo = $this->trackingService->getTrackingInfo('FoCLoWMfrFQUPNnMbWeSA1Hf');

        // Assert tracking info was returned
        $this->assertIsArray($trackingInfo);
        $this->assertEquals('FoCLoWMfrFQUPNnMbWeSA1Hf', $trackingInfo['id']);
        $this->assertEquals('allocated', $trackingInfo['status']);
        $this->assertEquals('jne', $trackingInfo['courier']['company']);
        $this->assertEquals('John Doe', $trackingInfo['courier']['name']);
        $this->assertEquals('08123456789', $trackingInfo['courier']['phone']);
        $this->assertEquals('Fauzi', $trackingInfo['destination']['contact_name']);
        $this->assertEquals('Mustika Jaya, Bekasi, Jawa Barat. 17157', $trackingInfo['destination']['address']);
        $this->assertCount(2, $trackingInfo['history']);
        $this->assertEquals('confirmed', $trackingInfo['history'][0]['status']);
        $this->assertEquals('allocated', $trackingInfo['history'][1]['status']);

        // Assert tracking was saved to database
        $this->assertDatabaseHas('trackings', [
            'order_id' => $order->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_get_tracking_info_failed()
    {
        // Authenticate user
        Auth::login($this->user);

        // Mock Biteship service error response
        $biteshipResponse = new JsonResponse([
            'success' => false,
            'message' => 'Gagal mengambil data tracking',
            'data' => [
                'success' => false,
                'message' => 'Gagal mengambil data tracking'
            ]
        ], 404);

        $this->biteshipService
            ->expects($this->once())
            ->method('getTracking')
            ->with('INVALID123')
            ->willReturn($biteshipResponse);

        // Assert exception is thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gagal mengambil data tracking');

        // Get tracking info
        $this->trackingService->getTrackingInfo('INVALID123');
    }

    public function test_save_tracking()
    {
        // Authenticate user
        Auth::login($this->user);

        // Create test order with tracking_id in courier
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'raw_biteship_payload' => [
                'courier' => [
                    'tracking_id' => 'FoCLoWMfrFQUPNnMbWeSA1Hf'
                ]
            ]
        ]);

        // Test tracking data (realistic)
        $trackingData = [
            'success' => true,
            'message' => 'Success to get tracking data',
            'object' => 'tracking',
            'id' => 'FoCLoWMfrFQUPNnMbWeSA1Hf',
            'waybill_id' => 'WYB-1747365552829',
            'courier' => [
                'company' => 'jne',
                'name' => 'John Doe',
                'phone' => '08123456789',
                'driver_name' => 'John Doe',
                'driver_phone' => '08123456789'
            ],
            'destination' => [
                'contact_name' => 'Fauzi',
                'address' => 'Mustika Jaya, Bekasi, Jawa Barat. 17157'
            ],
            'history' => [
                [
                    'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747365552829',
                    'service_type' => 'reg',
                    'status' => 'confirmed',
                    'updated_at' => '2025-05-16T10:19:12+07:00'
                ],
                [
                    'note' => 'Courier is allocated and ready to pick up',
                    'service_type' => 'reg',
                    'status' => 'allocated',
                    'updated_at' => '2025-05-16T10:29:57+07:00'
                ]
            ],
            'link' => 'https://track.biteship.com/FoCLoWMfrFQUPNnMbWeSA1Hf?environment=development',
            'order_id' => '6826aeb0f4648c0012ede626',
            'origin' => [
                'contact_name' => 'Akmal',
                'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40112'
            ],
            'status' => 'allocated'
        ];

        // Save tracking
        $tracking = $this->trackingService->saveTracking($trackingData);

        // Assert tracking was saved
        $this->assertInstanceOf(Tracking::class, $tracking);
        $this->assertEquals($order->id, $tracking->order_id);
        $this->assertEquals($this->user->id, $tracking->user_id);
        $this->assertEquals($trackingData, $tracking->raw_biteship_payload);
    }
} 