<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Noad Tee — Edition 01',
            'slug' => 'noad-tee-edition-01',
            'description' => 'T-shirt Noad, coupe droite, coton lourd 240g.',
            'price' => 3500,
            'image' => 'placeholder.jpg',
        ]);

        $product->variants()->createMany([
            ['size' => 'S', 'stock' => 10],
            ['size' => 'M', 'stock' => 15],
            ['size' => 'L', 'stock' => 8],
            ['size' => 'XL', 'stock' => 0],
        ]);
    }
}