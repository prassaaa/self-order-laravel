<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan Utama',
                'slug' => 'makanan-utama',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Minuman',
                'slug' => 'minuman',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Snack & Cemilan',
                'slug' => 'snack-cemilan',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Dessert',
                'slug' => 'dessert',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Paket Hemat',
                'slug' => 'paket-hemat',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Categories created successfully!');
    }
}
