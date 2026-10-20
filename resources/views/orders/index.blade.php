@extends('layouts.app')

@section('title', 'Mes Commandes — NOAD')
@section('robots', 'noindex, nofollow')

@section('content')

@php
    use App\Enums\OrderStatus;

    $totalCount = $orders->total();
    $inProgressCount = $orders->filter(function ($order) {
        return in_array($order->status, [OrderStatus::Pending, OrderStatus::Paid, OrderStatus::Shipped], true);
    })->count();
@endphp

<div class="orders-page">

    {{-- =========================================================
         01. BANDE TECHNIQUE SUPÉRIEURE
    ========================================================== --}}
    <div class="orders-top-strip">
        <div class="container-orders strip-flex">
            <div class="strip-left font-mono">
                <span class="dot-red-sq">■</span>
                SYS.ID: CLIENT-ORDERS // REGISTRE PERSONNEL
            </div>
            <div class="strip-right font-mono">
                <span class="pulse-dot">●</span>
                {{ $inProgressCount }} COMMANDE(S) EN FLUX ACTIF
            </div>
        </div>
    </div>

    <div class="container-orders">

        {{-- =====================================================
             02. EN-TÊTE & FIL D'ARIANE
        ====================================================== --}}
        <div class="orders-header-section">
            <div class="breadcrumbs-row font-mono">
                <a href="{{ route('home') }}">ACCUEIL</a>
                <span class="sep">/</span>
                <span class="sep-user">COMPTE</span>
                <span class="sep">/</span>
                <span class="active">MES COMMANDES</span>
            </div>

            <div class="header-split-row">
                <div class="header-text-block">
                    <h1 class="orders-main-title">MES COMMANDES</h1>
                    <p class="orders-lead-desc">
                        Consultez l'historique complet de vos acquisitions, suivez l'avancement de la préparation
                        et l'acheminement de vos colis jusqu'à réception.
                    </p>
                </div>

                <div class="orders-stat-widget font-mono">
                    <span class="widget-label">VOLUMÉTRIE COMPTE</span>
                    <div class="widget-val text-accent">
                        {{ $totalCount }} {{ $totalCount > 1 ? 'COMMANDES' : 'COMMANDE' }}
                    </div>
                    <span class="widget-sub">
                        PAIEMENT SÉCURISÉ À LA LIVRAISON (COD)
                    </span>
                </div>
            </div>
        </div>

        {{-- =====================================================
             03. LISTE DES COMMANDES
        ====================================================== --}}
        @if($orders->isEmpty())
            <div class="empty-orders-card">
                <div class="empty-icon font-mono">00 // VIDE</div>
                <h2 class="empty-title">AUCUNE COMMANDE ENREGISTRÉE</h2>
                <p class="empty-desc">
                    Vous n'avez pas encore passé de commande sur le catalogue NOAD ou participé à un drop exclusif.
                </p>
                <div class="empty-actions">
                    <a href="{{ route('shop.index') }}" class="btn-tactical">
                        ACCÉDER À LA BOUTIQUE →
                    </a>
                    <a href="{{ route('drops.index') }}" class="btn-tactical-outline">
                        VOIR LES DROPS ACTIFS
                    </a>
                </div>
            </div>
        @else
            <div class="orders-list">
                @foreach($orders as $order)
                    @php
                        $statusClass = match($order->status) {
                            OrderStatus::Pending => 'status--pending',
                            OrderStatus::Paid => 'status--paid',
                            OrderStatus::Shipped => 'status--shipped',
                            OrderStatus::Delivered => 'status--delivered',
                            OrderStatus::Cancelled => 'status--cancelled',
                            default => 'status--default',
                        };

                        $statusBadgeText = match($order->status) {
                            OrderStatus::Pending => 'EN ATTENTE DE CONFIRMATION',
                            OrderStatus::Paid => 'CONFIRMÉE // EN PRÉPARATION',
                            OrderStatus::Shipped => 'EXPÉDIÉE // EN TRANSIT',
                            OrderStatus::Delivered => 'LIVRÉE AVEC SUCCÈS',
                            OrderStatus::Cancelled => 'COMMANDE ANNULÉE',
                            default => strtoupper($order->status?->value ?? 'INCONNU'),
                        };
                    @endphp

                    <article class="order-card">
                        {{-- CARTOUCHE HAUT --}}
                        <div class="order-card__head">
                            <div class="order-card__meta">
                                <span class="order-ref font-mono">
                                    {{ $order->formatted_reference }}
                                </span>
                                <span class="order-date font-mono">
                                    ENREGISTRÉE LE {{ $order->created_at->format('d.m.Y à H:i') }}
                                </span>
                            </div>

                            <div class="order-card__badge-wrap">
                                <span class="order-status-badge {{ $statusClass }} font-mono">
                                    <span class="badge-dot">●</span>
                                    {{ $statusBadgeText }}
                                </span>
                            </div>
                        </div>

                        {{-- CONTENU PRINCIPAL --}}
                        <div class="order-card__body">
                            {{-- LISTE DES ARTICLES --}}
                            <div class="order-items-summary">
                                <h3 class="section-micro-title font-mono">ARTICLES ({{ $order->items->sum('quantity') }})</h3>
                                <ul class="items-list">
                                    @foreach($order->items as $item)
                                        <li class="item-row">
                                            <div class="item-info">
                                                <span class="item-name">{{ $item->product_name }}</span>
                                                <div class="item-specs font-mono">
                                                    @if($item->variant_size)
                                                        <span class="spec-tag">Taille: {{ $item->variant_size }}</span>
                                                    @endif
                                                    @if($item->variant_color)
                                                        <span class="spec-tag">Couleur: {{ $item->variant_color }}</span>
                                                    @endif
                                                    <span class="spec-qty">Qté: {{ $item->quantity }}</span>
                                                </div>
                                            </div>
                                            <div class="item-price font-mono">
                                                {{ number_format($item->price * $item->quantity, 0, ',', ' ') }} DA
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- RÉCAPITULATIF LIVRAISON --}}
                            <div class="order-delivery-summary">
                                <h3 class="section-micro-title font-mono">EXPÉDITION & CONTACT</h3>
                                <div class="delivery-details font-mono">
                                    <div class="detail-line">
                                        <span class="label">DESTINATAIRE:</span>
                                        <span class="value">{{ $order->full_name }}</span>
                                    </div>
                                    <div class="detail-line">
                                        <span class="label">TÉLÉPHONE:</span>
                                        <span class="value">{{ $order->phone }}</span>
                                    </div>
                                    <div class="detail-line">
                                        <span class="label">WILAYA:</span>
                                        <span class="value">{{ $order->wilaya }}</span>
                                    </div>
                                    <div class="detail-line">
                                        <span class="label">ADRESSE:</span>
                                        <span class="value">{{ $order->address }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PIED DE LA COMMANDE --}}
                        <div class="order-card__foot">
                            <div class="order-total-block">
                                <span class="total-label font-mono">TOTAL À RÉGLER (COD) :</span>
                                <span class="total-amount font-mono">
                                    {{ number_format($order->total, 0, ',', ' ') }} DA
                                </span>
                            </div>

                            <div class="order-action-block">
                                <a href="{{ route('orders.show', $order->id) }}" class="btn-details font-mono">
                                    VOIR LE SUIVI DÉTAILLÉ →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach

                {{-- PAGINATION --}}
                <div class="orders-pagination">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif

    </div>

