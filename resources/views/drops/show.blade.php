@extends('layouts.app')

@section('content')
<div class="drop-detail">
    <div class="drop-detail__header">
        <div>
            <h1>{{ $drop->name }}</h1>
            <p class="drop-detail__date">Lancement : {{ $drop->start_date->format('d M Y H:i') }}</p>
            <p class="drop-detail__info">Statut : {{ ucfirst($drop->status) }}</p>
        </div>
        @if($drop->status === 'active' && auth()->check() && !$isWhitelisted)
            <form action="{{ route('drops.request-whitelist', $drop) }}" method="POST">
                @csrf
                <button type="submit" class="drop-request-button">Demander la whitelist</button>
            </form>
        @elseif($drop->status === 'active' && auth()->check() && $isWhitelisted)
            <div class="drop-request-success">Vous êtes whitelisté pour ce drop.</div>
        @elseif($drop->status === 'active' && auth()->guest())
            <div class="drop-request-success">Connectez-vous pour demander la whitelist.</div>
        @endif
    </div>

    <div class="drop-detail__description">{{ $drop->description }}</div>

    <div class="drop-products">
        <h2>Produits du drop</h2>
        @if($drop->products->isEmpty())
            <p>Aucun produit associé pour le moment.</p>
        @else
            <div class="drop-products__grid">
                @foreach($drop->products as $product)
                    <a href="{{ route('products.show', $product) }}" class="drop-product-card">
                        <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}">
                        <div>
                            <h3>{{ $product->name }}</h3>
                            <p>{{ number_format($product->price, 0) }} DA</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
.drop-detail{
    padding:3rem;
    max-width:800px;
    margin:0 auto;
}
.drop-detail__date{
    margin-top:.5rem;
    color:rgba(255,255,255,.7);
}
.drop-detail__description{
    margin-top:1.5rem;
    line-height:1.8;
    opacity:.85;
}
</style>
@endsection
