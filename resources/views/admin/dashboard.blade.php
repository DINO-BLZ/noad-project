@extends('layouts.admin')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Données du graphique des ventes
    |--------------------------------------------------------------------------
    */

    $maxSalesByDay = $salesByDay->max(
        fn ($sale) => (float) $sale->total
    ) ?? 0;


    /*
    |--------------------------------------------------------------------------
    | Monitoring du Drop actif
    |--------------------------------------------------------------------------
    */

    $hasActiveDrop = $activeDrop !== null;

    $dropName = $activeDrop?->name ?? 'AUCUN DROP ACTIF';

    $dropQuota = (int) ($dropMonitoring['quota'] ?? 0);
    $dropSold = (int) ($dropMonitoring['sold'] ?? 0);
    $dropRemaining = (int) ($dropMonitoring['remaining'] ?? 0);
    $dropPercentage = (int) ($dropMonitoring['percentage'] ?? 0);
    $dropQuotaDefined = (bool) ($dropMonitoring['quota_defined'] ?? false);

@endphp


<div class="admin-dashboard-view">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="dashboard-header">

        <div class="dashboard-header-left">

            <div class="dashboard-kicker font-mono">
                NOAD / ADMIN
            </div>

            <h1 class="dashboard-title">
                CENTRE DE CONTRÔLE
            </h1>

            <p class="dashboard-subtitle">
                Vue opérationnelle de votre activité.
            </p>

        </div>


        <div class="dashboard-header-meta font-mono">

            <span class="server-status">
                <span class="server-dot"></span>
                SERVEUR LIVE
            </span>

            <span class="header-separator">•</span>

            <span>
                {{ now('Africa/Algiers')->format('H:i:s') }}
                UTC+1
            </span>

        </div>

    </div>


    {{-- =========================================================
         NAVIGATION ADMIN
    ========================================================== --}}

    <nav class="dashboard-nav font-mono">

        <a
            href="{{ route('admin.dashboard') }}"
            class="dashboard-nav-item active"
        >
            DASHBOARD
        </a>

        <a
            href="{{ route('admin.products.index') }}"
            class="dashboard-nav-item"
        >
            PRODUITS
        </a>

        @if(Route::has('admin.orders.index'))

            <a
                href="{{ route('admin.orders.index') }}"
                class="dashboard-nav-item"
            >
                COMMANDES

                <span class="nav-count">
                    {{ $pendingOrders }}
                </span>
            </a>

        @else

            <span class="dashboard-nav-item">

                COMMANDES

                <span class="nav-count">
                    {{ $pendingOrders }}
                </span>

            </span>

        @endif


        @if(Route::has('admin.whitelist.index'))

            <a
                href="{{ route('admin.whitelist.index') }}"
                class="dashboard-nav-item"
            >
                WHITELIST

                <span class="nav-count">
                    {{ $pendingReviews }}
                </span>
            </a>

        @else

            <span class="dashboard-nav-item">

                WHITELIST

                <span class="nav-count">
                    {{ $pendingReviews }}
                </span>

            </span>

        @endif

    </nav>


    {{-- =========================================================
         KPI
    ========================================================== --}}

    <section class="kpi-metrics-grid">

        <div class="kpi-card">

            <span class="kpi-label font-mono">
                CHIFFRE D'AFFAIRES
            </span>

            <strong class="kpi-value">

                {{ number_format($totalRevenue, 0, ',', ' ') }}

                <span class="kpi-unit">
                    DA
                </span>

            </strong>

            <span class="kpi-meta font-mono">
                COMMANDES CONFIRMÉES
            </span>

        </div>


        <div class="kpi-card">

            <span class="kpi-label font-mono">
                COMMANDES
            </span>

            <strong class="kpi-value">
                {{ $totalOrders }}
            </strong>

            <span class="kpi-meta font-mono">
                {{ $confirmedOrders }} CONFIRMÉES
            </span>

        </div>


        <div class="kpi-card">

            <span class="kpi-label font-mono">
                STOCK DISPONIBLE
            </span>

            <strong class="kpi-value">
                {{ $totalStock }}
            </strong>

            <span class="kpi-meta font-mono">
                UNITÉS
            </span>

        </div>


        <div class="kpi-card">

            <span class="kpi-label font-mono">
                WHITELIST
            </span>

            <strong class="kpi-value">
                {{ $whitelistApplications }}
            </strong>

            <span class="kpi-meta font-mono">
                {{ $pendingReviews }} EN ATTENTE
            </span>

        </div>

    </section>


    {{-- =========================================================
         GRILLE PRINCIPALE
    ========================================================== --}}

    <div class="dashboard-main-grid">


        {{-- =====================================================
             COLONNE PRINCIPALE
        ====================================================== --}}

        <div class="grid-primary-column">


            {{-- =================================================
                 COMMANDES RÉCENTES
            ================================================== --}}

            <section class="dashboard-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            01
                        </span>

                        <h2 class="card-title">
                            COMMANDES RÉCENTES
                        </h2>

                    </div>

                    @if(Route::has('admin.orders.index'))

                        <a
                            href="{{ route('admin.orders.index') }}"
                            class="card-link font-mono"
                        >
                            VOIR LES COMMANDES →
                        </a>

                    @endif

                </div>


                <div class="table-responsive">

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>
                                    CLIENT
                                </th>

                                <th>
                                    LOCALISATION
                                </th>

                                <th>
                                    ARTICLES
                                </th>

                                <th>
                                    TOTAL
                                </th>

                                <th>
                                    STATUT
                                </th>

                                <th>
                                    DATE
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($recentOrders as $order)

                                @php

                                    $orderStatus = $order->status instanceof \BackedEnum
                                        ? $order->status->value
                                        : (string) $order->status;

                                    $statusConfig = match ($orderStatus) {

                                        'pending' => [
                                            'label' => 'EN ATTENTE',
                                            'class' => 'pending',
                                        ],

                                        'paid' => [
                                            'label' => 'À EXPÉDIER',
                                            'class' => 'to-ship',
                                        ],

                                        'shipped' => [
                                            'label' => 'EXPÉDIÉE',
                                            'class' => 'shipped',
                                        ],

                                        'delivered' => [
                                            'label' => 'LIVRÉE',
                                            'class' => 'delivered',
                                        ],

                                        'cancelled' => [
                                            'label' => 'ANNULÉE',
                                            'class' => 'cancelled',
                                        ],

                                        default => [
                                            'label' => strtoupper($orderStatus),
                                            'class' => 'pending',
                                        ],

                                    };

                                @endphp


                                <tr>

                                    <td>

                                        <div class="customer-cell">

                                            <strong>
                                                {{ $order->full_name }}
                                            </strong>

                                            <span class="customer-email">
                                                {{ $order->user?->email ?? '—' }}
                                            </span>

                                        </div>

                                    </td>


                                    <td>

                                        <span class="wilaya-cell">
                                            {{ $order->wilaya }}
                                        </span>

                                    </td>


                                    <td>

                                        <span class="articles-count">
                                            {{ $order->items_count }}
                                        </span>

                                    </td>


                                    <td>

                                        <strong>

                                            {{ number_format($order->total, 0, ',', ' ') }}

                                            DA

                                        </strong>

                                    </td>


                                    <td>

                                        <span
                                            class="badge-status {{ $statusConfig['class'] }}"
                                        >
                                            {{ $statusConfig['label'] }}
                                        </span>

                                    </td>


                                    <td>

                                        <span class="order-date font-mono">
                                            {{ $order->created_at?->format('d/m/Y H:i') }}
                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="empty-orders-cell"
                                    >
                                        AUCUNE COMMANDE ENREGISTRÉE
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>


            {{-- =================================================
                 VENTES
            ================================================== --}}

            <section class="dashboard-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            02
                        </span>

                        <h2 class="card-title">
                            VENTES DES 7 DERNIERS JOURS
                        </h2>

                    </div>

                </div>


                <div class="sales-list">

                    @forelse($salesByDay as $sale)

                        @php

                            $saleTotal = (float) $sale->total;

                            $salesBarWidth = $maxSalesByDay > 0
                                ? min(
                                    100,
                                    ($saleTotal / $maxSalesByDay) * 100
                                )
                                : 0;

                        @endphp


                        <div class="sales-row">

                            <span class="sales-day font-mono">

                                {{ \Carbon\Carbon::parse($sale->day)->format('d/m') }}

                            </span>


                            <span class="sales-bar-wrap">

                                <span
                                    class="sales-bar"
                                    style="width: {{ $salesBarWidth }}%;"
                                ></span>

                            </span>


                            <strong class="sales-value font-mono">

                                {{ number_format($saleTotal, 0, ',', ' ') }}

                                DA

                            </strong>

                        </div>

                    @empty

                        <div class="empty-state">
                            AUCUNE VENTE CONFIRMÉE SUR LA PÉRIODE.
                        </div>

                    @endforelse

                </div>

            </section>


            {{-- =================================================
                 TOP PRODUITS
            ================================================== --}}

            <section class="dashboard-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            03
                        </span>

                        <h2 class="card-title">
                            PRODUITS LES PLUS VENDUS
                        </h2>

                    </div>

                </div>


                <div class="top-products-list">

                    @forelse($topProducts as $product)

                        <div class="top-product-row">

                            <span class="top-product-name">
                                {{ $product->product_name }}
                            </span>

                            <span class="top-product-sales font-mono">
                                {{ $product->total_sold }}
                                VENDUES
                            </span>

                        </div>

                    @empty

                        <div class="empty-state">
                            AUCUNE VENTE ENREGISTRÉE.
                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        {{-- =====================================================
             COLONNE DROITE
        ====================================================== --}}

        <aside class="dashboard-sidebar">


            {{-- =================================================
                 DROP ACTIF
            ================================================== --}}

            <section class="dashboard-card sidebar-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            01
                        </span>

                        <h2 class="card-title">
                            MONITORING DROP
                        </h2>

                    </div>


                    @if($hasActiveDrop)

                        <span class="card-badge-live font-mono">
                            LIVE
                        </span>

                    @else

                        <span class="card-badge-live card-badge-neutral font-mono">
                            INACTIF
                        </span>

                    @endif

                </div>


                <div class="drop-monitor">

                    @if($hasActiveDrop)

                        <div class="drop-monitor-name">
                            {{ $dropName }}
                        </div>


                        @if($dropQuotaDefined)

                            <div class="drop-monitor-grid">

                                <div>

                                    <span class="monitor-label font-mono">
                                        VENDUES
                                    </span>

                                    <strong>
                                        {{ $dropSold }} / {{ $dropQuota }}
                                    </strong>

                                </div>


                                <div>

                                    <span class="monitor-label font-mono">
                                        TAUX
                                    </span>

                                    <strong>
                                        {{ $dropPercentage }}%
                                    </strong>

                                </div>


                                <div>

                                    <span class="monitor-label font-mono">
                                        RESTANTES
                                    </span>

                                    <strong>
                                        {{ $dropRemaining }}
                                    </strong>

                                </div>


                                <div>

                                    <span class="monitor-label font-mono">
                                        FIN
                                    </span>

                                    <strong>
                                        {{ $activeDrop->end_date?->format('d/m/Y H:i') ?? '—' }}
                                    </strong>

                                </div>

                            </div>


                            <div class="drop-progress">

                                <div class="drop-progress-header">

                                    <span class="monitor-label font-mono">
                                        PROGRESSION DU QUOTA
                                    </span>

                                    <span class="drop-progress-value font-mono">
                                        {{ $dropPercentage }}%
                                    </span>

                                </div>


                                <div class="drop-progress-track">

                                    <span
                                        class="drop-progress-fill"
                                        style="width: {{ min(100, max(0, $dropPercentage)) }}%;"
                                    ></span>

                                </div>

                            </div>


                            @if($dropCountdown)

                                <div class="drop-countdown">

                                    <span class="monitor-label font-mono">
                                        TEMPS RESTANT
                                    </span>


                                    <div class="drop-countdown-values">

                                        <div>

                                            <strong>
                                                {{ $dropCountdown['days'] }}
                                            </strong>

                                            <span class="font-mono">
                                                J
                                            </span>

                                        </div>


                                        <span class="countdown-separator">
                                            :
                                        </span>


                                        <div>

                                            <strong>
                                                {{ $dropCountdown['hours'] }}
                                            </strong>

                                            <span class="font-mono">
                                                H
                                            </span>

                                        </div>


                                        <span class="countdown-separator">
                                            :
                                        </span>


                                        <div>

                                            <strong>
                                                {{ $dropCountdown['minutes'] }}
                                            </strong>

                                            <span class="font-mono">
                                                MIN
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            @endif

                        @else

                            <div class="drop-monitor-empty">

                                <span class="monitor-label font-mono">
                                    QUOTA
                                </span>

                                <strong>
                                    NON DÉFINI
                                </strong>

                                <p>
                                    Aucun quota produit n'est actuellement
                                    défini pour ce Drop.
                                </p>

                            </div>

                        @endif

                    @else

                        <div class="drop-monitor-empty">

                            <strong>
                                AUCUN DROP ACTIF
                            </strong>

                            <p>
                                Aucun Drop n'est actuellement actif.
                            </p>

                        </div>

                    @endif

                </div>

            </section>


            {{-- =================================================
                 STOCKS CRITIQUES
            ================================================== --}}

            <section class="dashboard-card sidebar-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            02
                        </span>

                        <h2 class="card-title">
                            NIVEAUX DE STOCK CRITIQUES
                        </h2>

                    </div>


                    <span class="card-alert-badge font-mono">

                        {{ $criticalStockAlertsCount }}

                        {{ $criticalStockAlertsCount === 1 ? 'ALERTE' : 'ALERTES' }}

                    </span>

                </div>


                <div class="stock-alert-list">

                    @forelse($lowStockVariants as $alert)

                        <div class="stock-item">

                            <div
                                class="stock-item-icon"
                                aria-hidden="true"
                            >
                                ▪
                            </div>


                            <div class="stock-item-info">

                                <strong class="stock-item-name">
                                    {{ $alert['product_name'] }}
                                </strong>


                                <span class="stock-item-variant font-mono">

                                    @if(filled($alert['color']))

                                        {{ $alert['color'] }}

                                        <span class="variant-separator">
                                            •
                                        </span>

                                    @endif

                                    {{ $alert['size'] }}

                                </span>

                            </div>


                            <div class="stock-item-status">

                                <span
                                    class="stock-status {{ $alert['status_class'] }}"
                                >
                                    {{ $alert['status_label'] }}
                                </span>


                                <span class="stock-item-quantity font-mono">
                                    {{ $alert['label'] }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="stock-empty-state font-mono">
                            AUCUNE ALERTE DE STOCK
                        </div>

                    @endforelse

                </div>

            </section>


            {{-- =================================================
                 JOURNAL
            ================================================== --}}

            <section class="dashboard-card sidebar-card">

                <div class="card-header">

                    <div class="card-title-wrap">

                        <span class="card-index font-mono">
                            03
                        </span>

                        <h2 class="card-title">
                            JOURNAL D'ACTIVITÉ
                        </h2>

                    </div>

                </div>


                <div class="activity-list">

                    <div class="activity-empty-state">

                        <span class="activity-empty-icon">
                            —
                        </span>

                        <strong>
                            JOURNAL NON DISPONIBLE
                        </strong>

                        <span class="font-mono">
                            AUCUNE ACTIVITÉ HISTORISÉE
                        </span>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 ACTION RAPIDE
            ================================================== --}}

            <section class="dashboard-card quick-action-card">

                @if(Route::has('admin.whitelist.index'))

                    <a
                        href="{{ route('admin.whitelist.index') }}"
                        class="quick-action-link"
                    >

                        <span>
                            ✓ GÉRER LA WHITELIST
                        </span>

                        <span>
                            →
                        </span>

                    </a>

                @endif

            </section>

        </aside>

    </div>

