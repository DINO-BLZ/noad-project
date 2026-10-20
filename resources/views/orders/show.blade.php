@extends('layouts.app')

@section('title', 'Commande ' . $order->formatted_reference . ' — NOAD')
@section('robots', 'noindex, nofollow')

@section('content')

@php
    use App\Enums\OrderStatus;

    $status = $order->status;
    $isCancelled = $status === OrderStatus::Cancelled;

    // Calcul de l'avancement du tracking stepper
    $step1Completed = true; // Enregistrement toujours fait
    $step2Completed = in_array($status, [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered], true);
    $step2Current = $status === OrderStatus::Pending;
    $step3Completed = in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true);
    $step3Current = $status === OrderStatus::Paid;
    $step4Completed = $status === OrderStatus::Delivered;
    $step4Current = $status === OrderStatus::Shipped;

    $statusBadgeClass = match($status) {
        OrderStatus::Pending => 'badge--pending',
        OrderStatus::Paid => 'badge--paid',
        OrderStatus::Shipped => 'badge--shipped',
        OrderStatus::Delivered => 'badge--delivered',
        OrderStatus::Cancelled => 'badge--cancelled',
        default => 'badge--default',
    };

    $statusTitle = match($status) {
        OrderStatus::Pending => 'COMMANDE EN COURS DE VALIDATION',
        OrderStatus::Paid => 'COMMANDE CONFIRMÉE — PRÉPARATION EN COURS',
        OrderStatus::Shipped => 'COMMANDE EXPÉDIÉE — EN COURS D\'ACHEMINEMENT',
        OrderStatus::Delivered => 'COMMANDE LIVRÉE AVEC SUCCÈS',
        OrderStatus::Cancelled => 'COMMANDE ANNULÉE',
        default => strtoupper($status?->value ?? 'INCONNU'),
    };
@endphp

