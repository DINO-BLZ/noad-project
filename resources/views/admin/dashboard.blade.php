@extends('layouts.admin')

@section('content')
<div class="admin-dashboard">

    <h1 class="admin-dashboard__title">Tableau de bord</h1>

    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-card__label">Chiffre d'affaires</span>
            <span class="kpi-card__value">{{ number_format($totalRevenue, 0) }} DA</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-card__label">Commandes totales</span>
            <span class="kpi-card__value">{{ $totalOrders }}</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-card__label">Commandes en attente</span>
            <span class="kpi-card__value">{{ $pendingOrders }}</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-card__label">Comptes inscrits</span>
            <span class="kpi-card__value">{{ $totalUsers }}</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-card__label">Produits</span>
            <span class="kpi-card__value">{{ $totalProducts }}</span>
        </div>
    </div>

    @if($salesByDay->count() > 0)
        @php $maxSale = $salesByDay->max('total'); @endphp
        <div class="admin-panel admin-panel--chart">
            <h2>Ventes des 7 derniers jours</h2>
            <div class="sales-chart">
                @foreach($salesByDay as $day)
                    <div class="sales-chart__bar-wrapper">
                        <div class="sales-chart__bar" style="height: {{ $maxSale > 0 ? ($day->total / $maxSale) * 100 : 0 }}%"></div>
                        <span class="sales-chart__value">{{ number_format($day->total, 0) }}</span>
                        <span class="sales-chart__label">{{ \Carbon\Carbon::parse($day->day)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="admin-dashboard__row">

        <div class="admin-panel">
            <h2>Commandes récentes</h2>
            @forelse($recentOrders as $order)
                <div class="order-row">
                    <span>#{{ $order->id }} — {{ $order->full_name }}</span>
                    <span>{{ number_format($order->total, 0) }} DA</span>
                    <span class="order-status order-status--{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
            @empty
                <p class="admin-panel__empty">Aucune commande pour le moment.</p>
            @endforelse
        </div>

        <div class="admin-panel">
            <h2>Stock faible (≤ 5)</h2>
            @forelse($lowStockVariants as $variant)
                <div class="order-row">
                    <span>{{ $variant->product->name }} — {{ $variant->size }}</span>
                    <span class="stock-warning">{{ $variant->stock }} restants</span>
                </div>
            @empty
                <p class="admin-panel__empty">Aucun stock critique.</p>
            @endforelse
        </div>

        <div class="admin-panel">
            <h2>Meilleures ventes</h2>
            @forelse($topProducts as $product)
                <div class="order-row">
                    <span>{{ $product->product_name }}</span>
                    <span>{{ $product->total_sold }} vendu(s)</span>
                </div>
            @empty
                <p class="admin-panel__empty">Aucune vente pour le moment.</p>
            @endforelse
        </div>

    </div>

</div>

<style>
.admin-dashboard{
    padding:3rem;
    max-width:1200px;
    margin:0 auto;
}

.admin-dashboard__header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:2rem;
    flex-wrap:wrap;
    gap:1rem;
}

.admin-dashboard__header h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
}

.admin-nav{
    display:flex;
    gap:1.5rem;
}

.admin-nav a{
    color:var(--text);
    opacity:.6;
    text-decoration:none;
    font-size:.85rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.admin-nav a.is-active,
.admin-nav a:hover{
    opacity:1;
    color:var(--accent);
}

.kpi-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
    gap:1.2rem;
    margin-bottom:2.5rem;
}

.kpi-card{
    border:1px solid var(--border);
    padding:1.5rem;
    display:flex;
    flex-direction:column;
    gap:.5rem;
}

.kpi-card__label{
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.6;
}

.kpi-card__value{
    font-size:1.6rem;
    font-weight:900;
}

.admin-panel--chart{
    border:1px solid var(--border);
    padding:1.5rem;
    margin-bottom:1.5rem;
}

.admin-panel--chart h2{
    font-size:1rem;
    text-transform:uppercase;
    margin-bottom:1.5rem;
}

.sales-chart{
    display:flex;
    align-items:flex-end;
    gap:1rem;
    height:160px;
    padding-top:1rem;
}

.sales-chart__bar-wrapper{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-end;
    height:100%;
    gap:.4rem;
}

.sales-chart__value{
    font-size:.7rem;
    opacity:.7;
    order:-1;
}

.sales-chart__bar{
    width:100%;
    max-width:40px;
    background:var(--accent);
    border-radius:4px 4px 0 0;
    min-height:2px;
    transition:.3s;
}

.sales-chart__label{
    font-size:.7rem;
    opacity:.6;
}

.admin-dashboard__row{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:1.5rem;
}

.admin-panel{
    border:1px solid var(--border);
    padding:1.5rem;
}

.admin-panel h2{
    font-size:1rem;
    text-transform:uppercase;
    margin-bottom:1rem;
}

.order-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:.6rem 0;
    border-bottom:1px solid var(--border);
    font-size:.85rem;
    gap:.5rem;
}

.order-row:last-child{
    border-bottom:none;
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

.stock-warning{
    color:var(--accent);
    font-weight:700;
}

.admin-panel__empty{
    opacity:.5;
    font-size:.85rem;
}

@media(max-width:900px){
    .admin-dashboard__row{
        grid-template-columns:1fr;
    }
}
</style>
@endsection