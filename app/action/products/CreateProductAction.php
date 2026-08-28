<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateProductAction
{
    public function execute(
        array $data,
        ?UploadedFile $image = null
    ): Product {
        return DB::transaction(function () use ($data, $image) {

            $product = Product::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'price' => $data['price'],
                'description' => $data['description'] ?? null,
            ]);

            if ($image) {
                $path = $image->store(
                    'products',
                    'public'
                );

                $product->images()->create([
                    'path' => $path,
                    'is_primary' => true,
                ]);
            }

            foreach ($data['sizes'] ?? [] as $size => $variant) {
                $product->variants()->create([
                    'size' => $size,
                    'stock' => $variant['stock'],
                ]);
            }

            return $product->load([
                'category',
                'variants',
                'images',
            ]);
        });
    }
}