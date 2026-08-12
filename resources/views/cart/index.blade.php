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


@endsection