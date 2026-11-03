<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->info(
                'Les seeders sont désactivés en production. '
                .'Utilisez `php artisan app:create-admin {email} {name}` pour créer un administrateur.'
            );

            return;
        }

        $category = Category::firstOrCreate(
            ['slug' => 't-shirts'],
            ['name' => 'T-Shirts']
        );

        $product = Product::firstOrCreate(
            ['slug' => 'noad-tee-edition-01'],
            [
                'category_id' => $category->id,
                'name' => 'Noad Tee — Edition 01',
                'description' => 'T-shirt Noad, coupe droite, coton lourd 240g.',
                'price' => 3500,
                'image' => 'placeholder.jpg',
            ]
        );

        foreach ([
            'S' => 10,
            'M' => 15,
            'L' => 8,
            'XL' => 0,
        ] as $size => $stock) {
            $product->variants()->firstOrCreate(
                ['size' => $size, 'color' => ''],
                ['stock' => $stock]
            );
        }
    }
}
