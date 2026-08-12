@extends('layouts.app')

@section('content')
<div class="shop">

    <nav class="shop__filters">
        <a href="{{ route('shop.index') }}"
           class="{{ !request('category') ? 'is-active' : '' }}">Tout</a>

        @foreach($categories as $category)
            <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
               class="{{ request('category') === $category->slug ? 'is-active' : '' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </nav>

    <div class="shop__grid">
        @forelse($products as $product)
            @php
                $totalStock = $product->variants->sum('stock');
                $activeDrop = $product->activeDrop();
            @endphp

            <a href="{{ route('products.show', $product) }}" class="product-card">
                <div class="product-card__image">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    @if($totalStock <= 0)
                        <span class="product-card__badge">Épuisé</span>
                    @elseif($activeDrop)
                        <span class="product-card__badge product-card__badge--drop">
                            Drop : {{ \Illuminate\Support\Str::limit($activeDrop->name, 18) }}
                        </span>
                    @endif
                </div>
                <h3 class="product-card__name">{{ $product->name }}</h3>
                <p class="product-card__price">{{ number_format($product->price, 0) }} DA</p>
            </a>
        @empty
            @for ($i = 0; $i < 8; $i++)
                <div class="product-card product-card--empty">
                    <div class="product-card__image">
                        <span class="product-card__soon">Bientôt</span>
                    </div>
                    <h3 class="product-card__name">—</h3>
                    <p class="product-card__price">—</p>
                </div>
            @endfor
        @endforelse
    </div>

    <div class="shop__pagination">
        {{ $products->withQueryString()->links() }}
    </div>

</div>

<style>
.shop{
    padding:3rem;
}

.shop__filters{
    display:flex;
    gap:2rem;
    justify-content:center;
    margin-bottom:2.5rem;
}

.shop__filters a{
    color:var(--text);
    text-decoration:none;
    font-size:.85rem;
    font-weight:600;
    letter-spacing:.08em;
    text-transform:uppercase;
    opacity:.5;
    transition:.3s;
}

.shop__filters a:hover,
.shop__filters a.is-active{
    opacity:1;
    color:var(--accent);
}

.shop__grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
    gap:2rem;
}

.product-card{
    color:var(--text);
    text-decoration:none;
}

.product-card__image{
    position:relative;
    aspect-ratio:3/4;
    background:var(--border);
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
}

.product-card__image img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.4s;
}

.product-card:hover .product-card__image img{
    transform:scale(1.05);
}

.product-card__badge{
    position:absolute;
    top:.8rem;
    left:.8rem;
    background:var(--accent);
    color:#fff;
    font-size:.7rem;
    font-weight:700;
    letter-spacing:.05em;
    text-transform:uppercase;
    padding:.3rem .7rem;
}

.product-card__badge--drop{
    background:#2563eb;
}

.product-card__name{
    margin-top:1rem;
    font-size:.9rem;
    font-weight:600;
    text-transform:uppercase;
}

.product-card__price{
    margin-top:.3rem;
    font-size:.85rem;
    opacity:.7;
}

/* CASES VIDES */

.product-card--empty{
    cursor:default;
}

.product-card--empty .product-card__image{
    border:1px dashed var(--border);
    background:transparent;
}

.product-card__soon{
    font-size:.75rem;
    font-weight:600;
    letter-spacing:.1em;
    text-transform:uppercase;
    opacity:.25;
}

.product-card--empty .product-card__name,
.product-card--empty .product-card__price{
    opacity:.25;
}

.shop__pagination{
    margin-top:3rem;
    display:flex;
    justify-content:center;
}
</style>
@endsection