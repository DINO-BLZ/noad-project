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

            <a href="{{ route('products.show', $product->slug) }}" class="product-card">
                <div class="product-card__image">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    @if($totalStock <= 0)
                        <span class="product-card__badge">Épuisé</span>
                    @elseif($activeDrop)
                        <span class="product-card__badge product-card__badge--drop">Drop</span>
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
@endsection