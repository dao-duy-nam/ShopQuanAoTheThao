<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        $localImages = [
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
            'Luu=fl.jpg',
        ];

        $externalImages = [
            'https://i.pinimg.com/736x/f5/e8/c5/f5e8c550976838663d719335378ea0f6.jpg',
            'https://i.pinimg.com/1200x/2a/15/35/2a1535c830520a4a840f7ac09a525d11.jpg',
            'https://i.pinimg.com/1200x/0c/3d/d9/0c3dd9d9f67f1bf5443aea26d35a9c10.jpg',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $title = $faker->sentence(6);
            $shortDesc = $faker->paragraph();
            $paragraphs = $faker->paragraphs(rand(8, 12));

            $content = '';
            $imageSrc = $faker->randomElement($externalImages);
            $content .= "<p><img src=\"{$imageSrc}\" alt=\"Ảnh minh họa\" style=\"width:300px;height:200px;object-fit:cover;display:block;margin:15px auto;\"></p>";
            foreach ($paragraphs as $para) {
                $content .= "<p>{$para}</p>";
            }

            DB::table('bai_viet')->insert([
                'tieu_de' => $title,
                'mo_ta_ngan' => $shortDesc,
                'noi_dung' => $content,
                'anh_dai_dien' => 'posts/' . $faker->randomElement($localImages),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
