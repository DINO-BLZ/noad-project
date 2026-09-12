@extends('layouts.app')

@section('content')
<div class="shop-page">
    <div class="shop-header">
        <div class="shop-breadcrumbs">
            <a href="{{ route('home') }}">ACCUEIL</a> / <span>BOUTIQUE</span>
        </div>
        <div class="shop-title-row">
            <div>
                <h1 class="shop-title">BOUTIQUE</h1>
                <p class="shop-subtitle">Pièces intemporelles et drops exclusifs. Le vestiaire NOAD pour ceux qui restent fidèles à leurs principes.</p>
            </div>
            <div class="shop-brand-badge">
                <span class="badge-title">NOAD</span>
                <span class="badge-slogan">DEFEND YOUR PRINCIPLE</span>
            </div>
        </div>
    </div>

    <div class="shop-layout">
        <aside class="shop-filters">
            <div class="filter-group">
                <h3 class="filter-heading">CATÉGORIE</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}"
                           class="filter-link {{ ! request('category') ? 'active' : '' }}">TOUTES</a>
                    </li>
                    @foreach($categories as $category)
                        <li class="filter-item">
                            <a href="{{ request()->fullUrlWithQuery(['category' => $category->slug]) }}"
                               class="filter-link {{ request('category') === $category->slug ? 'active' : '' }}">
                                {{ $category->name }}
                            </a>
                            <span class="filter-count">({{ $category->products_count }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-group">
                <h3 class="filter-heading">TAILLE</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="{{ request()->fullUrlWithQuery(['size' => null]) }}"
                           class="filter-link {{ ! request('size') ? 'active' : '' }}">TOUTES</a>
                    </li>
                    @foreach($sizes as $size)
                        <li class="filter-item">
                            <a href="{{ request()->fullUrlWithQuery(['size' => $size]) }}"
                               class="filter-link {{ request('size') === $size ? 'active' : '' }}">{{ $size }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-group">
                <h3 class="filter-heading">COULEUR</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="{{ request()->fullUrlWithQuery(['color' => null]) }}"
                           class="filter-link {{ ! request('color') ? 'active' : '' }}">TOUTES</a>
                    </li>
                    @foreach($colors as $color)
                        <li class="filter-item">
                            <a href="{{ request()->fullUrlWithQuery(['color' => $color]) }}"
                               class="filter-link {{ request('color') === $color ? 'active' : '' }}">{{ $color }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-group">
                <h3 class="filter-heading">PRIX</h3>
                <ul class="filter-list">
                    <li class="filter-item">
                        <a href="{{ request()->fullUrlWithQuery(['price_min' => null, 'price_max' => null]) }}"
                           class="filter-link {{ ! request('price_min') && ! request('price_max') ? 'active' : '' }}">TOUS</a>
                    </li>
                    @foreach($priceBuckets as $bucket)
                        <li class="filter-item">
                            <a href="{{ request()->fullUrlWithQuery(['price_min' => $bucket['min'], 'price_max' => $bucket['max']]) }}"
                               class="filter-link {{ (int) request('price_min') === (int) $bucket['min'] && (int) request('price_max') === (int) $bucket['max'] ? 'active' : '' }}">
                                {{ $bucket['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <main class="shop-catalog">
            <div class="catalog-toolbar">
                <span class="catalog-count">{{ $products->total() }} PIÈCE(S) DISPONIBLE(S)</span>
                <div class="catalog-sort">
                    <span>TRIER PAR :</span>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                       class="sort-link {{ ! request('sort') || request('sort') === 'newest' ? 'active' : '' }}">NOUVEAUTÉS</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}"
                       class="sort-link {{ request('sort') === 'price_asc' ? 'active' : '' }}">PRIX CROISSANT</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}"
                       class="sort-link {{ request('sort') === 'price_desc' ? 'active' : '' }}">PRIX DÉCROISSANT</a>
                </div>
            </div>

            <div class="products-grid">
                @forelse($products as $product)
                    <article class="product-card">
                        <a href="{{ route('products.show', $product) }}" class="product-media-link">
                            <div class="product-media">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <div class="product-placeholder">NOAD</div>
                                @endif
                            </div>
                        </a>
                        <div class="product-info">
                            <div class="product-meta">
                                <span class="product-tag">{{ $product->category->name ?? 'VESTIAIRE' }}</span>
                            </div>
                            <h2 class="product-name">
                                <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                            </h2>
                            <div class="product-footer">
                                <span class="product-price">{{ number_format($product->price, 0, ',', ' ') }} DA</span>
                                <a href="{{ route('products.show', $product) }}" class="product-action-btn">+ DÉTAILS</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-catalog">
                        <p class="empty-title">AUCUNE PIÈCE DISPONIBLE</p>
                        <p class="empty-desc">Aucun produit ne correspond à cette sélection de filtres.</p>
                        <a href="{{ route('shop.index') }}" class="btn-reset">RÉINITIALISER LES FILTRES</a>
                    </div>
                @endforelse
            </div>

            <div class="shop-pagination">
                {{ $products->links() }}
            </div>
        </main>
    </div>
</div>

<style>
.shop-page { width: 100%; max-width: 1400px; margin: 0 auto; padding: 32px 24px 64px 24px; box-sizing: border-box; color: var(--text, #e5e5e5); }
.shop-page a { color: inherit; text-decoration: none; }
.shop-header { border-bottom: 1px solid var(--border, #242424); padding-bottom: 24px; margin-bottom: 32px; }
.shop-breadcrumbs { font-size: 11px; font-family: monospace; letter-spacing: 0.15em; color: #777; margin-bottom: 16px; text-transform: uppercase; }
.shop-breadcrumbs a:hover { color: var(--text, #fff); }
.shop-breadcrumbs span { color: var(--text, #fff); }
.shop-title-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap; }
.shop-title { font-size: clamp(32px, 5vw, 48px); font-weight: 900; letter-spacing: 0.04em; margin: 0 0 8px 0; text-transform: uppercase; line-height: 1; }
.shop-subtitle { font-size: 13px; color: #888; margin: 0; max-width: 600px; line-height: 1.5; }
.shop-brand-badge { border: 1px solid var(--border, #242424); padding: 10px 16px; background-color: #121212; text-align: right; }
.badge-title { display: block; font-weight: 900; font-size: 14px; letter-spacing: 0.2em; color: #fff; }
.badge-slogan { display: block; font-size: 10px; font-family: monospace; letter-spacing: 0.15em; color: var(--accent, #d32f2f); }
.shop-layout { display: grid; grid-template-columns: 240px 1fr; gap: 40px; }
@media (max-width: 900px) { .shop-layout { grid-template-columns: 1fr; } }
.shop-filters { border-right: 1px solid var(--border, #242424); padding-right: 24px; }
@media (max-width: 900px) { .shop-filters { border-right: none; border-bottom: 1px solid var(--border, #242424); padding-right: 0; padding-bottom: 24px; } }
.filter-group { margin-bottom: 32px; }
.filter-heading { font-size: 12px; font-family: monospace; letter-spacing: 0.15em; color: #aaa; margin: 0 0 16px 0; text-transform: uppercase; }
.filter-list { list-style: none; margin: 0; padding: 0; }
.filter-item { display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 10px; text-transform: uppercase; }
.filter-link { color: #777; }
.filter-link.active, .filter-link:hover { color: var(--text, #fff); }
.filter-count { font-family: monospace; font-size: 11px; color: #666; }
.catalog-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-bottom: 16px; margin-bottom: 24px; border-bottom: 1px solid var(--border, #242424); font-family: monospace; font-size: 11px; color: #777; letter-spacing: 0.1em; }
.catalog-sort { display: flex; gap: 12px; align-items: center; }
.sort-link { color: #777; }
.sort-link.active, .sort-link:hover { color: var(--text, #fff); }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
.product-card { background-color: #121212; border: 1px solid var(--border, #242424); display: flex; flex-direction: column; transition: border-color 0.2s ease; }
.product-card:hover { border-color: #555; }
.product-media { position: relative; aspect-ratio: 4 / 5; background-color: #181818; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.product-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.product-placeholder { font-family: monospace; font-size: 12px; color: #555; letter-spacing: 0.2em; }
.product-info { padding: 16px; display: flex; flex-direction: column; flex: 1; justify-content: space-between; }
.product-meta { font-family: monospace; font-size: 10px; color: #666; margin-bottom: 6px; text-transform: uppercase; }
.product-name { font-size: 16px; font-weight: 800; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.3; }
.product-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #1a1a1a; }
.product-price { font-family: monospace; font-size: 14px; font-weight: 800; color: var(--text, #fff); }
.product-action-btn { border: 1px solid var(--border, #242424); padding: 6px 12px; font-family: monospace; font-size: 11px; color: #aaa; }
.product-card:hover .product-action-btn { border-color: var(--text, #fff); color: #000; background-color: var(--text, #fff); }
.empty-catalog { grid-column: 1 / -1; padding: 64px 24px; text-align: center; border: 1px dashed var(--border, #242424); }
.empty-title { font-size: 18px; font-weight: 900; letter-spacing: 0.1em; margin: 0 0 8px 0; }
.empty-desc { font-family: monospace; font-size: 12px; color: #777; margin: 0 0 24px 0; }
.btn-reset { display: inline-block; border: 1px solid var(--text, #fff); color: #000; background-color: var(--text, #fff); padding: 10px 20px; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; }
.shop-pagination { margin-top: 48px; display: flex; justify-content: center; }
</style>
@endsection