<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ten' => $this->faker->words(3, true),
            'so_luong' => $this->faker->numberBetween(0, 100),
            'mo_ta' => $this->faker->paragraph(2),
            'hinh_anh' => 'products/' . $this->faker->randomElement([
                '5bff93b7-027c-445a-9057-aca0e651044f.jpg',
                '39d8343e-8503-4c58-8826-2d436036b0c7.jpg',
                '677bd66c-9aaa-4709-a87a-9c6d5a5ae640.jpg',
                '7f197c88-4f46-430e-bdb1-231bd11b5860.jpg',
                '324c3af8-8450-49c7-8956-ada5e7755e39.jpg',
                '811baaf5-6d80-4313-aa05-7e81070facb6.jpg',
                'ac4a56f1-52d6-4e97-82fa-431989184c60.jpg',
                'e76af41b-3bf2-4903-98d8-0dc31946163a.jpg',
                '11a7f299-5284-4a01-b103-8107150cd7ed.jpg',
                '473ebc23-28ac-4c44-bc6e-963289106996.jpg',
                'a76e7422-0422-4b40-a952-9e393bf4a545.jpg',
                'e192ebf7-c10c-451e-9a38-173ef7775330.jpg',
                'Lưu=fl.jpg',
            ]),
            'danh_muc_id' => Category::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
