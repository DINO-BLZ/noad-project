@extends('layouts.admin')

@section('content')
<div class="order-detail">

    <a href="{{ route('admin.orders.index') }}" class="order-detail__back">← Retour aux commandes</a>

    <h1 class="order-detail__title">Commande #{{ $order->id }}</h1>

    @if(session('success'))
        <p class="order-detail__notice">{{ session('success') }}</p>
    @endif

    <div class="order-detail__grid">

        <div class="order-detail__panel">
            <h2>Client</h2>
            <p><strong>{{ $order->full_name }}</strong></p>
            <p>{{ $order->phone }}</p>
            <p>{{ $order->address }}, {{ $order->wilaya }}</p>
            <p>Paiement : {{ $order->payment_method === 'cod' ? 'À la livraison' : 'Carte CIB / Edahabia' }}</p>
            @if($order->user)
                <p>Compte : {{ $order->user->email }}</p>
            @else
                <p>Commande invité</p>
            @endif
        </div>

        <div class="order-detail__panel">
            <h2>Statut</h2>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="order-detail__status-form">
                @csrf
                @method('PATCH')
                <select name="status">
                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
                <button type="submit">Mettre à jour</button>
            </form>
        </div>

    </div>

    <div class="order-detail__panel">
        <h2>Articles</h2>
        <table class="order-detail__table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Taille</th>
                    <th>Qté</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->variant_size }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0) }} DA</td>
                        <td>{{ number_format($item->price * $item->quantity, 0) }} DA</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="order-detail__total">
            <span>Total</span>
            <span>{{ number_format($order->total, 0) }} DA</span>
        </div>
    </div>

</div>

<style>
.order-detail{
    padding:3rem;
    max-width:1000px;
    margin:0 auto;
}

.order-detail__back{
    display:inline-block;
    color:var(--text);
    opacity:.6;
    text-decoration:none;
    font-size:.85rem;
    margin-bottom:1.5rem;
}

.order-detail__back:hover{
    opacity:1;
}

.order-detail__title{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:1.5rem;
}

.order-detail__notice{
    background:var(--accent);
    color:#fff;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.order-detail__grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:1.5rem;
    margin-bottom:1.5rem;
}

.order-detail__panel{
    border:1px solid var(--border);
    padding:1.5rem;
    margin-bottom:1.5rem;
}

.order-detail__panel h2{
    font-size:1rem;
    text-transform:uppercase;
    margin-bottom:1rem;
}

.order-detail__panel p{
    font-size:.85rem;
    margin-bottom:.4rem;
    opacity:.85;
}

.order-detail__status-form{
    display:flex;
    gap:.8rem;
}

.order-detail__status-form select{
    flex:1;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.6rem;
    border-radius:6px;
}

.order-detail__status-form button{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:.6rem 1.2rem;
    border-radius:6px;
    cursor:pointer;
    text-transform:uppercase;
    font-size:.8rem;
    letter-spacing:.05em;
}

.order-detail__table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:1rem;
}

.order-detail__table th{
    text-align:left;
    font-size:.75rem;
    text-transform:uppercase;
    opacity:.6;
    padding:.6rem;
    border-bottom:1px solid var(--border);
}

.order-detail__table td{
    padding:.6rem;
    border-bottom:1px solid var(--border);
    font-size:.85rem;
}

.order-detail__total{
    display:flex;
    justify-content:space-between;
    font-weight:900;
    text-transform:uppercase;
    padding-top:1rem;
}

@media(max-width:768px){
    .order-detail__grid{
        grid-template-columns:1fr;
    }
}
</style>
@endsection