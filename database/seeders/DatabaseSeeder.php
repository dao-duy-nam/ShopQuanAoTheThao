<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Database\Seeder;
use Database\Seeders\TestDashboardSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CategorySeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            
            PaymentSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ProductSeeder::class,
            // OrderSeeder::class,
            BannerSeeder::class,
            DiscountCodeSeeder::class,
            PhiShipSeeder::class,
            TestDashboardSeeder::class,
            ContactSeeder::class,
            PostSeeder::class,
            DanhGiaSeeder::class,
        ]);
    
    }
}
