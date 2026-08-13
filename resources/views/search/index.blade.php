@extends('layouts.app')

@section('content')
<div class="shop">

    <div class="shop__search-heading">
        @if($query !== '')
            <p>Résultats pour « {{ $query }} »</p>
        @else
            <p>Tape un mot-clé pour lancer une recherche.</p>
        @endif
    </div>

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
            @if($query !== '')
                <p class="shop__empty">Aucun produit ne correspond à « {{ $query }} ».</p>
            @endif
        @endforelse
    </div>

    {{ $products->links() }}

</div>
@endsection