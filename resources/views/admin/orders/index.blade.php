@extends('layouts.admin')

@section('content')
<div class="admin-orders">

    <h1 class="admin-orders__title">Commandes</h1>

    @if(session('success'))
        <p class="admin-orders__notice">{{ session('success') }}</p>
    @endif

    <div class="admin-orders__filters">
        <a href="{{ route('admin.orders.index') }}" class="{{ !request('status') ? 'is-active' : '' }}">Toutes</a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="{{ request('status') === 'pending' ? 'is-active' : '' }}">En attente</a>
        <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="{{ request('status') === 'paid' ? 'is-active' : '' }}">Payées</a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="{{ request('status') === 'shipped' ? 'is-active' : '' }}">Expédiées</a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="{{ request('status') === 'cancelled' ? 'is-active' : '' }}">Annulées</a>
    </div>

    <table class="admin-orders__table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Client</th>
                <th>Total</th>
                <th>Paiement</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->full_name }}</td>
                    <td>{{ number_format($order->total, 0) }} DA</td>
                    <td>{{ $order->payment_method === 'cod' ? 'Livraison' : 'CIB' }}</td>
                    <td><span class="order-status order-status--{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="admin-orders__empty">Aucune commande.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}

</div>

<style>
.admin-orders{
    padding:3rem;
    max-width:1200px;
    margin:0 auto;
}

.admin-orders__title{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:1.5rem;
}

.admin-orders__notice{
    background:var(--accent);
    color:#fff;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.admin-orders__filters{
    display:flex;
    gap:1.5rem;
    margin-bottom:1.5rem;
}

.admin-orders__filters a{
    color:var(--text);
    text-decoration:none;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.6;
}

.admin-orders__filters a.is-active,
.admin-orders__filters a:hover{
    opacity:1;
    color:var(--accent);
}

.admin-orders__table{
    width:100%;
    border-collapse:collapse;
}

.admin-orders__table th{
    text-align:left;
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.6;
    padding:.8rem;
    border-bottom:1px solid var(--border);
}

.admin-orders__table td{
    padding:.8rem;
    border-bottom:1px solid var(--border);
    font-size:.85rem;
}

.admin-orders__table tbody tr{
    cursor:pointer;
    transition:.2s;
}

.admin-orders__table tbody tr:hover{
    background:rgba(245,245,245,.03);
}

.admin-orders__empty{
    text-align:center;
    opacity:.5;
    padding:2rem 0;
}

.order-status{
    font-size:.7rem;
    text-transform:uppercase;
    padding:.2rem .6rem;
    border:1px solid var(--border);
}

.order-status--pending{ color:#e0a800; border-color:#e0a800; }
.order-status--paid{ color:#2ecc71; border-color:#2ecc71; }
.order-status--shipped{ color:#3498db; border-color:#3498db; }
.order-status--cancelled{ color:var(--accent); border-color:var(--accent); }
</style>
@endsection