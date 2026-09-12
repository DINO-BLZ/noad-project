<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')->get();

        $sizes = Variant::query()
            ->select('size')
            ->distinct()
            ->whereNotNull('size')
            ->orderBy('size')
            ->pluck('size');

        $colors = Variant::query()
            ->select('color')
            ->distinct()
            ->whereNotNull('color')
            ->orderBy('color')
            ->pluck('color');

        $priceBuckets = [
            [
                'key' => 'lt10',
                'label' => 'Moins de 10 000 DA',
                'min' => null,
                'max' => 9999,
            ],
            [
                'key' => '10-15',
                'label' => '10 000 – 15 000 DA',
                'min' => 10000,
                'max' => 15000,
            ],
            [
                'key' => '15-20',
                'label' => '15 000 – 20 000 DA',
                'min' => 15000,
                'max' => 20000,
            ],
            [
                'key' => 'gt20',
                'label' => 'Plus de 20 000 DA',
                'min' => 20001,
                'max' => null,
            ],
        ];

        $products = Product::with([
            'variants',
            'category',
            'images',
            'drops' => fn ($query) => $query->active(),
        ])
            ->when($request->category, function ($query, $categorySlug) {
                $query->whereHas(
                    'category',
                    fn ($q) => $q->where('slug', $categorySlug)
                );
            })
            ->when($request->size, function ($query, $size) {
                $query->whereHas(
                    'variants',
                    fn ($q) => $q->where('size', $size)
                );
            })
            ->when($request->color, function ($query, $color) {
                $query->whereHas(
                    'variants',
                    fn ($q) => $q->where('color', $color)
                );
            })
            ->when(
                $request->price_min,
                fn ($query, $min) => $query->where('price', '>=', $min)
            )
            ->when(
                $request->price_max,
                fn ($query, $max) => $query->where('price', '<=', $max)
            )
            ->when(
                $request->sort === 'price_asc',
                fn ($query) => $query->orderBy('price', 'asc')
            )
            ->when(
                $request->sort === 'price_desc',
                fn ($query) => $query->orderBy('price', 'desc')
            )
            ->when(
                ! $request->sort || $request->sort === 'newest',
                fn ($query) => $query->latest()
            )
            ->paginate(12)
            ->withQueryString();

        return view(
            'shop.index',
            compact(
                'products',
                'categories',
                'sizes',
                'colors',
                'priceBuckets'
            )
        );
    }

    public function show(Product $product)
    {
        $product->load([
            'variants',
            'category',
            'drops' => fn ($query) => $query->orderByDesc('start_date'),
            'images' => fn ($query) => $query->orderBy('position'),
        ]);

        /*
         * Produits similaires de la même catégorie.
         */
        $relatedProducts = Product::query()
            ->with([
                'category',
                'images',
            ])
            ->where(
                $product->getKeyName(),
                '!=',
                $product->getKey()
            )
            ->when(
                $product->category_id,
                fn ($query) =>
                    $query->where(
                        'category_id',
                        $product->category_id
                    )
            )
            ->latest()
            ->limit(4)
            ->get();

        /*
         * Si la catégorie ne contient pas suffisamment
         * de produits, on complète avec d'autres produits.
         */
        if ($relatedProducts->count() < 4) {
            $fallbackProducts = Product::query()
                ->with([
                    'category',
                    'images',
                ])
                ->where(
                    $product->getKeyName(),
                    '!=',
                    $product->getKey()
                )
                ->when(
                    $product->category_id,
                    fn ($query) =>
                        $query->where(
                            'category_id',
                            '!=',
                            $product->category_id
                        )
                )
                ->latest()
                ->limit(
                    4 - $relatedProducts->count()
                )
                ->get();

            $relatedProducts = $relatedProducts->concat(
                $fallbackProducts
            );
        }

        return view(
            'products.show',
            compact(
                'product',
                'relatedProducts'
            )
        );
    }
}