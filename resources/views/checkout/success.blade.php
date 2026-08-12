@extends('layouts.app')

@section('content')
<div class="success">
    <h1>Commande confirmée</h1>
    <p>Merci {{ $order->full_name }}, ta commande #{{ $order->id }} a bien été enregistrée.</p>
    <p>Total : {{ number_format($order->total, 0) }} DA — {{ $order->payment_method === 'cod' ? 'Paiement à la livraison' : 'Carte CIB / Edahabia' }}</p>
</div>
@endsection