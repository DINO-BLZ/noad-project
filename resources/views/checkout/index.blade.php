@extends('layouts.app')

@section('content')

<div class="checkout">

<h1 class="checkout__title">Finaliser la commande</h1>

<div class="checkout__grid">

    <form action="{{ route('checkout.store') }}" method="POST" class="checkout__form">
        @csrf

        <label>
            Nom complet
            <input
                type="text"
                name="full_name"
                value="{{ old('full_name') }}"
                required
            >
        </label>

        <label>
            Téléphone
            <input
                type="text"
                name="phone"
                value="{{ old('phone') }}"
                required
            >
        </label>

        <label>
            Adresse
            <input
                type="text"
                name="address"
                value="{{ old('address') }}"
                required
            >
        </label>

        <label>
            Wilaya
            <input
                type="text"
                name="wilaya"
                value="{{ old('wilaya') }}"
                required
            >
        </label>

        <div class="checkout__payment">
            <label class="payment-option">
                <input
                    type="radio"
                    name="payment_method"
                    value="cod"
                    checked
                >
                <span>Paiement à la livraison</span>
            </label>
        </div>

        @if($errors->any())
            <div class="checkout__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="checkout__submit">
            Confirmer la commande
        </button>

    </form>

    <div class="checkout__summary">
        <h2>Récapitulatif</h2>

        @foreach($items as $item)
            <div class="checkout__summary-item">
                <span>
                    {{ $item['variant']->product->name }}
                    ({{ $item['variant']->size }})
                    × {{ $item['quantity'] }}
                </span>

                <span>
                    {{ number_format($item['subtotal'], 0) }} DA
                </span>
            </div>
        @endforeach

        <div class="checkout__summary-total">
            <span>Total</span>

            <span>
                {{ number_format($total, 0) }} DA
            </span>
        </div>
    </div>

</div>

</div>
@endsection
