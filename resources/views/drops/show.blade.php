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
            <p class="drop-products__empty">Aucun produit associé pour le moment.</p>
        @else
            <div class="drop-products__grid">
                @foreach($drop->products as $product)
                    <a href="{{ route('products.show', $product) }}" class="drop-product-card">
                        <div class="drop-product-card__image">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        </div>
                        <div class="drop-product-card__info">
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
    max-width:1000px;
    margin:0 auto;
}

.drop-detail__header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    flex-wrap:wrap;
    gap:1.5rem;
}

.drop-detail__header h1{
    font-size:1.8rem;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.02em;
}

.drop-detail__date{
    margin-top:.5rem;
    color:var(--text);
    opacity:.7;
    font-size:.9rem;
}

.drop-detail__info{
    margin-top:.3rem;
    font-size:.85rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.6;
}

.drop-detail__description{
    margin-top:1.5rem;
    line-height:1.8;
    opacity:.85;
    max-width:600px;
}

.drop-request-button{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:.9rem 1.6rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    font-size:.85rem;
    cursor:pointer;
    white-space:nowrap;
}

.drop-request-button:hover{
    opacity:.9;
}

.drop-request-success{
    border:1px solid #2ecc71;
    color:#2ecc71;
    padding:.8rem 1.2rem;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    white-space:nowrap;
}

.drop-products{
    margin-top:3rem;
    padding-top:2rem;
    border-top:1px solid var(--border);
}

.drop-products h2{
    font-size:1.1rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:1.5rem;
}

.drop-products__empty{
    opacity:.5;
    font-size:.9rem;
}

.drop-products__grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
    gap:1.5rem;
}

.drop-product-card{
    color:var(--text);
    text-decoration:none;
    display:block;
}

.drop-product-card__image{
    aspect-ratio:3/4;
    background:var(--border);
    overflow:hidden;
}

.drop-product-card__image img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.3s;
}

.drop-product-card:hover .drop-product-card__image img{
    transform:scale(1.05);
}

.drop-product-card__info h3{
    margin-top:.8rem;
    font-size:.85rem;
    font-weight:600;
    text-transform:uppercase;
}

.drop-product-card__info p{
    margin-top:.2rem;
    font-size:.8rem;
    opacity:.7;
}

@media(max-width:600px){
    .drop-detail__header{
        flex-direction:column;
    }
}
</style>
@endsection