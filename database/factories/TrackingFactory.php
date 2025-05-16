<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingFactory extends Factory
{
    protected $model = Tracking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'raw_biteship_payload' => [
                'success' => true,
                'message' => 'Success to get tracking data',
                'object' => 'tracking',
                'id' => $this->faker->uuid,
                'waybill_id' => 'WYB-' . $this->faker->numberBetween(1000000000, 9999999999),
                'courier' => [
                    'company' => $this->faker->randomElement(['jne', 'sicepat', 'jnt']),
                    'name' => null,
                    'phone' => null,
                    'driver_name' => null,
                    'driver_phone' => null
                ],
                'destination' => [
                    'contact_name' => $this->faker->name,
                    'address' => $this->faker->address
                ],
                'history' => [
                    [
                        'note' => 'Courier order is confirmed.',
                        'service_type' => 'reg',
                        'status' => 'confirmed',
                        'updated_at' => now()->toIso8601String()
                    ]
                ],
                'link' => $this->faker->url,
                'order_id' => $this->faker->uuid,
                'origin' => [
                    'contact_name' => $this->faker->name,
                    'address' => $this->faker->address
                ],
                'status' => 'confirmed'
            ]
        ];
    }
} 