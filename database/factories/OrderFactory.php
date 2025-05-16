<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shipper_name' => $this->faker->name,
            'shipper_phone' => $this->faker->phoneNumber,
            'shipper_address' => $this->faker->address,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'receiver_address' => $this->faker->address,
            'items' => json_encode([
                [
                    'name' => $this->faker->word,
                    'description' => $this->faker->sentence,
                    'value' => $this->faker->numberBetween(100000, 1000000),
                    'length' => $this->faker->numberBetween(10, 100),
                    'width' => $this->faker->numberBetween(10, 100),
                    'height' => $this->faker->numberBetween(10, 100),
                    'weight' => $this->faker->numberBetween(1, 10),
                    'quantity' => $this->faker->numberBetween(1, 5)
                ]
            ]),
            'raw_biteship_payload' => json_encode([
                'courier' => [
                    'tracking_id' => 'b2j9XaoS0TqFs5wKcEK0rWHz'
                ]
            ]),
            'status' => 'pending',
            'notes' => $this->faker->sentence
        ];
    }

    public function confirmed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
                'raw_biteship_payload' => array_merge($attributes['raw_biteship_payload'], [
                    'status' => 'confirmed'
                ])
            ];
        });
    }

    public function inTransit(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_transit',
                'raw_biteship_payload' => array_merge($attributes['raw_biteship_payload'], [
                    'status' => 'in_transit'
                ])
            ];
        });
    }

    public function delivered(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'delivered',
                'raw_biteship_payload' => array_merge($attributes['raw_biteship_payload'], [
                    'status' => 'delivered'
                ])
            ];
        });
    }
} 