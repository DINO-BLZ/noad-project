@extends('layouts.app')

@section('content')

<div class="admin-orders-page">

```
{{-- =========================================================
     01. BARRE SUPÉRIEURE OPÉRATIONNELLE & TÉLÉMÉTRIE
========================================================== --}}

<div class="ops-telemetry-strip">

    <div class="telemetry-left">

        <span class="ops-badge">
            OPS CORE V4.2
        </span>

        <span class="ops-sub">
            POSTE DE CONTRÔLE LOGISTIQUE
        </span>

        <span class="sep">•</span>

        <span class="carrier-sync">
            <span class="dot-carrier-live">●</span>
            YALIDINE / ALG EXPRESS SYNC : ONLINE
        </span>
    </div>

    <div class="telemetry-right">

        <span class="sys-slogan">
            DEFEND YOUR PRINCIPLES
        </span>

        <span class="carrier-clock">
            {{ now()->format('H:i:s') }} UTC+1
        </span>
    </div>
</div>


{{-- =========================================================
     02. EN-TÊTE DE PAGE & ACTIONS GLOBALES
========================================================== --}}

<header class="orders-head-section">

    <div class="head-titles">

        <h1 class="orders-page-title">
            NOAD — COMMANDES &amp; EXPÉDITIONS
        </h1>

        <span class="national-coverage-tag">
            TERRITOIRE NATIONAL : 58 WILAYAS
        </span>
    </div>

    <div class="head-actions-stack">

        <button
            type="button"
            class="btn-ops-outline"
        >
            <span class="icon">⤓</span>
            EXPORTER MANIFESTE (.CSV)
        </button>

        <button
            type="button"
            class="btn-ops-solid"
        >
            <span class="icon">🖨</span>
            BORDEREAUX DE TRANSPORT
            ({{ $toShipOrdersCount ?? 0 }})
        </button>
    </div>
</header>


{{-- =========================================================
     03. KPIs LOGISTIQUES
========================================================== --}}

<section class="orders-kpi-grid">

    {{-- KPI 1 --}}

    <div class="ops-kpi-card">

        <div class="kpi-top">

            <span class="kpi-label">
                VOLUME GLOBAL
            </span>

            <span class="kpi-icon">
                🗂
            </span>
        </div>

        <div class="kpi-value-row">

            <span class="kpi-val">
                {{ $totalOrdersCount ?? 0 }}
            </span>

            <span class="kpi-sub-unit">
                COMMANDES
            </span>
        </div>

        <div class="kpi-foot-row">

            <span class="meta-tag">
                CYCLE EN COURS
            </span>

            <span class="stat-growth positive">
                +14% S/S-1
            </span>
        </div>
    </div>


    {{-- KPI 2 --}}

    <div class="ops-kpi-card alert-card">

        <div class="kpi-top">

            <span class="kpi-label">
                À EXPÉDIER
            </span>

            <span class="kpi-icon alert-col">
                📦
            </span>
        </div>

        <div class="kpi-value-row">

            <span class="kpi-val alert-val">
                {{ $toShipOrdersCount ?? 0 }}
            </span>

            <span class="kpi-sub-unit">
                COLIS HUB
            </span>
        </div>

        <div class="kpi-foot-row">

            <span class="meta-tag">
                DÉPART ALGER HUB
            </span>

            <span class="stat-growth alert-tag">
                CRITIQUE &lt; 3H
            </span>
        </div>
    </div>


    {{-- KPI 3 --}}

    <div class="ops-kpi-card">

        <div class="kpi-top">

            <span class="kpi-label">
                EN TRANSIT EXPÉDITIONS
            </span>

            <span class="kpi-icon">
                🚚
            </span>
        </div>

        <div class="kpi-value-row">

            <span class="kpi-val">
                {{ $inTransitCount ?? 0 }}
            </span>

            <span class="kpi-sub-unit">
                SUR ROUTE
            </span>
        </div>

        <div class="kpi-foot-row">

            <span class="meta-tag">
                ALG EXPRESS / YALIDINE
            </span>

            <span class="meta-white">
                24 WILAYAS
            </span>
        </div>
    </div>


    {{-- KPI 4 --}}

    <div class="ops-kpi-card">

        <div class="kpi-top">

            <span class="kpi-label">
                LIVRÉES &amp; ENCAISSÉES
            </span>

            <span class="kpi-icon">
                ✓
            </span>
        </div>

        <div class="kpi-value-row">

            <span class="kpi-val">
                {{ $deliveredCount ?? 0 }}
            </span>

            <span class="kpi-sub-unit">
                COMPLÉTÉES
            </span>
        </div>

        <div class="kpi-foot-row">

            <span class="meta-tag">
                TAUX SUCCÈS 1ST RUN
            </span>

            <span class="stat-growth positive">
                96.8%
            </span>
        </div>
    </div>


    {{-- KPI 5 --}}

    <div class="ops-kpi-card">

        <div class="kpi-top">

            <span class="kpi-label">
                COD EN ATTENTE
            </span>

            <span class="kpi-icon">
                💵
            </span>
        </div>

        <div class="kpi-value-row">

            <span class="kpi-val">
                {{ number_format($pendingCodAmount ?? 0, 0, ',', ' ') }}
            </span>

            <span class="kpi-currency">
                DA
            </span>
        </div>

        <div class="kpi-foot-row">

            <span class="meta-tag">
                À RAPPROCHER BANQUE
            </span>

            <span class="stat-growth alert-tag">
                {{ $pendingVouchersCount ?? 0 }} BORDEREAUX
            </span>
        </div>
    </div>

</section>


{{-- =========================================================
     04. FILTRES AVANCÉS & ONGLETS
========================================================== --}}

<div class="filters-master-block">

    {{-- Onglets --}}

    <div class="status-tabs-row">

        <div class="tabs-list">

            <a
                href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                class="tab-btn {{ !request('status') ? 'active' : '' }}"
            >
                TOUS

                <span class="tab-count">
                    {{ $totalOrdersCount ?? 0 }}
                </span>
            </a>

            <a
                href="{{ request()->fullUrlWithQuery(['status' => 'to_ship']) }}"
                class="tab-btn {{ request('status') === 'to_ship' ? 'active' : '' }}"
            >
                À EXPÉDIER

                <span class="tab-count alert">
                    {{ $toShipOrdersCount ?? 0 }}
                </span>
            </a>

            <a
                href="{{ request()->fullUrlWithQuery(['status' => 'in_transit']) }}"
                class="tab-btn {{ request('status') === 'in_transit' ? 'active' : '' }}"
            >
                EN TRANSIT

                <span class="tab-count">
                    {{ $inTransitCount ?? 0 }}
                </span>
            </a>

            <a
                href="{{ request()->fullUrlWithQuery(['status' => 'delivered']) }}"
                class="tab-btn {{ request('status') === 'delivered' ? 'active' : '' }}"
            >
                LIVRÉES

                <span class="tab-count">
                    {{ $deliveredCount ?? 0 }}
                </span>
            </a>

            <a
                href="{{ request()->fullUrlWithQuery(['status' => 'incident']) }}"
                class="tab-btn {{ request('status') === 'incident' ? 'active' : '' }}"
            >
                INCIDENTS / RETOURS

                <span class="tab-count alert-bg">
                    {{ $incidentCount ?? 0 }}
                </span>
            </a>

        </div>

        <div class="sync-indicator-block">

            <span class="sync-icon">
                ↺
            </span>

            <span class="sync-label">
                SYNC AUTOMATIQUE : 45s
            </span>

        </div>

    </div>


    {{-- Recherche + filtres --}}

    <form
        method="GET"
        action="{{ route('admin.orders.index') }}"
        class="search-filter-controls"
    >

        {{-- Recherche --}}

        <div class="search-input-group">

            <span class="search-lens">
                🔍
            </span>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="RECHERCHER PAR N° COMMANDE, NOM CLIENT, TÉLÉPHONE (+213)..."
                class="input-search-ops"
            >

        </div>


        {{-- Wilaya --}}

        <div class="select-group">

            <select
                name="wilaya"
                class="select-ops"
            >

                <option value="">
                    WILAYAS D'EXPÉDITION (58 TOUTES)
                </option>

                <option
                    value="16"
                    {{ request('wilaya') == '16' ? 'selected' : '' }}
                >
                    16 — ALGER
                </option>

                <option
                    value="31"
                    {{ request('wilaya') == '31' ? 'selected' : '' }}
                >
                    31 — ORAN
                </option>

                <option
                    value="25"
                    {{ request('wilaya') == '25' ? 'selected' : '' }}
                >
                    25 — CONSTANTINE
                </option>

                <option
                    value="09"
                    {{ request('wilaya') == '09' ? 'selected' : '' }}
                >
                    09 — BLIDA
                </option>

                <option
                    value="19"
                    {{ request('wilaya') == '19' ? 'selected' : '' }}
                >
                    19 — SÉTIF
                </option>

                <option
                    value="30"
                    {{ request('wilaya') == '30' ? 'selected' : '' }}
                >
                    30 — OUARGLA / HASSI MESSAOUD
                </option>

            </select>

        </div>


        {{-- Drop --}}

        <div class="select-group">

            <select
                name="drop"
                class="select-ops"
            >

                <option value="">
                    TOUS LES DROPS
                </option>

                <option
                    value="1"
                    {{ request('drop') == '1' ? 'selected' : '' }}
                >
                    DROP 01 — THE RESISTANCE
                </option>

                <option
                    value="2"
                    {{ request('drop') == '2' ? 'selected' : '' }}
                >
                    DROP 02 — URBAN ARMOUR
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn-more-filters"
        >

            <span class="icon">
                ⚙
            </span>

            FILTRER

        </button>

    </form>

</div>


{{-- =========================================================
     05. TABLEAU DES COMMANDES
========================================================== --}}

<div class="orders-table-wrapper">

    <table class="orders-tactical-table">

        <thead>

            <tr>

                <th class="col-check">
                    <input
                        type="checkbox"
                        id="select-all"
                        class="custom-check"
                    >
                </th>

                <th>
                    COMMANDE &amp; HEURE
                </th>

                <th>
                    DESTINATAIRE &amp; DESTINATION
                </th>

                <th>
                    ARTICLES &amp; CONTENU
                </th>

                <th>
                    TOTAL TTC &amp; RÈGLEMENT
                </th>

                <th>
                    STATUT LOGISTIQUE
                </th>

                <th class="text-right">
                    ACTIONS
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($orders as $order)

                <tr>

                    {{-- Checkbox --}}

                    <td class="col-check">

                        <input
                            type="checkbox"
                            name="selected_orders[]"
                            value="{{ $order->id }}"
                            class="custom-check order-checkbox"
                        >

                    </td>


                    {{-- Commande --}}

                    <td>

                        <div class="order-id-stack">

                            <div class="id-row">

                                <span class="dot-red-select">
                                    ●
                                </span>

                                <span class="order-hash">
                                    {{ $order->reference ?? ('ND-2026-' . $order->id) }}
                                </span>

                                <span class="hub-badge">
                                    HUB {{ sprintf('%02d', $order->wilaya_code ?? 16) }}
                                </span>

                            </div>

                            <span class="order-date-meta">

                                @if($order->created_at)

                                    {{ $order->created_at->diffForHumans() }}
                                    •
                                    {{ $order->created_at->format('H:i') }} CET

                                @else

                                    DATE INCONNUE

                                @endif

                            </span>

                            <span class="tracking-code">
                                TRACK:
                                {{ $order->tracking_number ?? 'NON ASSIGNÉ' }}
                            </span>

                        </div>

                    </td>


                    {{-- Destinataire --}}

                    <td>

                        <div class="dest-cell">

                            <div class="dest-avatar-row">

                                <span class="avatar-tag">

                                    {{ strtoupper(
                                        substr(
                                            $order->fullname ?? 'NA',
                                            0,
                                            2
                                        )
                                    ) }}

                                </span>

                                <div class="dest-info">

                                    <span class="dest-name">

                                        {{ $order->fullname ?? 'CLIENT INCONNU' }}

                                        <span class="wilaya-pill">

                                            {{ sprintf('%02d', $order->wilaya_code ?? 16) }}

                                            —

                                            {{ strtoupper($order->city ?? 'ALGER') }}

                                        </span>

                                    </span>

                                    <span class="dest-address">
                                        {{ $order->address ?? 'Adresse non renseignée' }}
                                    </span>

                                    <span class="dest-phone">
                                        {{ $order->phone ?? 'Téléphone non renseigné' }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </td>


                    {{-- Articles --}}

                    <td>

                        <div class="items-cell">

                            @php

                                $firstItem = $order->items->first();

                                $secondItem = $order->items->skip(1)->first();

                            @endphp

                            <span class="item-title">
                                {{ $firstItem?->product?->name ?? 'ARTICLE NOAD' }}
                            </span>

                            @if($secondItem)

                                <span class="item-secondary">
                                    + {{ $secondItem->product?->name ?? 'ARTICLE' }}
                                </span>

                            @endif

                            <span class="item-count-meta">

                                {{ $order->items->count() }}

                                ARTICLE{{ $order->items->count() > 1 ? 'S' : '' }}

                            </span>

                        </div>

                    </td>


                    {{-- Prix --}}

                    <td>

                        <div class="pricing-cell">

                            <span class="total-val">

                                {{ number_format(
                                    $order->total ?? 0,
                                    0,
                                    ',',
                                    ' '
                                ) }}

                                DA

                            </span>

                            @if(($order->payment_method ?? '') === 'cod')

                                <span class="payment-method cod">
                                    ● CASH À LA LIVRAISON (COD)
                                </span>

                            @else

                                <span class="payment-method cib">
                                    ● {{ strtoupper($order->payment_method ?? 'PAIEMENT') }}
                                </span>

                            @endif

                            <span class="shipping-fee-meta">

                                Frais d'envoi :

                                {{ number_format(
                                    $order->shipping_cost ?? 0,
                                    0,
                                    ',',
                                    ' '
                                ) }}

                                DA

                            </span>

                        </div>

                    </td>


                    {{-- Statut --}}

                    <td>

                        @php

                            $statusClasses = [
                                'to_ship'    => 'to-prepare',
                                'in_transit' => 'in-transit',
                                'delivered'  => 'delivered',
                                'cancelled'  => 'incident',
                                'incident'   => 'incident',
                            ];

                            $statusLabels = [
                                'to_ship'    => 'À EXPÉDIER',
                                'in_transit' => 'EN TRANSIT',
                                'delivered'  => 'LIVRÉ & ENCAISSÉ',
                                'cancelled'  => 'ANNULÉE',
                                'incident'   => 'INCIDENT',
                            ];

                            $statusClass =
                                $statusClasses[$order->status?->value ?? '']
                                ?? 'to-prepare';

                            $statusLabel =
                                $statusLabels[$order->status?->value ?? '']
                                ?? strtoupper($order->status?->value ?? 'À PRÉPARER');

                        @endphp

                        <span class="status-tag {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>

                        <span class="sub-status-label">
                            {{ $order->status_sub ?? 'SUIVI LOGISTIQUE' }}
                        </span>

                    </td>


                    {{-- Actions --}}

                    <td class="text-right">

                        <div class="action-buttons-group">

                            <a
                                href="{{ route('admin.orders.show', $order->id) }}"
                                class="btn-action-icon"
                                title="Voir le détail"
                            >
                                👁
                            </a>

                            <button
                                type="button"
                                class="btn-action-icon"
                                title="Bordereau"
                            >
                                📄
                            </button>

                        </div>

                    </td>

                </tr>

            @empty

                {{-- Aucun résultat --}}

                <tr>

                    <td
                        colspan="7"
                        class="empty-orders"
                    >

                        <div class="empty-orders-content">

                            <span class="empty-icon">
                                ◌
                            </span>

                            <strong>
                                AUCUNE COMMANDE TROUVÉE
                            </strong>

                            <span>
                                Aucun résultat ne correspond aux filtres actuels.
                            </span>

                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- Pagination Laravel --}}

    @if($orders instanceof \Illuminate\Pagination\AbstractPaginator)

        <div class="table-pagination-footer">

            <div class="pag-left">

                <span class="pag-counter">

                    AFFICHAGE :

                    {{ $orders->firstItem() ?? 0 }}

                    -

                    {{ $orders->lastItem() ?? 0 }}

                    SUR

                    {{ $orders->total() }}

                    EXPÉDITIONS

                </span>

                <span class="pag-sep">
                    /
                </span>

                <span class="pag-lines-select">

                    LIGNES PAR PAGE :

                    <strong class="text-white">
                        {{ $orders->perPage() }}
                    </strong>

                </span>

            </div>

            <div class="pag-right">

                {{ $orders->onEachSide(1)->links() }}

            </div>

        </div>

    @endif

</div>


{{-- =========================================================
     06. TÉLÉMÉTRIE TRANSPORTEURS & ALERTES
========================================================== --}}

<section class="bottom-logistics-grid">


    {{-- =====================================================
         TRANSPORTEURS
    ====================================================== --}}

    <div class="telemetry-box">

        <div class="box-head">

            <div class="box-head-title">

                <span class="network-icon">
                    ⬡
                </span>

                <h2 class="box-title">
                    TÉLÉMÉTRIE TRANSPORTEURS • ALGÉRIE 58 WILAYAS
                </h2>

            </div>

            <span class="api-tag">
                PASSERELLE API EN TEMPS RÉEL
            </span>

        </div>


        <div class="carriers-sub-grid">


            {{-- Algérie Express --}}

            <div class="carrier-node-card">

                <div class="carrier-node-head">

                    <span class="carrier-brand-name">
                        ALGÉRIE EXPRESS
                    </span>

                    <span class="carrier-dot-ok">
                        ●
                    </span>

                </div>

                <span class="carrier-zone">
                    Région Centre &amp; Grand Alger
                </span>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        CHARGE LOGISTIQUE
                    </span>

                    <span class="metric-val">
                        82%
                    </span>

                </div>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        TEMPS MOYEN
                    </span>

                    <span class="metric-val highlight">
                        18 HEURES
                    </span>

                </div>

            </div>


            {{-- Yalidine --}}

            <div class="carrier-node-card">

                <div class="carrier-node-head">

                    <span class="carrier-brand-name">
                        YALIDINE EXPRESS
                    </span>

                    <span class="carrier-dot-ok">
                        ●
                    </span>

                </div>

                <span class="carrier-zone">
                    Réseau 58 Wilayas &amp; Relais
                </span>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        FLUX EN COURS
                    </span>

                    <span class="metric-val">
                        {{ $inTransitCount ?? 0 }} COLIS
                    </span>

                </div>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        TAUX LIVRAISON J+1
                    </span>

                    <span class="metric-val highlight">
                        94.1%
                    </span>

                </div>

            </div>


            {{-- Grand Sud --}}

            <div class="carrier-node-card">

                <div class="carrier-node-head">

                    <span class="carrier-brand-name">
                        FLEET GRAND SUD
                    </span>

                    <span class="carrier-dot-warn">
                        ●
                    </span>

                </div>

                <span class="carrier-zone">
                    Wilayas 30, 47, 11, 33, 58
                </span>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        DÉLAI MOYEN
                    </span>

                    <span class="metric-val">
                        48H - 72H
                    </span>

                </div>

                <div class="carrier-metric-row">

                    <span class="metric-lbl">
                        STABILITÉ RÉSEAU
                    </span>

                    <span class="metric-val warning">
                        FLUIDE
                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         ALERTES
    ====================================================== --}}

    <div class="alerts-box">

        <div class="box-head">

            <div class="box-head-title">

                <span class="alert-icon-head">
                    ⚠
                </span>

                <h2 class="box-title">
                    ALERTES &amp; RETENUES
                </h2>

            </div>

            <span class="badge-alert-count">
                {{ $incidentCount ?? 0 }} ACTIVES
            </span>

        </div>


        <div class="alerts-list-content">

            @if(($incidentCount ?? 0) > 0)

                <div class="alert-item-row">

                    <div class="alert-item-icon">
                        ⚡
                    </div>

                    <div class="alert-item-body">

                        <div class="alert-item-title-row">

                            <span class="alert-client-name">
                                INCIDENTS LOGISTIQUES
                            </span>

                        </div>

                        <p class="alert-client-desc">
                            Des commandes nécessitent une intervention administrative.
                        </p>

                        <span class="alert-delay-tag">
                            ACTION REQUISE
                        </span>

                    </div>

                </div>

            @else

                <div class="alert-item-row no-alert">

                    <div class="alert-item-icon success">
                        ✓
                    </div>

                    <div class="alert-item-body">

                        <div class="alert-item-title-row">

                            <span class="alert-client-name">
                                SYSTÈME NOMINAL
                            </span>

                        </div>

                        <p class="alert-client-desc">
                            Aucun incident logistique actif.
                        </p>

                    </div>

                </div>

            @endif

        </div>


        <div class="alerts-box-footer">

            <button
                type="button"
                class="btn-open-incident-mgr"
            >

                <span class="icon">
                    🗂
                </span>

                OUVRIR LE GESTIONNAIRE D'INCIDENTS

            </button>

        </div>

    </div>

</section>
```