</div>


<style>

.admin-dashboard-view {
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    padding: 32px 28px 80px;
    color: #f5f5f0;
}

.font-mono {
    font-family: monospace;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
    margin-bottom: 28px;
    border-bottom: 1px solid #222;
    padding-bottom: 24px;
}

.dashboard-kicker {
    color: #d32f2f;
    font-size: 10px;
    letter-spacing: .18em;
    margin-bottom: 8px;
}

.dashboard-title {
    margin: 0;
    font-size: 42px;
    line-height: 1;
    letter-spacing: .03em;
    text-transform: uppercase;
}

.dashboard-subtitle {
    margin: 10px 0 0;
    color: #777;
    font-size: 13px;
}

.dashboard-header-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #777;
    font-size: 10px;
    letter-spacing: .08em;
}

.server-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #aaa;
}

.server-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #2ecc71;
}

.header-separator {
    color: #333;
}

.dashboard-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 24px;
    overflow-x: auto;
}

.dashboard-nav-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 14px;
    border: 1px solid #222;
    color: #777;
    text-decoration: none;
    font-size: 9px;
    letter-spacing: .12em;
    white-space: nowrap;
}

.dashboard-nav-item:hover,
.dashboard-nav-item.active {
    color: #fff;
    border-color: #444;
}

