<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Madagascar Centella Light Cleansing Oil',
                'price' => 19.0,
                'image' => 'https://i.postimg.cc/4dNLy8G9/skin1004-centella-oil.webp',
                'category' => 'Cleansers',
                'description' => 'A non-comedogenic oil cleanser that melts away makeup and impurities while soothing with Centella extract.',
                'rate' => 8.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Heartleaf Pore Control Cleansing Oil',
                'price' => 18.0,
                'image' => 'https://i.postimg.cc/PfLGMxG1/anua-heartleaf-oil.webp',
                'category' => 'Cleansers',
                'description' => 'Effective for removing blackheads and sebum while maintaining moisture with Heartleaf extract.',
                'rate' => 8.0,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Deep Cleansing Oil',
                'price' => 28.0,
                'image' => 'https://i.postimg.cc/jqn97FNF/dhc-deep-oil.webp',
                'category' => 'Cleansers',
                'description' => 'An olive oil-based classic that emulsifies into a milk to dissolve heavy makeup instantly.',
                'rate' => 9.0,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Perfect Whip (Blue)',
                'price' => 8.0,
                'image' => 'https://i.postimg.cc/cJjh1dDL/senka-perfect-whip.webp',
                'category' => 'Cleansers',
                'description' => 'Creates a super-dense foam that cleanses pores deeply without leaving the skin tight.',
                'rate' => 7.6,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Madagascar Centella Toning Toner',
                'price' => 17.0,
                'image' => 'https://i.postimg.cc/6p4yJwG0/skin1004-toning-toner.webp',
                'category' => 'Toners',
                'description' => 'A mild PHA exfoliating toner that hydrates and brightens with Niacinamide and Centella.',
                'rate' => 8.7,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Heartleaf 77% Soothing Toner',
                'price' => 21.0,
                'image' => 'https://i.postimg.cc/6529Tc5T/anua-77-toner.webp',
                'category' => 'Toners',
                'description' => 'Specifically formulated to soothe irritation and balance the skin\'s pH level.',
                'rate' => 9.0,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Gokujyun Premium Lotion',
                'price' => 16.0,
                'image' => 'https://i.postimg.cc/zvL1bDN6/hadalabo-premium.webp',
                'category' => 'Toners',
                'description' => 'Features 7 types of hyaluronic acid to provide intense, essence-like hydration.',
                'rate' => 8.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Rice Toner',
                'price' => 28.0,
                'image' => 'https://i.postimg.cc/Pq2GSvr3/imfrom-rice-toner.webp',
                'category' => 'Toners',
                'description' => 'A bilayer toner made with 77.78% rice extract to brighten and firm the skin.',
                'rate' => 9.2,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Madagascar Centella Ampoule',
                'price' => 18.0,
                'image' => 'https://i.postimg.cc/c1VJR2zC/skin1004-ampoule.webp',
                'category' => 'Serums',
                'description' => '100% pure Centella Asiatica extract to soothe and replenish the skin barrier.',
                'rate' => 8.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Dive-In Hyaluronic Acid Serum',
                'price' => 23.0,
                'image' => 'https://i.postimg.cc/KYqGRHLh/torriden-serum.webp',
                'category' => 'Serums',
                'description' => 'Low-molecular HA that absorbs instantly for deep plumping and hydration.',
                'rate' => 8.8,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Vitamin C Brightening Essence',
                'price' => 14.0,
                'image' => 'https://i.postimg.cc/xTD0JHkC/melano-cc.webp',
                'category' => 'Serums',
                'description' => 'Pure Vitamin C in an air-tight tube to fade dark spots and acne scarring.',
                'rate' => 8.3,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Advanced Snail 96 Mucin Power Essence',
                'price' => 25.0,
                'image' => 'https://i.postimg.cc/Kz324vxB/cosrx-snail-mucin.webp',
                'category' => 'Serums',
                'description' => '96% snail mucin to repair texture and provide a boost of elastic moisture.',
                'rate' => 8.9,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Madagascar Centella Soothing Cream',
                'price' => 16.0,
                'image' => 'https://i.postimg.cc/wTQd1r8W/skin1004-soothing-cream.webp',
                'category' => 'Moisturizers',
                'description' => 'A lightweight gel-cream that provides a cooling sensation and strengthens the barrier.',
                'rate' => 8.2,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Atobarrier 365 Cream',
                'price' => 30.0,
                'image' => 'https://i.postimg.cc/B6B397gk/aestura-cream.webp',
                'category' => 'Moisturizers',
                'description' => 'High-density ceramide capsules melt into the skin for 100-hour moisture retention.',
                'rate' => 9.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'The Dewy Skin Cream',
                'price' => 72.0,
                'image' => 'https://i.postimg.cc/QxbR8W1v/tatcha-dewy.avif',
                'category' => 'Moisturizers',
                'description' => 'A rich moisturizing cream with purple rice for a luminous, healthy-looking glow.',
                'rate' => 9.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Ceramide Ato Concentrate Cream',
                'price' => 18.0,
                'image' => 'https://i.postimg.cc/L5V08rYx/illiyoon-cream.webp',
                'category' => 'Moisturizers',
                'description' => 'Fragrance-free, hypoallergenic cream that uses encapsulated ceramides for long-term repair.',
                'rate' => 8.8,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Bio-Collagen Real Deep Mask',
                'price' => 19.0,
                'image' => 'https://i.postimg.cc/PxWSSB4G/biodance-mask.webp',
                'category' => 'Masks',
                'description' => 'Overnight hydrogel mask that turns clear as the collagen penetrates the skin.',
                'rate' => 8.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Watergel Sheet Ampoule Mask',
                'price' => 3.0,
                'image' => 'https://i.postimg.cc/y8DnMJPX/skin1004-sheet-mask.webp',
                'category' => 'Masks',
                'description' => 'A sheet mask made of tencel and seaweed that holds high amounts of soothing Centella.',
                'rate' => 8.3,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Tea Tree Essential Mask',
                'price' => 2.0,
                'image' => 'https://i.postimg.cc/C5Msmd5N/mediheal-tea-tree.webp',
                'category' => 'Masks',
                'description' => 'Top-selling sheet mask in Korea for immediate breakout calming and redness reduction.',
                'rate' => 8.0,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Rice Mask',
                'price' => 15.0,
                'image' => 'https://i.postimg.cc/0yKdtTkF/keana-rice-mask.webp',
                'category' => 'Masks',
                'description' => 'Made from 100% Japanese rice to minimize pore appearance and smooth skin texture.',
                'rate' => 8.8,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Madagascar Centella Hyalu-Cica Water-Fit Sun Serum',
                'price' => 19.0,
                'image' => 'https://i.postimg.cc/5ykLg3ZF/skin1004-sun-serum.webp',
                'category' => 'Sun Protection',
                'description' => 'The internet\'s favorite sunscreen; non-greasy, no white cast, and feels like a moisturizer.',
                'rate' => 8.7,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Relief Sun: Rice + Probiotics (SPF50+)',
                'price' => 18.0,
                'image' => 'https://i.postimg.cc/9XN7vLNk/boj-sunscreen.jpg',
                'category' => 'Sun Protection',
                'description' => 'A creamy organic sunscreen that provides lightweight protection and skin-calming benefits.',
                'rate' => 8.5,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'UV Aqua Rich Watery Essence',
                'price' => 12.0,
                'image' => 'https://i.postimg.cc/Nj8LfDpj/biore-aqua-rich.avif',
                'category' => 'Sun Protection',
                'description' => 'A world-famous Japanese gel sunscreen that feels like water on the skin.',
                'rate' => 8.3,
                'is_active' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Perfect UV Sunscreen Skincare Milk',
                'price' => 32.0,
                'image' => 'https://i.postimg.cc/gjvrXKmM/anessa-milk.jpg',
                'category' => 'Sun Protection',
                'description' => 'Highly durable and water-resistant; features Auto Booster Technology to strengthen with heat.',
                'rate' => 9.0,
                'is_active' => true,
                'stock' => 100,
            ],
        ];

        $seededNames = [];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
            $seededNames[] = $product['name'];
        }

        // Remove old products that are no longer in the seeder (only if not used in any order)
        Product::whereNotIn('name', $seededNames)
            ->whereDoesntHave('orderItems')
            ->delete();

        $this->command->info('Products seeded successfully!');
    }
}