</div>

{{-- =============================================================
JAVASCRIPT
============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const selectAll = document.getElementById('select-all');

    if (!selectAll) {
        return;
    }

    selectAll.addEventListener('change', function () {

        document
            .querySelectorAll('.order-checkbox')
            .forEach(function (checkbox) {

                checkbox.checked = selectAll.checked;

            });

    });

});

</script>

{{-- =============================================================
CSS
============================================================= --}}

<style>

.admin-orders-page {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #0c0c0c;
}

.admin-orders-page *,
.admin-orders-page *::before,
.admin-orders-page *::after {
    box-sizing: border-box;
}

.admin-orders-page a {
    color: inherit;
    text-decoration: none;
}


/* =============================================================
   01. TELEMETRY
============================================================= */

.ops-telemetry-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 10px;
    margin-bottom: 24px;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.1em;
    flex-wrap: wrap;
    gap: 12px;
}

.telemetry-left,
.telemetry-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.telemetry-right {
    gap: 16px;
}

.ops-badge {
    background-color: var(--accent, #d32f2f);
    color: #fff;
    font-weight: 800;
    padding: 2px 6px;
    font-size: 9px;
}

.ops-sub {
    color: #888;
}

.sep {
    color: #444;
}

.carrier-sync {
    color: #aaa;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dot-carrier-live {
    color: #2ecc71;
    font-size: 9px;
}

.sys-slogan {
    color: #777;
    font-weight: bold;
    letter-spacing: 0.15em;
}

.carrier-clock {
    color: #555;
}


/* =============================================================
   02. HEADER
============================================================= */

.orders-head-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 24px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 20px;
}