.dashboard-nav-item.active {
    background: #141414;
}

.nav-count {
    min-width: 18px;
    padding: 2px 5px;
    text-align: center;
    background: #222;
    color: #aaa;
}

.kpi-metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.kpi-card {
    min-height: 125px;
    padding: 20px;
    background: #111;
    border: 1px solid #222;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.kpi-label,
.kpi-meta {
    color: #666;
    font-size: 9px;
    letter-spacing: .12em;
}

.kpi-value {
    font-size: 27px;
    letter-spacing: .02em;
}

.kpi-unit {
    font-size: 12px;
    color: #777;
}

.dashboard-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: start;
}

.grid-primary-column {
    min-width: 0;
}

.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.dashboard-card {
    background: #111;
    border: 1px solid #222;
    margin-bottom: 24px;
}

.sidebar-card {
    margin-bottom: 0;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #222;
}

.card-title-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-index {
    color: #d32f2f;
    font-size: 10px;
}

.card-title {
    margin: 0;
    font-size: 12px;
    letter-spacing: .12em;
    font-weight: 800;
}

.card-link {
    color: #888;
    text-decoration: none;
    font-size: 9px;
    letter-spacing: .1em;
}

.card-link:hover {
    color: #fff;
}

.card-badge-live {
    color: #2ecc71;
    font-size: 9px;
    letter-spacing: .1em;
}