</div>

<style>
.orders-page {
    min-height: 80vh;
    padding-bottom: 5rem;
    color: var(--text);
}

.container-orders {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.orders-top-strip {
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.02);
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    padding: 0.6rem 0;
}

.strip-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dot-red-sq {
    color: var(--accent);
    margin-right: 0.4rem;
}

.pulse-dot {
    color: var(--accent);
    margin-right: 0.4rem;
    display: inline-block;
    animation: pulse 1.8s infinite;
}

@keyframes pulse {
    0% { opacity: 0.3; }
    50% { opacity: 1; }
    100% { opacity: 0.3; }
}

.font-mono {
    font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
}

.orders-header-section {
    padding: 3rem 0 2.5rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 2.5rem;
}

.breadcrumbs-row {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    color: rgba(245, 245, 245, 0.5);
    margin-bottom: 1.5rem;
}

.breadcrumbs-row a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumbs-row a:hover {
    color: var(--text);
}

.breadcrumbs-row .sep {
    margin: 0 0.5rem;
    opacity: 0.3;
}

.breadcrumbs-row .active {
    color: var(--accent);
}

.header-split-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    flex-wrap: wrap;
}

.header-text-block {
    max-width: 650px;
}

.orders-main-title {
    font-size: 2.5rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    margin: 0 0 1rem;
    text-transform: uppercase;
}

