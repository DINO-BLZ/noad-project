<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CreateProductAction
{
    public function execute(
        array $data,
        UploadedFile $image
    ): Product {
        $imagePath = null;

        try {
            $imagePath = $image->store(
                'products',
                'public'
            );

            return DB::transaction(function () use (
                $data,
                $imagePath
            ) {
                $product = Product::create([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'slug' => $this->generateUniqueSlug(
                        $data['name']
                    ),
                    'description' => $data['description'] ?? null,
                    'price' => $data['price'],
                    'image' => $imagePath,
                ]);

                foreach ($data['sizes'] as $size => $sizeData) {
                    $product->variants()->create([
                        'size' => $size,
                        'stock' => $sizeData['stock'],
                    ]);
                }

                return $product->load([
                    'category',
                    'variants',
                ]);
            });
        } catch (Throwable $e) {

            if ($imagePath) {
                Storage::disk('public')->delete(
                    $imagePath
                );
            }

            throw $e;
        }
    }

    private function generateUniqueSlug(
        string $name
    ): string {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            Product::where('slug', $slug)->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}