.card-badge-neutral {
    color: #666;
}

.card-alert-badge {
    color: #d32f2f;
    border: 1px solid #441818;
    background: #220e0e;
    padding: 5px 7px;
    font-size: 8px;
    letter-spacing: .1em;
}

.table-responsive {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    padding: 12px 16px;
    color: #555;
    background: #0d0d0d;
    border-bottom: 1px solid #222;
    font-family: monospace;
    font-size: 8px;
    letter-spacing: .12em;
    text-align: left;
    white-space: nowrap;
}

.orders-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #1d1d1d;
    font-size: 12px;
    white-space: nowrap;
}

.customer-cell {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.customer-email {
    color: #666;
    font-size: 10px;
}

.wilaya-cell {
    color: #aaa;
}

.articles-count {
    color: #fff;
    font-family: monospace;
}

.order-date {
    color: #666;
    font-size: 9px;
}

.badge-status {
    display: inline-block;
    padding: 5px 7px;
    font-family: monospace;
    font-size: 8px;
    letter-spacing: .1em;
    font-weight: 700;
    white-space: nowrap;
}

.badge-status.pending {
    color: #e67e22;
    background: #21190c;
    border: 1px solid #443216;
}

.badge-status.to-ship {
    color: #f39c12;
    background: #21190c;
    border: 1px solid #443216;
}

.badge-status.shipped {
    color: #5dade2;
    background: #101c26;
    border: 1px solid #20394d;
}

.badge-status.delivered {
    color: #2ecc71;
    background: #0e1c12;
    border: 1px solid #1a3823;
}

.badge-status.cancelled {
    color: #d32f2f;
    background: #220e0e;
    border: 1px solid #441818;
}

.empty-orders-cell {
    padding: 36px 20px !important;
    color: #666;
    text-align: center;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .1em;
}

.sales-list,
.top-products-list,
.stock-alert-list,
.activity-list {
    padding: 4px 20px 16px;
}

.sales-row {
    display: grid;
    grid-template-columns: 55px minmax(80px, 1fr) 100px;
    align-items: center;
    gap: 12px;
    padding: 13px 0;
    border-bottom: 1px solid #1d1d1d;
}

.sales-day {
    color: #777;
    font-size: 9px;
}

.sales-bar-wrap {
    height: 4px;
    background: #1c1c1c;
}

.sales-bar {
    display: block;
    height: 100%;
    background: #d32f2f;
}

.sales-value {
    color: #aaa;
    font-size: 9px;
    text-align: right;
}

.top-product-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 14px 0;
    border-bottom: 1px solid #1d1d1d;
}