.orders-page-title {
    font-size: clamp(26px, 3.8vw, 42px);
    font-weight: 900;
    letter-spacing: 0.03em;
    line-height: 1.08;
    margin: 0 0 6px;
    color: #fff;
    text-transform: uppercase;
}

.national-coverage-tag {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.15em;
    color: #777;
    font-weight: bold;
}

.head-actions-stack {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-ops-outline,
.btn-ops-solid {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ops-outline {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    color: #ccc;
    padding: 12px 18px;
    font-family: monospace;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
}

.btn-ops-outline:hover {
    color: #fff;
    border-color: #666;
}

.btn-ops-solid {
    background-color: #fff;
    color: #000;
    border: 1px solid #fff;
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.12em;
}

.btn-ops-solid:hover {
    background-color: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
    color: #fff;
}


/* =============================================================
   03. KPI
============================================================= */

.orders-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.ops-kpi-card {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 120px;
}

.ops-kpi-card.alert-card {
    border-left: 3px solid var(--accent, #d32f2f);
}

.kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.kpi-label {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.12em;
    color: #777;
    text-transform: uppercase;
}

.kpi-icon {
    font-size: 11px;
    opacity: 0.5;
}

.kpi-icon.alert-col {
    color: var(--accent, #d32f2f);
    opacity: 1;
}

.kpi-value-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 8px;
}

.kpi-val {
    font-family: monospace;
    font-size: 32px;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}

.kpi-val.alert-val {
    color: var(--accent, #d32f2f);
}

.kpi-sub-unit,
.kpi-currency {
    font-family: monospace;
    color: #777;
}

.kpi-sub-unit {
    font-size: 9px;
    letter-spacing: 0.08em;
}

.kpi-currency {
    font-size: 14px;
    font-weight: 800;
    color: #888;
}

.kpi-foot-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-family: monospace;
    font-size: 9px;
    border-top: 1px solid #181818;
    padding-top: 8px;
}

.meta-tag {
    color: #666;
}

.meta-white {
    color: #fff;
    font-weight: bold;
}

.stat-growth.positive {
    color: #2ecc71;
    font-weight: bold;
}

.stat-growth.alert-tag {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}


/* =============================================================
   04. FILTERS
============================================================= */

.filters-master-block {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    margin-bottom: 24px;
}

.status-tabs-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border, #242424);
    padding: 0 16px;
    overflow-x: auto;
}

.tabs-list {
    display: flex;
    gap: 12px;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 14px 12px;
    font-family: monospace;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #777;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn:hover {
    color: #fff;
}

.tab-btn.active {
    color: #fff;
    border-bottom-color: var(--accent, #d32f2f);
}

.tab-count {
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    padding: 1px 5px;
    font-size: 9px;
    color: #aaa;
}

.tab-count.alert {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}

.tab-count.alert-bg {
    background-color: var(--accent, #d32f2f);
    color: #fff;
    font-weight: bold;
}

.sync-indicator-block {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: monospace;
    font-size: 9px;
    color: #666;
    white-space: nowrap;
}

.search-filter-controls {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 12px;
    padding: 16px;
}

.search-input-group {
    display: flex;
    align-items: center;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 0 12px;
}

.search-lens {
    color: #666;
    font-size: 11px;
    margin-right: 8px;
}

.input-search-ops {
    background: transparent;
    border: none;
    color: #fff;
    font-family: monospace;
    font-size: 10px;
    padding: 10px 0;
    width: 100%;
    outline: none;
    letter-spacing: 0.05em;
}

.select-group select {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #fff;
    font-family: monospace;
    font-size: 10px;
    padding: 10px 12px;
    width: 100%;
    outline: none;
    cursor: pointer;
}

.btn-more-filters {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #ccc;
    padding: 10px 16px;
    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: all 0.2s;
}

.btn-more-filters:hover {
    color: #fff;
    border-color: #666;
}


/* =============================================================
   05. TABLE
============================================================= */

.orders-table-wrapper {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    margin-bottom: 28px;
    overflow-x: auto;
}

.orders-tactical-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
    font-family: monospace;
    font-size: 11px;
    text-align: left;
}

.orders-tactical-table th {
    background-color: #0c0c0c;
    padding: 12px 16px;
    color: #666;
    font-size: 9px;
    letter-spacing: 0.12em;
    border-bottom: 1px solid var(--border, #242424);
    font-weight: 700;
    white-space: nowrap;
}

.orders-tactical-table td {
    padding: 16px;
    border-bottom: 1px solid #161616;
    vertical-align: middle;
}

.orders-tactical-table tr:hover td {
    background-color: #141414;
}

.col-check {
    width: 42px;
    text-align: center;
}

.custom-check {
    accent-color: var(--accent, #d32f2f);
    cursor: pointer;
}

.order-id-stack {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.id-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.dot-red-select {
    color: var(--accent, #d32f2f);
    font-size: 8px;
}

.order-hash {
    font-weight: 800;
    color: #fff;
    font-size: 12px;
}

.hub-badge {
    background-color: #1a1a1a;
    border: 1px solid #333;
    padding: 1px 5px;
    font-size: 8px;
    color: #aaa;
}

.order-date-meta {
    font-size: 9px;
    color: #666;
}

.tracking-code {
    font-size: 8px;
    color: #555;
    letter-spacing: 0.05em;
}


/* =============================================================
   DESTINATION
============================================================= */

.dest-avatar-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-tag {
    width: 28px;
    height: 28px;
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 800;
    color: #aaa;
    flex-shrink: 0;
}

.dest-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.dest-name {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.05em;
}

.wilaya-pill {
    background-color: #1a1a1a;
    border: 1px solid #333;
    padding: 1px 5px;
    font-size: 8px;
    color: #fff;
    font-family: monospace;
    margin-left: 6px;
}

.dest-address,
.dest-phone {
    font-size: 9px;
}

.dest-address {
    color: #777;
}

.dest-phone {
    color: #aaa;
    font-weight: bold;
}


/* =============================================================
   ITEMS
============================================================= */

.items-cell,
.pricing-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.item-title {
    font-size: 11px;
    font-weight: 700;
    color: #fff;
}

.item-secondary {
    font-size: 9px;
    color: #777;
}

.item-count-meta,
.shipping-fee-meta {
    font-size: 8px;
    color: #555;
}

.total-val {
    font-size: 13px;
    font-weight: 900;
    color: #fff;
}

.payment-method {
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 0.08em;
}

.payment-method.cod {
    color: #e5e5e5;
}

.payment-method.cib {
    color: #2ecc71;
}


/* =============================================================
   STATUS
============================================================= */

.status-tag {
    display: inline-block;
    padding: 3px 8px;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.1em;
    border: 1px solid transparent;
}

.status-tag.to-prepare {
    background-color: rgba(211, 47, 47, 0.15);
    border-color: var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
}

.status-tag.in-transit {
    background-color: #121c24;
    border-color: #3498db;
    color: #3498db;
}

.status-tag.delivered {
    background-color: rgba(46, 204, 113, 0.12);
    border-color: #2ecc71;
    color: #2ecc71;
}

.status-tag.incident {
    background-color: var(--accent, #d32f2f);
    color: #fff;
}

.sub-status-label {
    display: block;
    font-size: 8px;
    color: #666;
    margin-top: 3px;
    letter-spacing: 0.05em;
}


/* =============================================================
   ACTIONS
============================================================= */

.text-right {
    text-align: right;
}

.action-buttons-group {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.btn-action-icon {
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    color: #aaa;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-action-icon:hover {
    color: #fff;
    border-color: #666;
}


/* =============================================================
   EMPTY STATE
============================================================= */

.empty-orders {
    padding: 60px 20px !important;
    text-align: center !important;
}

.empty-orders-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #666;
}

.empty-orders-content strong {
    color: #fff;
    font-family: monospace;
    font-size: 12px;
}

.empty-orders-content span {
    font-family: monospace;
    font-size: 9px;
}

.empty-icon {
    font-size: 28px !important;
    color: #444;
}


/* =============================================================
   PAGINATION
============================================================= */

.table-pagination-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-top: 1px solid var(--border, #242424);
    font-family: monospace;
    font-size: 9px;
    color: #666;
    flex-wrap: wrap;
    gap: 12px;
}

.pag-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pag-right nav {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pag-right nav svg {
    width: 14px;
    height: 14px;
}

.pag-right nav a,
.pag-right nav span {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #aaa;
    padding: 4px 8px;
    font-size: 9px;
}


/* =============================================================
   06. LOGISTICS
============================================================= */

.bottom-logistics-grid {
    display: grid;
    grid-template-columns: 1.3fr 0.7fr;
    gap: 24px;
}

.telemetry-box,
.alerts-box {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    padding: 20px;
}

.box-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 12px;
    margin-bottom: 16px;
}

.box-head-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.network-icon,
.alert-icon-head {
    color: var(--accent, #d32f2f);
    font-size: 13px;
}

.box-title {
    font-size: 13px;
    font-weight: 900;
    letter-spacing: 0.1em;
    margin: 0;
    color: #fff;
    text-transform: uppercase;
}

.api-tag {
    font-family: monospace;
    font-size: 8px;
    color: #2ecc71;
    letter-spacing: 0.1em;
}

.badge-alert-count {
    background-color: rgba(211, 47, 47, 0.15);
    border: 1px solid var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
    font-family: monospace;
    font-size: 8px;
    font-weight: bold;
    padding: 2px 6px;
}


/* =============================================================
   CARRIERS
============================================================= */

.carriers-sub-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.carrier-node-card {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.carrier-node-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.carrier-brand-name {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
}

.carrier-dot-ok {
    color: #2ecc71;
    font-size: 9px;
}

.carrier-dot-warn {
    color: #f39c12;
    font-size: 9px;
}

.carrier-zone {
    font-family: monospace;
    font-size: 8px;
    color: #666;
    margin-bottom: 8px;
}

.carrier-metric-row {
    display: flex;
    justify-content: space-between;
    font-family: monospace;
    font-size: 9px;
}

.metric-lbl {
    color: #777;
}

.metric-val {
    color: #fff;
    font-weight: bold;
}

.metric-val.highlight {
    color: #2ecc71;
}

.metric-val.warning {
    color: #f39c12;
}


/* =============================================================
   ALERTS
============================================================= */

.alerts-list-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.alert-item-row {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 12px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.alert-item-row.no-alert {
    border-color: rgba(46, 204, 113, 0.25);
}

.alert-item-icon {
    color: var(--accent, #d32f2f);
    font-size: 13px;
    flex-shrink: 0;
}

.alert-item-icon.success {
    color: #2ecc71;
}

.alert-item-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.alert-client-name {
    font-family: monospace;
    font-size: 10px;
    font-weight: 800;
    color: #fff;
}

.alert-client-desc {
    font-size: 10px;
    color: #888;
    margin: 0;
    line-height: 1.35;
}

.alert-delay-tag {
    font-family: monospace;
    font-size: 8px;
    color: var(--accent, #d32f2f);
    font-weight: bold;
    margin-top: 4px;
}

.btn-open-incident-mgr {
    width: 100%;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #ccc;
    padding: 10px;
    font-family: monospace;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 0.1em;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-open-incident-mgr:hover {
    color: #fff;
    border-color: #666;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1200px) {

    .orders-kpi-grid {
        grid-template-columns: repeat(3, 1fr);
    }

}

@media (max-width: 1050px) {

    .bottom-logistics-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 900px) {

    .search-filter-controls {
        grid-template-columns: 1fr;
    }

    .status-tabs-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
        padding-bottom: 8px;
    }

    .sync-indicator-block {
        padding-bottom: 4px;
    }

}

@media (max-width: 768px) {

    .admin-orders-page {
        padding: 12px 14px 60px;
    }

    .orders-kpi-grid {
        grid-template-columns: 1fr;
    }

    .head-actions-stack {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .btn-ops-outline,
    .btn-ops-solid {
        justify-content: center;
        width: 100%;
    }

    .telemetry-left,
    .telemetry-right {
        width: 100%;
        flex-wrap: wrap;
    }

    .carriers-sub-grid {
        grid-template-columns: 1fr;
    }

}

</style>

@endsection
