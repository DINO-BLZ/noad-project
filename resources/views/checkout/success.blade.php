@extends('layouts.app')

@section('content')
<div class="success">
    <h1>Commande confirmée</h1>
    <p>Merci {{ $order->full_name }}, ta commande #{{ $order->id }} a bien été enregistrée.</p>
    <p>Total : {{ number_format($order->total, 0) }} DA — {{ $order->payment_method === 'cod' ? 'Paiement à la livraison' : 'Carte CIB / Edahabia' }}</p>
</div>

<style>
.success{
    max-width:600px;
    margin:5rem auto;
    text-align:center;
}
.success h1{
    font-size:1.8rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:1rem;
}
.success p{
    opacity:.8;
    margin-bottom:.5rem;
}
</style>
@endsection