.top-product-name {
    font-size: 12px;
}

.top-product-sales {
    color: #777;
    font-size: 9px;
    white-space: nowrap;
}

.empty-state {
    padding: 25px 0;
    color: #666;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .08em;
}


/* =========================================================
   DROP MONITORING
========================================================= */

.drop-monitor {
    padding: 20px;
}

.drop-monitor-name {
    margin-bottom: 18px;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: .04em;
}

.drop-monitor-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 12px;
}

.drop-monitor-grid > div {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.monitor-label {
    color: #666;
    font-size: 8px;
    letter-spacing: .1em;
}

.drop-monitor-grid strong {
    font-family: monospace;
    font-size: 13px;
}

.drop-progress {
    margin-top: 22px;
}

.drop-progress-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
}

.drop-progress-value {
    color: #aaa;
    font-size: 8px;
}

.drop-progress-track {
    width: 100%;
    height: 5px;
    background: #1c1c1c;
    overflow: hidden;
}

.drop-progress-fill {
    display: block;
    height: 100%;
    background: #d32f2f;
}

.drop-countdown {
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #1d1d1d;
}

.drop-countdown-values {
    display: flex;
    align-items: baseline;
    gap: 7px;
    margin-top: 9px;
}

.drop-countdown-values > div {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.drop-countdown-values strong {
    font-family: monospace;
    font-size: 20px;
}

.drop-countdown-values span {
    color: #666;
    font-size: 7px;
    letter-spacing: .08em;
}

.countdown-separator {
    color: #444 !important;
    font-family: monospace;
    font-size: 15px !important;
}

.drop-monitor-empty {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 4px 0 6px;
}

.drop-monitor-empty strong {
    font-family: monospace;
    font-size: 13px;
}

.drop-monitor-empty p {
    margin: 0;
    color: #666;
    font-size: 10px;
    line-height: 1.5;
}


/* =========================================================
   STOCK
========================================================= */

.stock-alert-list {
    padding-top: 6px;
}

.stock-item {
    display: grid;
    grid-template-columns: 24px minmax(0, 1fr) auto;
    align-items: center;
    gap: 10px;
    padding: 14px 0;
    border-bottom: 1px solid #1d1d1d;
}

.stock-item:last-child {
    border-bottom: 0;
}

.stock-item-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #292929;
    color: #777;
    font-size: 10px;
}

