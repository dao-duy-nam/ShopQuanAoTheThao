<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ten' => $this->faker->randomElement([
                'Áo thể thao nam',
                'Áo thể thao nữ',
                'Quần short nam',
                'Quần legging nữ',
                'Giày chạy bộ',
                'Giày bóng rổ',
                'Phụ kiện thể thao',
                'Túi thể thao',
                'Găng tay tập gym',
                'Đồ tập yoga',
            ]),
            'mo_ta' => $this->faker->sentence(10),
            'image' => $this->faker->optional(0.8, null)->imageUrl(640, 480, 'fashion', true, 'sportswear'),
        ];
    }
}
