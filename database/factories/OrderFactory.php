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
            'user_id' => User::inRandomOrder()->first()->id ?? 1,
            'so_tien_thanh_toan' => $this->faker->numberBetween(100000, 1000000),
            'trang_thai_don_hang' => 'da_giao',
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
