$ErrorActionPreference = "Stop"
Write-Host "-> Suppression de l ancienne migration dupliquee..."
$old = "database/migrations/2026_08_12_071748_add_is_primary_to_product_images_table.php"
if (Test-Path $old) {
  try { git rm -f $old } catch { Remove-Item $old -Force }
}

Write-Host "-> Ecriture de database/migrations/2026_08_18_112623_add_primary_image_id_to_products_table.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'database\migrations�6_08_18_112623_add_primary_image_id_to_products_table.php') | Out-Null
@'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('primary_image_id')
                ->nullable()
                ->after('image')
                ->constrained('product_images')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['primary_image_id']);
            $table->dropColumn('primary_image_id');
        });
    }
};
'@ | Set-Content -Path 'database\migrations�6_08_18_112623_add_primary_image_id_to_products_table.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de database/migrations/2026_08_18_112624_drop_is_primary_from_product_images_table.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'database\migrations�6_08_18_112624_drop_is_primary_from_product_images_table.php') | Out-Null
@'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('position');
        });
    }
};
'@ | Set-Content -Path 'database\migrations�6_08_18_112624_drop_is_primary_from_product_images_table.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de app/Models/Product.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'app\Models\Product.php') | Out-Null
@'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'image', 'primary_image_id'];

    /**
     * Les champs envoyés à Elasticsearch pour ce produit.
     * Seuls "name" et "description" sont utilisés pour la recherche pour l'instant.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function drops()
    {
        return $this->belongsToMany(Drop::class);
    }

    public function activeDrop()
    {
        if ($this->relationLoaded('drops')) {
            return $this->drops->first(fn ($drop) => $drop->isActive());
        }

        return $this->drops()->active()->first();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage()
    {
        return $this->belongsTo(ProductImage::class, 'primary_image_id');
    }
 public function upcomingDrop()
{
    if ($this->relationLoaded('drops')) {
        return $this->drops->first(fn ($drop) => $drop->isUpcoming());
    }

    return $this->drops()->upcoming()->first();
}
}'@ | Set-Content -Path 'app\Models\Product.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de app/Models/ProductImage.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'app\Models\ProductImage.php') | Out-Null
@'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'position'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::deleting(function (ProductImage $image) {
            if ($image->path) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}
'@ | Set-Content -Path 'app\Models\ProductImage.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de app/Http/Controllers/Admin/ProductController.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'app\Http\Controllers\Admin\ProductController.php') | Out-Null
@'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\OrderItem;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'required|image|max:4096',
            'sizes' => 'required|array|min:1',
            'sizes.*.stock' => 'required|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $data['image'] = $request->file('image')->store('products', 'public');

        $sizes = $data['sizes'];
        unset($data['sizes']);

        $product = Product::create($data);

        foreach ($sizes as $size => $sizeData) {
            $product->variants()->create([
                'size' => $size,
                'stock' => $sizeData['stock'],
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('variants');

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.size' => 'required_with:variants|string|max:50',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.sku' => 'nullable|string',
            'variants.*.color' => 'nullable|string|max:50',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'primary_image' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($request, $data, $product) {
            $product->update($data);

            $existing = $product->variants()->get()->keyBy('id');
            $kept = [];

            foreach ($request->input('variants', []) as $v) {
                if (isset($v['id']) && $v['id'] && $existing->has($v['id'])) {
                    $variant = $existing->get($v['id']);

                    if (!empty($v['sku']) && $v['sku'] !== $variant->sku && \App\Models\Variant::where('sku', $v['sku'])->exists()) {
                        throw ValidationException::withMessages(['variants' => ["SKU {$v['sku']} déjà utilisé."]]);
                    }

                    $variant->update([
                        'size' => $v['size'],
                        'stock' => $v['stock'] ?? 0,
                        'sku' => $v['sku'] ?? $variant->sku,
                        'color' => $v['color'] ?? $variant->color,
                    ]);
                    $kept[] = $variant->id;
                } else {
                    if (empty($v['size'])) continue;

                    if (!empty($v['sku']) && \App\Models\Variant::where('sku', $v['sku'])->exists()) {
                        throw ValidationException::withMessages(['variants' => ["SKU {$v['sku']} déjà utilisé."]]);
                    }

                    $new = $product->variants()->create([
                        'size' => $v['size'],
                        'stock' => $v['stock'] ?? 0,
                        'sku' => $v['sku'] ?? null,
                        'color' => $v['color'] ?? null,
                    ]);
                    $kept[] = $new->id;
                }
            }

            $toDelete = $existing->keys()->diff($kept);
            foreach ($toDelete as $variantId) {
                $hasOrders = OrderItem::where('variant_id', $variantId)->exists();
                if ($hasOrders) continue;
                Variant::find($variantId)?->delete();
            }

            $newPrimaryImage = null;

            if ($request->hasFile('images')) {
                $maxPos = $product->images()->max('position');
                $position = is_null($maxPos) ? 0 : $maxPos + 1;
                foreach ($request->file('images') as $index => $img) {
                    $path = $img->store('products', 'public');
                    $created = $product->images()->create([
                        'path' => $path,
                        'position' => $position++,
                    ]);

                    if ($request->input('primary_image') === 'new_'.$index) {
                        $newPrimaryImage = $created;
                    }
                }
            }

            foreach ($request->input('remove_images', []) as $imgId) {
                $img = $product->images()->find($imgId);
                if ($img) {
                    if ($img->path) {
                        Storage::disk('public')->delete($img->path);
                    }
                    $img->delete();
                }
            }

            if ($primary = $request->input('primary_image')) {
                if ($newPrimaryImage) {
                    $product->update(['primary_image_id' => $newPrimaryImage->id]);
                } elseif (!str_starts_with($primary, 'new_')) {
                    $img = $product->images()->find($primary);
                    if ($img) {
                        $product->update(['primary_image_id' => $img->id]);
                    }
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé.');
    }
}'@ | Set-Content -Path 'app\Http\Controllers\Admin\ProductController.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de resources/views/admin/products/edit.blade.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'resourcesiewsdmin\productsdit.blade.php') | Out-Null
@'
@extends('layouts.admin')

@section('content')
<div class="admin-form-page">

    <h1>Modifier le produit</h1>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf
        @method('PUT')

        <label>Nom du produit
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
        </label>

        <label>Catégorie
            <select name="category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>Prix (DA)
            <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" required>
        </label>

        <label>Description
            <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>
        </label>

        <label>Photo de couverture actuelle</label>
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:120px;">

        <label>Remplacer la photo de couverture (optionnel)
            <input type="file" name="image" accept="image/*">
        </label>

        <label>Galerie photo</label>
        <div class="gallery-grid">
            @foreach($product->images as $image)
                <div class="gallery-item">
                    <img src="{{ asset('storage/' . $image->path) }}" alt="">
                    <label class="gallery-item__primary">
                        <input type="radio" name="primary_image" value="{{ $image->id }}" {{ $product->primary_image_id == $image->id ? 'checked' : '' }}>
                        Principale
                    </label>
                    <label class="gallery-item__remove">
                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                        Supprimer
                    </label>
                </div>
            @endforeach
        </div>

        <label>Ajouter des photos à la galerie (plusieurs possibles)
            <input type="file" name="images[]" accept="image/*" multiple>
        </label>

        <label>Tailles disponibles et stock</label>
        <div id="sizes-list">
            @foreach($product->variants as $index => $variant)
                <div class="size-row">
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                    <label>Taille
                        <input type="text" name="variants[{{ $index }}][size]" value="{{ $variant->size }}" required>
                    </label>
                    <label>Stock
                        <input type="number" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" min="0" required>
                    </label>
                    <label>Couleur (optionnel)
                        <input type="text" name="variants[{{ $index }}][color]" value="{{ $variant->color }}">
                    </label>
                    <label>SKU (optionnel)
                        <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}">
                    </label>
                    <button type="button" class="size-row__remove">Retirer</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-size-row" class="admin-btn-secondary">+ Ajouter une taille</button>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Mettre à jour</button>
    </form>

</div>

<style>
.admin-form-page{
    padding:3rem;
    max-width:600px;
    margin:0 auto;
}

.admin-form-page h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:2rem;
}

.admin-form{
    display:flex;
    flex-direction:column;
    gap:1.2rem;
}

.admin-form label{
    display:flex;
    flex-direction:column;
    gap:.4rem;
    font-size:.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.8;
}

.admin-form input,
.admin-form select,
.admin-form textarea{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.8rem;
    font-family:inherit;
    font-size:.9rem;
}

.admin-form select option{
    background:var(--bg);
    color:var(--text);
}

.gallery-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));
    gap:1rem;
}

.gallery-item{
    border:1px solid var(--border);
    padding:.6rem;
    display:flex;
    flex-direction:column;
    gap:.4rem;
    align-items:center;
}

.gallery-item img{
    width:100%;
    height:90px;
    object-fit:cover;
    border-radius:4px;
}

.gallery-item__primary,
.gallery-item__remove{
    flex-direction:row !important;
    align-items:center;
    gap:.4rem !important;
    font-size:.65rem !important;
    text-transform:none !important;
}

.gallery-item__primary input,
.gallery-item__remove input{
    width:auto;
    padding:0;
}

.size-row{
    display:grid;
    grid-template-columns:1fr 1fr 1fr 1fr auto;
    gap:.8rem;
    align-items:end;
    border:1px solid var(--border);
    padding:1rem;
    margin-bottom:.8rem;
}

.size-row label{
    margin:0;
}

.size-row__remove{
    background:transparent;
    border:1px solid var(--accent);
    color:var(--accent);
    font-size:.7rem;
    text-transform:uppercase;
    padding:.6rem;
    cursor:pointer;
    height:fit-content;
}

.admin-btn-secondary{
    background:transparent;
    border:1px dashed var(--border);
    color:var(--text);
    padding:.8rem;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
}

.admin-form__errors{
    background:rgba(176,46,38,.1);
    border:1px solid var(--accent);
    color:var(--accent);
    padding:.8rem 1rem;
    font-size:.8rem;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:1rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let sizeIndex = {{ $product->variants->count() }};
    const list = document.getElementById('sizes-list');

    document.querySelectorAll('.size-row__remove').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.size-row').remove());
    });

    document.getElementById('add-size-row').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'size-row';
        row.innerHTML = `
            <label>Taille
                <input type="text" name="variants[${sizeIndex}][size]" placeholder="S, M, L, 42..." required>
            </label>
            <label>Stock
                <input type="number" name="variants[${sizeIndex}][stock]" min="0" value="1" required>
            </label>
            <label>Couleur (optionnel)
                <input type="text" name="variants[${sizeIndex}][color]">
            </label>
            <label>SKU (optionnel)
                <input type="text" name="variants[${sizeIndex}][sku]">
            </label>
            <button type="button" class="size-row__remove">Retirer</button>
        `;
        row.querySelector('.size-row__remove').addEventListener('click', () => row.remove());
        list.appendChild(row);
        sizeIndex++;
    });
});
</script>
@endsection'@ | Set-Content -Path 'resourcesiewsdmin\productsdit.blade.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de resources/views/products/show.blade.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'resourcesiews\products\show.blade.php') | Out-Null
@'
@extends('layouts.app')

@section('content')
<div class="product">

    <div class="product__gallery">
        <div class="product__image">
           <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" id="product-main-image">
        </div>

        @if($product->images->count() > 0)
            <div class="product__thumbnails">
                <img src="{{ asset('storage/' . $product->image) }}" alt="" class="product__thumbnail is-active" data-full="{{ asset('storage/' . $product->image) }}">
                @foreach($product->images->sortByDesc(fn ($image) => $image->id == $product->primary_image_id)->sortBy('position') as $image)
                    <img src="{{ asset('storage/' . $image->path) }}" alt="" class="product__thumbnail" data-full="{{ asset('storage/' . $image->path) }}">
                @endforeach
            </div>
        @endif
    </div>

    <div class="product__info">
        <p class="product__category">{{ $product->category->name }}</p>
        <h1 class="product__name">{{ $product->name }}</h1>
        <p class="product__price">{{ number_format($product->price, 0) }} DA</p>

        <p class="product__description">{{ $product->description }}</p>

        @php
            $activeDrop = $product->activeDrop();

            $sizeOrder = ['XS' => 0, 'S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5];

            $sortedVariants = $product->variants->sortBy(function ($variant) use ($sizeOrder) {
                if (isset($sizeOrder[$variant->size])) {
                    return $sizeOrder[$variant->size];
                }
                // Pointures ou toute taille numérique : tri par valeur numérique
                if (is_numeric($variant->size)) {
                    return 100 + (float) $variant->size;
                }
                // Fallback : ordre alphabétique après tout le reste
                return 1000;
            })->values();
        @endphp

        @if($activeDrop)
            <div class="drop-banner">
                <p><strong>Drop actif :</strong> {{ $activeDrop->name }}</p>
                @if(auth()->check() && auth()->user()->isWhitelistedForDrop($activeDrop))
                    <p class="drop-banner__status">Vous êtes whitelisté pour ce drop.</p>
                @else
                    <p class="drop-banner__status">Accès privé. Demandez à être whitelisté pour acheter.</p>
                @endif
            </div>
        @endif

        <form action="{{ route('cart.add', $product) }}" method="POST" class="product__form" id="add-to-cart-form">
            @csrf

            <div class="product__sizes">
                @foreach($sortedVariants as $variant)
                    <label class="size-option {{ $variant->stock <= 0 ? 'is-disabled' : '' }}">
                        <input type="radio"
                               name="variant_id"
                               value="{{ $variant->id }}"
                               {{ $variant->stock <= 0 ? 'disabled' : '' }}
                               required>
                        <span>{{ $variant->size }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="product__cta">Ajouter au panier</button>
        </form>

        @if($activeDrop && auth()->guest())
            <p class="drop-note">Veuillez vous connecter pour demander une whitelist et acheter ce drop.</p>
        @endif

        @if($activeDrop && auth()->check() && !auth()->user()->isWhitelistedForDrop($activeDrop))
            <form action="{{ route('drops.request-whitelist', $activeDrop) }}" method="POST" class="drop-request-form">
                @csrf
                <button type="submit" class="drop-request-button">Demander la whitelist</button>
            </form>
        @endif
    </div>

</div>
@endsection'@ | Set-Content -Path 'resourcesiews\products\show.blade.php' -Encoding UTF8 -NoNewline

Write-Host "-> Ecriture de tests/Feature/ProductImageDeletionTest.php..."
New-Item -ItemType Directory -Force -Path (Split-Path 'tests\Feature\ProductImageDeletionTest.php') | Out-Null
@'
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;

class ProductImageDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_product_image_removes_physical_file()
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'name' => 'Hat',
            'slug' => 'hat-test',
            'price' => 10,
            'category_id' => $category->id,
        ]);

        $path = 'products/photo.jpg';
        Storage::disk('public')->put($path, 'contents');

        $img = ProductImage::create([
            'product_id' => $product->id,
            'path' => $path,
            'position' => 0,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $img->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }
}
'@ | Set-Content -Path 'tests\Feature\ProductImageDeletionTest.php' -Encoding UTF8 -NoNewline

Write-Host ""
Write-Host "Termine. Verifie avec: git status"
