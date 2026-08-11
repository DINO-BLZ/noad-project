@extends('layouts.app')

@section('content')
<div class="checkout">

    <h1 class="checkout__title">Finaliser la commande</h1>

    <div class="checkout__grid">

        <form action="{{ route('checkout.store') }}" method="POST" class="checkout__form">
            @csrf

            <label>Nom complet
                <input type="text" name="full_name" value="{{ old('full_name') }}" required>
            </label>

            <label>Téléphone
                <input type="text" name="phone" value="{{ old('phone') }}" required>
            </label>

            <label>Adresse
                <input type="text" name="address" value="{{ old('address') }}" required>
            </label>

            <label>Wilaya
                <input type="text" name="wilaya" value="{{ old('wilaya') }}" required>
            </label>

            <div class="checkout__payment">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cod" checked>
                    <span>Paiement à la livraison</span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cib">
                    <span>Carte CIB / Edahabia</span>
                </label>
            </div>

            @if($errors->any())
                <div class="checkout__errors">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="checkout__submit">Confirmer la commande</button>
        </form>

        <div class="checkout__summary">
            <h2>Récapitulatif</h2>

            @foreach($items as $item)
                <div class="checkout__summary-item">
                    <span>{{ $item['variant']->product->name }} ({{ $item['variant']->size }}) × {{ $item['quantity'] }}</span>
                    <span>{{ number_format($item['subtotal'], 0) }} DA</span>
                </div>
            @endforeach

            <div class="checkout__summary-total">
                <span>Total</span>
                <span>{{ number_format($total, 0) }} DA</span>
            </div>
        </div>

    </div>

</div>

<style>
.checkout{
    max-width:1000px;
    margin:0 auto;
    padding:3rem 2rem;
}

.checkout__title{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:2rem;
}

.checkout__grid{
    display:grid;
    grid-template-columns:1.3fr 1fr;
    gap:3rem;
}

.checkout__form{
    display:flex;
    flex-direction:column;
    gap:1.2rem;
}

.checkout__form label{
    display:flex;
    flex-direction:column;
    gap:.4rem;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.8;
}

.checkout__form input[type="text"]{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.8rem;
    font-size:.9rem;
}

.checkout__payment{
    display:flex;
    gap:1rem;
    margin:.5rem 0;
}

.payment-option{
    flex:1;
    border:1px solid var(--border);
    padding:1rem;
    display:flex;
    align-items:center;
    gap:.6rem;
    cursor:pointer;
    font-size:.85rem;
    text-transform:none;
}

.checkout__errors{
    color:var(--accent);
    font-size:.85rem;
}

.checkout__submit{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:1rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
}

.checkout__summary{
    border:1px solid var(--border);
    padding:1.5rem;
    height:fit-content;
}

.checkout__summary h2{
    font-size:1rem;
    text-transform:uppercase;
    margin-bottom:1rem;
}

.checkout__summary-item{
    display:flex;
    justify-content:space-between;
    font-size:.85rem;
    padding:.5rem 0;
    border-bottom:1px solid var(--border);
}

.checkout__summary-total{
    display:flex;
    justify-content:space-between;
    font-weight:900;
    text-transform:uppercase;
    margin-top:1rem;
    padding-top:1rem;
    border-top:2px solid var(--text);
}

@media(max-width:768px){
    .checkout__grid{
        grid-template-columns:1fr;
    }
}
</style>
@endsection