@extends('layouts.app')

@section('content')
<div class="product">

    <div class="product__image">
       <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
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
@endsection