<div class="order-detail-page">

    {{-- BANDE TECHNIQUE --}}
    <div class="top-strip">
        <div class="container-detail strip-flex font-mono">
            <div>
                <span class="dot-red">■</span>
                SYS.REF: {{ $order->formatted_reference }} // SUIVI CLIENT
            </div>
            <div>
                STATUS // {{ strtoupper($order->status->value) }}
            </div>
        </div>
    </div>

    <div class="container-detail">

        {{-- BREADCRUMBS --}}
        <div class="breadcrumbs-row font-mono">
            <a href="{{ route('home') }}">ACCUEIL</a>
            <span class="sep">/</span>
            <a href="{{ route('orders.index') }}">MES COMMANDES</a>
            <span class="sep">/</span>
            <span class="active">{{ $order->formatted_reference }}</span>
        </div>

        {{-- HEADER COMMANDE --}}
        <header class="detail-header">
            <div class="header-main">
                <span class="order-type-tag font-mono">
                    COMMANDE COD // PAIEMENT À LA LIVRAISON
                </span>
                <h1 class="order-title">
                    {{ $order->formatted_reference }}
                </h1>
                <p class="order-subtitle">
                    Passée le {{ $order->created_at->format('d F Y à H:i') }}
                </p>
            </div>

            <div class="header-badge-box">
                <div class="status-badge {{ $statusBadgeClass }} font-mono">
                    <span class="badge-pulse">●</span>
                    {{ $statusTitle }}
                </div>
            </div>
        </header>

        {{-- STEPPER DE SUIVI --}}
        @if(!$isCancelled)
            <div class="tracking-stepper">
                <div class="step-node {{ $step1Completed ? 'completed' : '' }}">
                    <div class="step-circle font-mono">01</div>
                    <div class="step-text">
                        <span class="step-name font-mono">REÇUE</span>
                        <span class="step-desc">Enregistrée</span>
                    </div>
                </div>

                <div class="step-line {{ $step2Completed ? 'completed' : ($step2Current ? 'active' : '') }}"></div>

                <div class="step-node {{ $step2Completed ? 'completed' : ($step2Current ? 'active' : '') }}">
                    <div class="step-circle font-mono">02</div>
                    <div class="step-text">
                        <span class="step-name font-mono">VALIDÉE</span>
                        <span class="step-desc">Préparation</span>
                    </div>
                </div>

                <div class="step-line {{ $step3Completed ? 'completed' : ($step3Current ? 'active' : '') }}"></div>

                <div class="step-node {{ $step3Completed ? 'completed' : ($step3Current ? 'active' : '') }}">
                    <div class="step-circle font-mono">03</div>
                    <div class="step-text">
                        <span class="step-name font-mono">EXPÉDIÉE</span>
                        <span class="step-desc">En transit</span>
                    </div>
                </div>

                <div class="step-line {{ $step4Completed ? 'completed' : ($step4Current ? 'active' : '') }}"></div>

                <div class="step-node {{ $step4Completed ? 'completed' : ($step4Current ? 'active' : '') }}">
                    <div class="step-circle font-mono">04</div>
                    <div class="step-text">
                        <span class="step-name font-mono">LIVRÉE</span>
                        <span class="step-desc">Réception & COD</span>
                    </div>
                </div>
            </div>
        @else
            <div class="cancelled-banner">
                <div class="cancelled-icon font-mono">✕ COMMANDE ANNULÉE</div>
                <p>Cette commande a été annulée. Aucun montant ne vous sera réclamé à la livraison et les stocks ont été libérés.</p>
            </div>
        @endif

        {{-- GRILLE CONTENU --}}
        <div class="detail-grid">

            {{-- COLONNE GAUCHE : ARTICLES --}}
            <div class="main-column">
                <section class="card-box">
                    <div class="card-head font-mono">
                        <span>01 // DÉTAIL DES ARTICLES</span>
                        <span>{{ $order->items->count() }} RÉFÉRENCE(S)</span>
                    </div>

                    <div class="items-table-wrap">
                        <table class="items-table">
                            <thead>
                                <tr class="font-mono">
                                    <th>ARTICLE</th>
                                    <th>DÉCLINAISON</th>
                                    <th class="text-center">QTÉ</th>
                                    <th class="text-right">PRIX UNITAIRE</th>
                                    <th class="text-right">SOUS-TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <span class="product-title">{{ $item->product_name }}</span>
                                            @if($item->variant_sku)
                                                <span class="sku-tag font-mono">{{ $item->variant_sku }}</span>
                                            @endif
                                        </td>
                                        <td class="font-mono text-muted">
                                            @if($item->variant_size)
                                                <div>Taille: <strong>{{ $item->variant_size }}</strong></div>
                                            @endif
                                            @if($item->variant_color)
                                                <div>Couleur: <strong>{{ $item->variant_color }}</strong></div>
                                            @endif
                                        </td>
                                        <td class="text-center font-mono">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="text-right font-mono text-muted">
                                            {{ number_format($item->price, 0, ',', ' ') }} DA
                                        </td>
                                        <td class="text-right font-mono font-bold">
                                            {{ number_format($item->price * $item->quantity, 0, ',', ' ') }} DA
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="order-summary-footer font-mono">
                        <div class="summary-row">
                            <span class="summary-lbl">Sous-total articles :</span>
                            <span class="summary-val">{{ number_format($order->total, 0, ',', ' ') }} DA</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-lbl">Frais de livraison :</span>
                            <span class="summary-val text-muted">À confirmer par le transporteur</span>
                        </div>
                        <div class="summary-row total-row">
                            <span class="summary-lbl">TOTAL ARTICLES (À PAYER EN ESPÈCES) :</span>
                            <span class="summary-val text-accent">{{ number_format($order->total, 0, ',', ' ') }} DA</span>
                        </div>
                    </div>
                </section>
            </div>

            {{-- COLONNE DROITE : ADRESSE & CONTACT --}}
            <div class="side-column">
                <section class="card-box">
                    <div class="card-head font-mono">
                        <span>02 // LIVRAISON & CLIENT</span>
                    </div>
                    <div class="card-body font-mono">
                        <div class="info-group">
                            <span class="info-label">DESTINATAIRE</span>
                            <span class="info-value text-bold">{{ $order->full_name }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">NUMÉRO DE CONTACT</span>
                            <span class="info-value">{{ $order->phone }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">WILAYA DE DESTINATION</span>
                            <span class="info-value text-accent">{{ $order->wilaya }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">ADRESSE DE LIVRAISON</span>
                            <span class="info-value">{{ $order->address }}</span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">MODE DE RÈGLEMENT</span>
                            <span class="info-value">Espèces à la livraison (COD)</span>
                        </div>
                    </div>
                </section>

                <div class="support-notice">
                    <div class="support-head font-mono">INFORMATION EXPÉDITION</div>
                    <p class="support-text">
                        Notre équipe vous contacte systématiquement par appel téléphonique au <strong>{{ $order->phone }}</strong>
                        pour confirmer votre disponibilité avant le dépôt du colis auprès de notre société de livraison partenaire.
                    </p>
                </div>

                <div class="actions-panel">
                    <a href="{{ route('orders.index') }}" class="btn-back font-mono">
                        ← RETOUR À MES COMMANDES
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<style>
.order-detail-page {
    min-height: 85vh;
    padding-bottom: 5rem;
    color: var(--text);
}

.container-detail {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.top-strip {
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

.dot-red {
    color: var(--accent);
    margin-right: 0.4rem;
}

.font-mono {
    font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
}

.breadcrumbs-row {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    color: rgba(245, 245, 245, 0.5);
    margin: 2rem 0 1.5rem;
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

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border);
    gap: 1.5rem;
    flex-wrap: wrap;
}

.order-type-tag {
    display: block;
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    color: rgba(245, 245, 245, 0.45);
    margin-bottom: 0.5rem;
}

.order-title {
    font-size: 2.5rem;
    font-weight: 900;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
}

.order-subtitle {
    font-size: 0.95rem;
    color: rgba(245, 245, 245, 0.6);
    margin: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    border: 1px solid transparent;
}

.badge-pulse {
    font-size: 0.65rem;
}

.badge--pending {
    background: rgba(234, 179, 8, 0.1);
    color: #facc15;
    border-color: rgba(234, 179, 8, 0.3);
}

.badge--paid {
    background: rgba(59, 130, 246, 0.1);
    color: #60a5fa;
    border-color: rgba(59, 130, 246, 0.3);
}

.badge--shipped {
    background: rgba(168, 85, 247, 0.1);
    color: #c084fc;
    border-color: rgba(168, 85, 247, 0.3);
}

.badge--delivered {
    background: rgba(34, 197, 94, 0.1);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.3);
}

.badge--cancelled {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}

/* STEPPER */
.tracking-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 2.5rem 0;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
}

.step-node {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.step-circle {
    width: 36px;
    height: 36px;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.03);
    color: rgba(245, 245, 245, 0.4);
    transition: all 0.2s;
}

.step-node.completed .step-circle {
    border-color: var(--accent);
    background: var(--accent);
    color: #fff;
}

.step-node.active .step-circle {
    border-color: var(--accent);
    color: var(--accent);
}

.step-text {
    display: flex;
    flex-direction: column;
}

.step-name {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.step-desc {
    font-size: 0.68rem;
    color: rgba(245, 245, 245, 0.5);
}

.step-line {
    flex: 1;
    height: 1px;
    background: var(--border);
    margin: 0 1rem;
    transition: background 0.2s;
}

.step-line.completed {
    background: var(--accent);
}

.cancelled-banner {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.3);
    padding: 1.5rem;
    margin: 2rem 0;
}

.cancelled-icon {
    color: #f87171;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
}

.cancelled-banner p {
    color: rgba(245, 245, 245, 0.7);
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* GRILLE */
.detail-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 900px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .tracking-stepper {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.5rem;
    }
    .step-line {
        display: none;
    }
}

.card-box {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
}

.card-head {
    display: flex;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.015);
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    color: rgba(245, 245, 245, 0.5);
}

.card-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.items-table-wrap {
    overflow-x: auto;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    color: rgba(245, 245, 245, 0.45);
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border);
    text-align: left;
}

