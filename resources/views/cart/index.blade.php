@extends('layouts.app')

@section('content')
<div class="cart">

    <h1 class="cart__title">Panier</h1>

    @if(session('success'))
        <p class="cart__notice">{{ session('success') }}</p>
    @endif

    @forelse($items as $item)
        <div class="cart__item">
            <div class="cart__item-info">
                <h3>{{ $item['variant']->product->name }}</h3>
                <p>Taille : {{ $item['variant']->size }}</p>
                <p>{{ number_format($item['variant']->product->price, 0) }} DA</p>
            </div>

            <form action="{{ route('cart.update', $item['variant']->id) }}" method="POST" class="cart__qty">
                @csrf
                @method('PATCH')
                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1">
                <button type="submit">Mettre à jour</button>
            </form>

            <p class="cart__subtotal">{{ number_format($item['subtotal'], 0) }} DA</p>

            <form action="{{ route('cart.remove', $item['variant']->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="cart__remove">Retirer</button>
            </form>
        </div>
    @empty
        <p class="cart__empty">Ton panier est vide.</p>
    @endforelse

    @if(count($items) > 0)
        <div class="cart__total">
            <span>Total</span>
            <span>{{ number_format($total, 0) }} DA</span>
        </div>

        <a href="{{ route('checkout.index') }}" class="checkout__link">Passer commande</a>
    @endif

</div>

<style>
.cart{
    max-width:800px;
    margin:0 auto;
    padding:3rem 2rem;
}

.cart__title{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:2rem;
}

.cart__notice{
    background:var(--accent);
    color:#fff;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.cart__item{
    display:grid;
    grid-template-columns:2fr 1fr auto auto;
    align-items:center;
    gap:1.5rem;
    padding:1.2rem 0;
    border-bottom:1px solid var(--border);
}

.cart__item-info h3{
    font-size:.95rem;
    text-transform:uppercase;
}

.cart__item-info p{
    font-size:.8rem;
    opacity:.7;
}

.cart__qty{
    display:flex;
    gap:.5rem;
}

.cart__qty input{
    width:50px;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.3rem;
    text-align:center;
}

.cart__qty button,
.cart__remove{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    font-size:.75rem;
    text-transform:uppercase;
    padding:.4rem .8rem;
    cursor:pointer;
    transition:.2s;
}

.cart__qty button:hover,
.cart__remove:hover{
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}

.cart__subtotal{
    font-weight:700;
}

.cart__empty{
    opacity:.6;
    text-align:center;
    padding:3rem 0;
}

.cart__total{
    display:flex;
    justify-content:space-between;
    font-size:1.2rem;
    font-weight:900;
    text-transform:uppercase;
    margin-top:2rem;
    padding-top:1.5rem;
    border-top:2px solid var(--text);
}

.checkout__link{
    display:block;
    text-align:center;
    margin-top:1.5rem;
    background:var(--accent);
    color:#fff;
    padding:1rem;
    text-decoration:none;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
}
</style>
@endsection