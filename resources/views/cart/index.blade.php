@extends('layouts.app')

@section('content')
<div class="cart">

    <h1 class="cart__title">Panier</h1>

    @if(session('success'))
        <p class="cart__notice">{{ session('success') }}</p>
    @endif

    <div class="cart__items">
        @forelse($items as $item)
            <div class="cart__item" data-variant-id="{{ $item['variant']->id }}" data-unit-price="{{ $item['variant']->product->price }}">
                <div class="cart__item-image">
                    <img src="{{ $item['variant']->product->image ? asset('storage/' . $item['variant']->product->image) : asset('images/placeholder.png') }}" alt="{{ $item['variant']->product->name }}">
                </div>

                <div class="cart__item-info">
                    <h3>{{ $item['variant']->product->name }}</h3>
                    <p>Taille : {{ $item['variant']->size }}</p>
                    <p>{{ number_format($item['variant']->product->price, 0) }} DA</p>
                </div>

                <div class="cart__qty">
                    <input type="number" class="cart__qty-input" value="{{ $item['quantity'] }}" min="1">
                </div>

                <p class="cart__subtotal">{{ number_format($item['subtotal'], 0) }} DA</p>

                <button type="button" class="cart__remove">Retirer</button>
            </div>
        @empty
            <p class="cart__empty">Ton panier est vide.</p>
        @endforelse
    </div>

    @if(count($items) > 0)
        <div class="cart__total">
            <span>Total</span>
            <span class="cart__total-amount">{{ number_format($total, 0) }} DA</span>
        </div>

        <a href="{{ route('checkout.index') }}" class="checkout__link">Passer commande</a>
    @endif

</div>
@endsection