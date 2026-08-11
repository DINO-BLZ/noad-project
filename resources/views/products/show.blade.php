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

        <form action="{{ route('cart.add', $product) }}" method="POST" class="product__form">
            @csrf

            <div class="product__sizes">
                @foreach($product->variants as $variant)
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

<style>
.product{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:4rem;
    padding:3rem;
}

.product__image{
    aspect-ratio:3/4;
    background:var(--border);
    overflow:hidden;
}

.product__image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.product__category{
    font-size:.8rem;
    opacity:.6;
    text-transform:uppercase;
    letter-spacing:.08em;
}

.product__name{
    font-size:1.8rem;
    font-weight:900;
    text-transform:uppercase;
    margin:.5rem 0;
}

.product__price{
    font-size:1.1rem;
    opacity:.8;
    margin-bottom:1.5rem;
}

.product__description{
    line-height:1.6;
    opacity:.8;
    margin-bottom:2rem;
}

.product__sizes{
    display:flex;
    gap:.8rem;
    margin-bottom:2rem;
    flex-wrap:wrap;
}

.size-option{
    position:relative;
}

.size-option input{
    position:absolute;
    opacity:0;
}

.size-option span{
    display:flex;
    align-items:center;
    justify-content:center;
    width:48px;
    height:48px;
    border:1px solid var(--border);
    font-size:.8rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.size-option input:checked + span{
    background:var(--text);
    color:var(--bg);
}

.size-option.is-disabled span{
    opacity:.3;
    text-decoration:line-through;
    cursor:not-allowed;
}

.product__cta{
    width:100%;
    padding:1rem;
    background:var(--accent);
    color:#fff;
    border:none;
    font-weight:700;
    letter-spacing:.05em;
    text-transform:uppercase;
    cursor:pointer;
    transition:.2s;
}

.product__cta:hover{
    opacity:.9;
}

@media(max-width:768px){
    .product{
        grid-template-columns:1fr;
        gap:2rem;
    }
}
</style>
@endsection