<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $products = Product::query()
            ->with([
                'category',
                'variants',
            ])
            ->when($status === 'available', fn ($query) => $query->whereHas(
                'variants',
                fn ($variants) => $variants->where('stock', '>', 0)
            ))
            ->when($status === 'out_of_stock', fn ($query) => $query->whereDoesntHave(
                'variants',
                fn ($variants) => $variants->where('stock', '>', 0)
            ))
            ->when($request->filled('category_id'), fn ($query) => $query->where(
                'category_id',
                $request->integer('category_id')
            ))
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $activeReferences = Product::count();

        $outOfStock = Product::query()
            ->whereDoesntHave('variants', fn ($query) => $query->where('stock', '>', 0))
            ->count();

        $variantStock = DB::table('variants')
            ->select('product_id', DB::raw('SUM(stock) as total_stock'))
            ->groupBy('product_id');

        $stockValue = (float) DB::table('products')
            ->leftJoinSub($variantStock, 'variant_stock', function ($join) {
                $join->on('products.id', '=', 'variant_stock.product_id');
            })
            ->selectRaw('COALESCE(SUM(products.price * COALESCE(variant_stock.total_stock, 0)), 0) as stock_value')
            ->value('stock_value');

        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', compact(
            'products',
            'categories',
            'activeReferences',
            'outOfStock',
            'stockValue',
            'status'
        ));
    }

    public function create()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProductAction $createProduct
    ) {
        $this->authorize('create', Product::class);

        $product = $createProduct->execute(
            $request->validated(),
            $request->file('image')
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                "Le produit {$product->name} a été créé."
            );
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $product->load(['variants', 'images']);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $updateProduct
    ) {
        $this->authorize('update', $product);

        $product = $updateProduct->execute(
            $product,
            $request->validated(),
            $request->file('image'),
            $request->file('images', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                "Le produit {$product->name} a été mis à jour."
            );
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Produit supprimé.'
            );
    }
}
