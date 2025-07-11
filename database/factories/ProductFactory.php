<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'ten' => $this->faker->randomElement([
                'iPhone 14 Pro',
                'Samsung Galaxy S23',
                'Dell XPS 13',
                'Sony WH-1000XM5',
                'Canon EOS R5',
                'Apple Watch Series 8',
                'Xiaomi Redmi Note 12',
                'MacBook Air M2',
            ]),
            
            'so_luong' => $this->faker->numberBetween(0, 100), // Số lượng từ 0 đến 100
            'mo_ta' => $this->faker->paragraph(2), // Mô tả 2 câu
            'hinh_anh' => $this->faker->optional(0.8, null)->imageUrl(640, 480, 'products'), // 80% có ảnh
            'danh_muc_id' => Category::factory(), // Tạo hoặc lấy ID danh mục
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
