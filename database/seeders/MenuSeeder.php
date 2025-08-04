<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $makananUtama = Category::where('slug', 'makanan-utama')->first();
        $minuman = Category::where('slug', 'minuman')->first();
        $snack = Category::where('slug', 'snack-cemilan')->first();
        $dessert = Category::where('slug', 'dessert')->first();
        $paket = Category::where('slug', 'paket-hemat')->first();

        $menus = [
            // Makanan Utama
            [
                'category_id' => $makananUtama->id,
                'name' => 'Nasi Gudeg Jogja',
                'description' => 'Nasi gudeg khas Jogja dengan ayam, telur, dan sambal krecek',
                'price' => 25000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $makananUtama->id,
                'name' => 'Ayam Bakar Madu',
                'description' => 'Ayam bakar dengan bumbu madu spesial, disajikan dengan nasi dan lalapan',
                'price' => 30000,
                'is_available' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $makananUtama->id,
                'name' => 'Soto Ayam Lamongan',
                'description' => 'Soto ayam khas Lamongan dengan kuah bening dan rempah pilihan',
                'price' => 20000,
                'is_available' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $makananUtama->id,
                'name' => 'Gado-Gado Jakarta',
                'description' => 'Gado-gado dengan sayuran segar dan bumbu kacang khas Jakarta',
                'price' => 18000,
                'is_available' => true,
                'sort_order' => 4,
            ],

            // Minuman
            [
                'category_id' => $minuman->id,
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'price' => 5000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Es Jeruk',
                'description' => 'Es jeruk segar dengan jeruk asli',
                'price' => 8000,
                'is_available' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Kopi Tubruk',
                'description' => 'Kopi tubruk tradisional dengan gula aren',
                'price' => 7000,
                'is_available' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Jus Alpukat',
                'description' => 'Jus alpukat segar dengan susu kental manis',
                'price' => 12000,
                'is_available' => true,
                'sort_order' => 4,
            ],

            // Snack & Cemilan
            [
                'category_id' => $snack->id,
                'name' => 'Kerupuk Udang',
                'description' => 'Kerupuk udang renyah',
                'price' => 3000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $snack->id,
                'name' => 'Tempe Mendoan',
                'description' => 'Tempe mendoan crispy dengan cabai rawit',
                'price' => 8000,
                'is_available' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $snack->id,
                'name' => 'Pisang Goreng',
                'description' => 'Pisang goreng dengan tepung crispy',
                'price' => 6000,
                'is_available' => true,
                'sort_order' => 3,
            ],

            // Dessert
            [
                'category_id' => $dessert->id,
                'name' => 'Es Cendol',
                'description' => 'Es cendol dengan santan dan gula merah',
                'price' => 10000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $dessert->id,
                'name' => 'Klepon',
                'description' => 'Klepon dengan gula merah dan kelapa parut',
                'price' => 8000,
                'is_available' => true,
                'sort_order' => 2,
            ],

            // Paket Hemat
            [
                'category_id' => $paket->id,
                'name' => 'Paket Hemat A',
                'description' => 'Nasi + Ayam Bakar + Es Teh + Kerupuk',
                'price' => 35000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $paket->id,
                'name' => 'Paket Hemat B',
                'description' => 'Soto Ayam + Nasi + Es Jeruk + Kerupuk',
                'price' => 25000,
                'is_available' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                [
                    'category_id' => $menu['category_id'],
                    'name' => $menu['name']
                ],
                $menu
            );
        }

        $this->command->info('Menus created successfully!');
    }
}
