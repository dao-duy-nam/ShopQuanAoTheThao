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
            //
            'ten' => $this->faker->randomElement([
                'Điện thoại',
                'Laptop',
                'Tai nghe',
                'Máy ảnh',
                'Đồng hồ thông minh',
                'Máy tính bảng',
                'Phụ kiện',
            ]),
            'mo_ta' => $this->faker->sentence(10), // Description with 10 words
            'image' => $this->faker->optional(0.8, null)->imageUrl(640, 480, 'categories'),
        ];
    }
}
