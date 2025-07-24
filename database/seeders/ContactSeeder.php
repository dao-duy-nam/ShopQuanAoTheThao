<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $statuses = ['chua_xu_ly', 'da_phan_hoi'];
        $types = ['gop_y', 'khieu_nai', 'hop_tac', 'ho_tro'];
        for ($i = 1; $i <= 30; $i++) {
            Contact::create([
                'name' => "Khách $i",
                'email' => "khach$i@example.com",
                'phone' => '09' . rand(10000000, 99999999),
                'subject' => "Chủ đề $i",
                'message' => "Nội dung tin nhắn $i - " . Str::random(20),
                'type' => $types[array_rand($types)],
                'status' => $statuses[array_rand($statuses)],
                'replied_at' => rand(0, 1) ? Carbon::now()->subDays(rand(0, 5)) : null,
                'attachment' => rand(0, 1) ? 'contacts/demo.png' : null,
            ]);
    }
        }
    }

