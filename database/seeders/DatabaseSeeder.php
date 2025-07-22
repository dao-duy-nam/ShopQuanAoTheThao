<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
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
        Product::factory(30)->create();

        $this->call([
            CategorySeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            DanhGiaSeeder::class,
            PaymentSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            VariantSeeder::class,
            OrderSeeder::class,
            BannerSeeder::class,
            DiscountCodeSeeder::class,
            PhiShipSeeder::class,
        ]);
    }
}
