<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            ['ten' => 'Kích cỡ'],
            ['ten' => 'Màu sắc'],
            
        ];

        foreach ($attributes as $item) {
            Attribute::create($item);
        }
    }
}