.items-table td {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    vertical-align: top;
    font-size: 0.85rem;
}

.product-title {
    display: block;
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 0.35rem;
}

.sku-tag {
    font-size: 0.68rem;
    color: rgba(245, 245, 245, 0.4);
    letter-spacing: 0.08em;
}

.text-muted {
    color: rgba(245, 245, 245, 0.5);
}

.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.text-accent { color: var(--accent); }

.order-summary-footer {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.01);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
}

.summary-row.total-row {
    padding-top: 1rem;
    border-top: 1px solid var(--border);
    font-size: 1rem;
    font-weight: 800;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.info-label {
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    color: rgba(245, 245, 245, 0.4);
}

.info-value {
    font-size: 0.88rem;
    line-height: 1.4;
}

.support-notice {
    margin-top: 1.5rem;
    padding: 1.25rem;
    background: rgba(255, 255, 255, 0.015);
    border: 1px solid var(--border);
}

.support-head {
    font-size: 0.7rem;
    letter-spacing: 0.1em;
    color: var(--accent);
    margin-bottom: 0.5rem;
}

.support-text {
    font-size: 0.82rem;
    color: rgba(245, 245, 245, 0.7);
    line-height: 1.5;
    margin: 0;
}

.actions-panel {
    margin-top: 1.5rem;
}

.btn-back {
    display: block;
    text-align: center;
    border: 1px solid var(--border);
    padding: 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--text);
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back:hover {
    border-color: var(--text);
    background: rgba(255, 255, 255, 0.03);
}
</style>
@endsection
