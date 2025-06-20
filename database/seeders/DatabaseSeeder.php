<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Product::factory(10)->create();

        $this->call([
            CategorySeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            DanhGiaSeeder::class,
            PaymentSeeder::class,
            OrderSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            VariantSeeder::class,
        ]);
    }
}
