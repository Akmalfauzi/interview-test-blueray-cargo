<?php

namespace Tests\Feature\Tracking;

use App\Models\Order;
use App\Models\Tracking;
use App\Models\User;
use App\Services\V1\API\ThirdParty\Biteship\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WebTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $biteshipService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure database is ready
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
            
            // Create tables if they don't exist
            if (!Schema::hasTable('users')) {
                Schema::create('users', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('email')->unique();
                    $table->timestamp('email_verified_at')->nullable();
                    $table->string('password');
                    $table->rememberToken();
                    $table->timestamps();
                });
            }
            
            if (!Schema::hasTable('orders')) {
                Schema::create('orders', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->string('shipper_name');
                    $table->string('shipper_phone');
                    $table->text('shipper_address');
                    $table->string('receiver_name');
                    $table->string('receiver_phone');
                    $table->text('receiver_address');
                    $table->longText('items');
                    $table->longText('raw_biteship_payload')->nullable();
                    $table->string('status')->default('pending');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            }
            
            if (!Schema::hasTable('trackings')) {
                Schema::create('trackings', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->unsignedBigInteger('order_id');
                    $table->longText('raw_biteship_payload')->nullable();
                    $table->timestamps();
                });
            }
        }
        
        $this->user = User::factory()->create();
        $this->biteshipService = Mockery::mock(BiteshipService::class);
        $this->app->instance(BiteshipService::class, $this->biteshipService);
    }

    protected function tearDown(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }
        
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_can_view_tracking_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tracking.index'));

        $response->assertStatus(200)
            ->assertViewIs('backend.v1.tracking.index');
    }

    public function test_user_can_view_tracking_history_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tracking.history'));

        $response->assertStatus(200)
            ->assertViewIs('backend.v1.tracking.history');
    }

    public function test_user_can_search_tracking()
    {
        $trackingData = [
            'success' => true,
            'message' => 'Success to get tracking data',
            'object' => 'tracking',
            'id' => 'b2j9XaoS0TqFs5wKcEK0rWHz',
            'waybill_id' => 'WYB-1747369846934',
            'courier' => [
                'company' => 'jne',
                'name' => null,
                'phone' => null,
                'driver_name' => null,
                'driver_phone' => null
            ],
            'destination' => [
                'contact_name' => 'Fauz',
                'address' => 'Menteng, Jakarta Pusat, DKI Jakarta. 10330'
            ],
            'history' => [
                [
                    'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747369846934',
                    'service_type' => 'reg',
                    'status' => 'confirmed',
                    'updated_at' => '2025-05-16T11:30:46+07:00'
                ]
            ],
            'link' => 'https://track.biteship.com/b2j9XaoS0TqFs5wKcEK0rWHz?environment=development',
            'order_id' => '6826bf7664d10a001240bf8a',
            'origin' => [
                'contact_name' => 'Aka',
                'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40111'
            ],
            'status' => 'confirmed'
        ];

        $this->biteshipService
            ->shouldReceive('getTracking')
            ->with('b2j9XaoS0TqFs5wKcEK0rWHz')
            ->once()
            ->andReturn(response()->json([
                'success' => true,
                'message' => 'Data tracking berhasil diambil',
                'data' => $trackingData
            ]));

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'raw_biteship_payload' => [
                'courier' => [
                    'tracking_id' => 'b2j9XaoS0TqFs5wKcEK0rWHz'
                ]
            ]
        ]);

        $tracking = Tracking::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => $trackingData
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tracking/b2j9XaoS0TqFs5wKcEK0rWHz');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data tracking berhasil diambil',
                'data' => $trackingData
            ]);
    }

    public function test_user_cannot_search_invalid_tracking()
    {
        $this->biteshipService
            ->shouldReceive('getTracking')
            ->with('INVALID123')
            ->once()
            ->andReturn(response()->json([
                'success' => false,
                'message' => 'Failed to get tracking data',
                'data' => null
            ], 400));

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tracking/INVALID123');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Gagal mengambil data tracking: Failed to get tracking data'
            ]);
    }

    public function test_user_can_view_tracking_history()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        $trackingData = [
            'success' => true,
            'message' => 'Success to get tracking data',
            'object' => 'tracking',
            'id' => 'b2j9XaoS0TqFs5wKcEK0rWHz',
            'waybill_id' => 'WYB-1747369846934',
            'courier' => [
                'company' => 'jne',
                'name' => null,
                'phone' => null,
                'driver_name' => null,
                'driver_phone' => null
            ],
            'destination' => [
                'contact_name' => 'Fauz',
                'address' => 'Menteng, Jakarta Pusat, DKI Jakarta. 10330'
            ],
            'history' => [
                [
                    'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747369846934',
                    'service_type' => 'reg',
                    'status' => 'confirmed',
                    'updated_at' => '2025-05-16T11:30:46+07:00'
                ]
            ],
            'link' => 'https://track.biteship.com/b2j9XaoS0TqFs5wKcEK0rWHz?environment=development',
            'order_id' => '6826bf7664d10a001240bf8a',
            'origin' => [
                'contact_name' => 'Aka',
                'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40111'
            ],
            'status' => 'confirmed'
        ];

        Tracking::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => $trackingData
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tracking/history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar tracking berhasil diambil'
            ])
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

        $data = $response->json('data');
        $this->assertEquals(3, $data['total']);
        $this->assertCount(3, $data['data']);
    }

    public function test_user_can_search_tracking_history()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        $trackingData = [
            'success' => true,
            'message' => 'Success to get tracking data',
            'object' => 'tracking',
            'id' => 'b2j9XaoS0TqFs5wKcEK0rWHz',
            'waybill_id' => 'WYB-1747369846934',
            'courier' => [
                'company' => 'jne',
                'name' => null,
                'phone' => null,
                'driver_name' => null,
                'driver_phone' => null
            ],
            'destination' => [
                'contact_name' => 'Fauz',
                'address' => 'Menteng, Jakarta Pusat, DKI Jakarta. 10330'
            ],
            'history' => [
                [
                    'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747369846934',
                    'service_type' => 'reg',
                    'status' => 'confirmed',
                    'updated_at' => '2025-05-16T11:30:46+07:00'
                ]
            ],
            'link' => 'https://track.biteship.com/b2j9XaoS0TqFs5wKcEK0rWHz?environment=development',
            'order_id' => '6826bf7664d10a001240bf8a',
            'origin' => [
                'contact_name' => 'Aka',
                'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40111'
            ],
            'status' => 'confirmed'
        ];

        Tracking::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => $trackingData
        ]);

        // Create another tracking with different status
        $otherTrackingData = $trackingData;
        $otherTrackingData['status'] = 'delivered';
        Tracking::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => $otherTrackingData
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tracking/history?search=confirmed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar tracking berhasil diambil'
            ])
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

        $data = $response->json('data');
        $this->assertEquals(1, $data['total']);
        $this->assertCount(1, $data['data']);
        
        // Verify the returned tracking has the correct status
        $firstTracking = $data['data'][0];
        $this->assertEquals('confirmed', $firstTracking['raw_biteship_payload']['status']);
    }

    public function test_user_can_paginate_tracking_history()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        $trackingData = [
            'success' => true,
            'message' => 'Success to get tracking data',
            'object' => 'tracking',
            'id' => 'b2j9XaoS0TqFs5wKcEK0rWHz',
            'waybill_id' => 'WYB-1747369846934',
            'courier' => [
                'company' => 'jne',
                'name' => null,
                'phone' => null,
                'driver_name' => null,
                'driver_phone' => null
            ],
            'destination' => [
                'contact_name' => 'Fauz',
                'address' => 'Menteng, Jakarta Pusat, DKI Jakarta. 10330'
            ],
            'history' => [
                [
                    'note' => 'Courier order is confirmed. jne has been notified to pick up. Pickup Number: WYB-1747369846934',
                    'service_type' => 'reg',
                    'status' => 'confirmed',
                    'updated_at' => '2025-05-16T11:30:46+07:00'
                ]
            ],
            'link' => 'https://track.biteship.com/b2j9XaoS0TqFs5wKcEK0rWHz?environment=development',
            'order_id' => '6826bf7664d10a001240bf8a',
            'origin' => [
                'contact_name' => 'Aka',
                'address' => 'Sumur Bandung, Bandung, Jawa Barat. 40111'
            ],
            'status' => 'confirmed'
        ];

        Tracking::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'raw_biteship_payload' => $trackingData
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tracking/history?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar tracking berhasil diambil'
            ])
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

        $data = $response->json('data');
        $this->assertEquals(2, $data['current_page']);
        $this->assertEquals(10, $data['per_page']);
        $this->assertEquals(15, $data['total']);
        $this->assertCount(5, $data['data']); // 15 total - 10 per page = 5 on second page
    }
} 