<?php

namespace Database\Seeders;

use App\Models\DigitalPlatform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DigitalPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            ['name' => 'Manta', 'website_url' => 'https://manta.net'],
            ['name' => 'Kakao Webtoon', 'website_url' => 'https://kakaowebtoon.com'],
            ['name' => 'KakaoPage', 'website_url' => 'https://page.kakao.com'],
            ['name' => 'WEBTOON', 'website_url' => 'https://www.webtoons.com'],
            ['name' => 'Tapas', 'website_url' => 'https://tapas.io'],
            ['name' => 'Tappytoon', 'website_url' => 'https://www.tappytoon.com'],
            ['name' => 'Lezhin', 'website_url' => 'https://www.lezhinus.com'],
            ['name' => 'Other', 'website_url' => null],
        ];

        foreach ($platforms as $platform) {
            DigitalPlatform::updateOrCreate(['name' => $platform['name']], $platform);
        }
    }
}
