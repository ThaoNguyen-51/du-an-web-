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
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'note' => null,
            'payment_method' => 'cod',
            'subtotal' => 1000000,
            'shipping_fee' => 30000,
            'total_amount' => 1030000,
            'ghn_total_fee' => 30000,
            'to_district_id' => 1,
            'to_ward_code' => '1',
            'shipping_status' => 'not_shipped',
            'ghn_order_code' => null,
            'status' => Order::STATUS_PENDING_CONFIRMATION,
        ];
    }
}