.orders-lead-desc {
    color: rgba(245, 245, 245, 0.7);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0;
}

.orders-stat-widget {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    padding: 1.25rem 1.75rem;
    min-width: 260px;
}

.widget-label {
    display: block;
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    color: rgba(245, 245, 245, 0.5);
    margin-bottom: 0.5rem;
}

.widget-val {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
}

.text-accent {
    color: var(--accent);
}

.widget-sub {
    display: block;
    font-size: 0.65rem;
    letter-spacing: 0.08em;
    color: rgba(245, 245, 245, 0.4);
}

/* CARTE VIDE */
.empty-orders-card {
    border: 1px dashed var(--border);
    padding: 4rem 2rem;
    text-align: center;
    background: rgba(255, 255, 255, 0.01);
}

.empty-icon {
    font-size: 0.85rem;
    letter-spacing: 0.15em;
    color: var(--accent);
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.4rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
}

.empty-desc {
    color: rgba(245, 245, 245, 0.6);
    max-width: 500px;
    margin: 0 auto 2rem;
    line-height: 1.6;
}

.empty-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-tactical {
    display: inline-block;
    background: var(--text);
    color: var(--bg);
    padding: 0.85rem 1.75rem;
    font-weight: 700;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-tactical:hover {
    background: var(--accent);
    color: #fff;
}

.btn-tactical-outline {
    display: inline-block;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0.85rem 1.75rem;
    font-weight: 600;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-tactical-outline:hover {
    border-color: var(--text);
}

/* LISTE DE COMMANDES */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.order-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    transition: border-color 0.2s;
}

.order-card:hover {
    border-color: rgba(245, 245, 245, 0.3);
}

.order-card__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.015);
    flex-wrap: wrap;
    gap: 1rem;
}

.order-card__meta {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.order-ref {
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: var(--text);
}

.order-date {
    font-size: 0.75rem;
    color: rgba(245, 245, 245, 0.5);
    letter-spacing: 0.05em;
}

.order-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.85rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    border: 1px solid transparent;
}

.badge-dot {
    font-size: 0.65rem;
}

.status--pending {
    background: rgba(234, 179, 8, 0.1);
    color: #facc15;
    border-color: rgba(234, 179, 8, 0.3);
}

.status--paid {
    background: rgba(59, 130, 246, 0.1);
    color: #60a5fa;
    border-color: rgba(59, 130, 246, 0.3);
}

.status--shipped {
    background: rgba(168, 85, 247, 0.1);
    color: #c084fc;
    border-color: rgba(168, 85, 247, 0.3);
}

.status--delivered {
    background: rgba(34, 197, 94, 0.1);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.3);
}

.status--cancelled {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}

.order-card__body {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 2rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
}

@media (max-width: 850px) {
    .order-card__body {
        grid-template-columns: 1fr;
    }
}

.section-micro-title {
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    color: rgba(245, 245, 245, 0.45);
    margin: 0 0 1rem;
    text-transform: uppercase;
}

.items-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.item-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.item-name {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.item-specs {
    font-size: 0.75rem;
    color: rgba(245, 245, 245, 0.55);
    display: flex;
    gap: 0.85rem;
}

.item-price {
    font-size: 0.9rem;
    font-weight: 700;
    white-space: nowrap;
    padding-left: 1rem;
}

.delivery-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.78rem;
    background: rgba(255, 255, 255, 0.015);
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-line {
    display: flex;
    gap: 0.75rem;
}

.detail-line .label {
    color: rgba(245, 245, 245, 0.4);
    min-width: 95px;
}

.detail-line .value {
    color: rgba(245, 245, 245, 0.9);
}

.order-card__foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: rgba(255, 255, 255, 0.01);
    flex-wrap: wrap;
    gap: 1rem;
}

.order-total-block {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
}

.total-label {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    color: rgba(245, 245, 245, 0.5);
}

.total-amount {
    font-size: 1.35rem;
    font-weight: 900;
    color: var(--accent);
}

.btn-details {
    display: inline-block;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0.65rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-details:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(176, 46, 38, 0.05);
}

.orders-pagination {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}
</style>
@endsection