.stock-item-info {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stock-item-name {
    overflow: hidden;
    color: #eee;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.stock-item-variant {
    color: #666;
    font-size: 8px;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.variant-separator {
    color: #444;
    padding: 0 3px;
}

.stock-item-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    text-align: right;
}

.stock-status {
    padding: 4px 6px;
    font-family: monospace;
    font-size: 7px;
    font-weight: 700;
    letter-spacing: .08em;
    white-space: nowrap;
}

.stock-status.exhausted {
    color: #d32f2f;
    background: #220e0e;
    border: 1px solid #441818;
}

.stock-status.critical {
    color: #e67e22;
    background: #21190c;
    border: 1px solid #443216;
}

.stock-item-quantity {
    color: #777;
    font-size: 8px;
    white-space: nowrap;
}

.stock-empty-state {
    padding: 30px 0;
    color: #666;
    text-align: center;
    font-size: 8px;
    letter-spacing: .1em;
}


/* =========================================================
   JOURNAL
========================================================= */

.activity-list {
    padding-top: 8px;
}

.activity-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 28px 10px;
    color: #666;
    text-align: center;
}

.activity-empty-icon {
    color: #444;
    font-family: monospace;
    font-size: 18px;
}

.activity-empty-state strong {
    color: #777;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .08em;
}

.activity-empty-state span:last-child {
    color: #4f4f4f;
    font-size: 8px;
    letter-spacing: .08em;
}


/* =========================================================
   ACTION RAPIDE
========================================================= */

.quick-action-card {
    padding: 0;
}

.quick-action-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 16px 18px;
    color: #aaa;
    text-decoration: none;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .1em;
}

.quick-action-link:hover {
    color: #fff;
    background: #161616;
}


@media (max-width: 1100px) {

    .kpi-metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-main-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-sidebar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 700px) {

    .admin-dashboard-view {
        padding: 24px 14px 60px;
    }

    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .dashboard-title {
        font-size: 32px;
    }

    .kpi-metrics-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-sidebar {
        display: flex;
    }

    .sales-row {
        grid-template-columns: 45px minmax(50px, 1fr);
    }

    .sales-value {
        grid-column: 2;
        text-align: left;
    }

    .drop-monitor-grid {
        grid-template-columns: 1fr 1fr;
    }

}

</style>

@endsection