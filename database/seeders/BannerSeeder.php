<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Beauty of Joseon',
                'image_url' => 'https://www.simpleskincare.in/cdn/shop/files/01_phone_homepage_banners-1.jpg?v=1742468585',
                'link' => 'https://example.com/welcome',
                'status' => true,
            ],
            [
                'title' => 'Fresh-faced glow',
                'image_url' => 'https://cdn11.bigcommerce.com/s-7p5jn6i1wf/images/stencil/original/image-manager/smart-actives-main-banner.jpg?t=1770029583',
                'link' => 'https://example.com/offers',
                'status' => true,
            ],
            [
                'title' => 'Kopher up to 60%',
                'image_url' => 'https://meaningfulbeauty.com/cdn/shop/files/Special_Offer_Nav_Body_1024x1024.jpg?v=1748963386',
                'link' => 'https://example.com/menu',
                'status' => true,
            ],
        ];
        // $banners = [
        //     [
        //         'title' => 'Welcome to Our Coffee Shop',
        //         'image_url' => 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        //         'link' => 'https://example.com/welcome',
        //         'status' => true,
        //     ],
        //     [
        //         'title' => 'Special Offers This Week',
        //         'image_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        //         'link' => 'https://example.com/offers',
        //         'status' => true,
        //     ],
        //     [
        //         'title' => 'New Menu Items',
        //         'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        //         'link' => 'https://example.com/menu',
        //         'status' => true,
        //     ],
        // ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
