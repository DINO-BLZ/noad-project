@extends('layouts.app')

@section('content')

<div class="order-confirmation-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <header class="confirmation-header">

        <div class="header-main-row">

            <div class="header-left">

                <div class="step-meta">
                    <span class="step-badge">ÉTAPE 3 / 3</span>
                    <span class="step-divider">—</span>
                    <span class="step-status">SUCCÈS OPÉRATIONNEL</span>
                </div>

                <h1 class="confirmation-title">
                    COMMANDE VALIDÉE
                </h1>

                <p class="confirmation-lead">
                    Merci pour votre confiance.
                    Votre commande
                    <span class="order-code">
                        {{ $order->reference ?? 'N° ND-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                    </span>
                    est enregistrée et prise en charge.
                </p>

            </div>


            <div class="order-status-card">

                <div class="status-indicator-line">
                    <span class="pulse-dot"></span>

                    <span class="status-label">
                        STATUT :
                       {{ strtoupper($order->status->value) }}
                    </span>
                </div>

                <span class="status-sub">
                    COMMANDE ENREGISTRÉE
                </span>

            </div>

        </div>


        {{-- STEPPER --}}

        <nav class="stepper-track">

            <div class="step-item completed">
                <span class="step-number">01</span>
                <span class="step-text">LIVRAISON</span>
                <span class="step-check">✓</span>
            </div>

            <div class="track-divider completed"></div>

            <div class="step-item completed">
                <span class="step-number">02</span>
                <span class="step-text">PAIEMENT</span>
                <span class="step-check">✓</span>
            </div>

            <div class="track-divider current"></div>

            <div class="step-item current">
                <span class="step-number">03</span>
                <span class="step-text">CONFIRMATION</span>
                <span class="step-dot">●</span>
            </div>

            <div class="stepper-brand">
                DEFEND YOUR PRINCIPLE
            </div>

        </nav>

    </header>


    {{-- =========================================================
         MAIN GRID
    ========================================================== --}}

    <div class="confirmation-grid">


        {{-- =====================================================
             LEFT COLUMN
        ====================================================== --}}

        <div class="details-column">


            {{-- =================================================
                 DELIVERY / CONTACT
            ================================================== --}}

            <section class="ops-card">

                <div class="ops-card-header">

                    <div class="card-title-group">

                        <span class="card-num">
                            01 //
                        </span>

                        <h2 class="card-title">
                            RÉCAPITULATIF DE LIVRAISON & CONTACT
                        </h2>

                    </div>

                    <span class="card-icon">
                        🗂
                    </span>

                </div>


                <div class="contact-delivery-grid">


                    {{-- DESTINATAIRE --}}

                    <div class="data-block">

                        <span class="data-label">
                            DESTINATAIRE
                        </span>

                        <span class="data-val primary-name">
                            {{ $order->full_name }}
                        </span>

                        @if($order->email)
                            <span class="data-sub">
                                {{ $order->email }}
                            </span>
                        @elseif($order->user)
                            <span class="data-sub">
                                {{ $order->user->email }}
                            </span>
                        @endif

                        <span class="data-sub">
                            {{ $order->phone }}
                        </span>

                    </div>


                    {{-- ADRESSE --}}

                    <div class="data-block">

                        <span class="data-label">
                            ADRESSE DE LIVRAISON
                        </span>

                        <span class="data-val">
                            {{ $order->address }}
                        </span>

                        <span class="data-sub">
                            {{ $order->wilaya }}
                        </span>

                        <span class="delivery-badge">
                            LIVRAISON À DOMICILE
                        </span>

                    </div>

                </div>


                {{-- PAYMENT --}}

                <div class="payment-summary-strip">

                    <div class="strip-icon">
                        💵
                    </div>

                    <div class="strip-text">

                        <span class="strip-title">
                            PAIEMENT À LA LIVRAISON
                        </span>

                        <p class="strip-desc">
                            Le paiement sera effectué en espèces
                            lors de la réception de votre commande.
                        </p>

                    </div>

                    <div class="strip-amount">

                        <span class="amount-val">
                            {{ number_format($order->total, 0, ',', ' ') }} DA
                        </span>

                        <span class="amount-sub">
                            À RÉGLER
                        </span>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 TRACKING
            ================================================== --}}

            <section class="ops-card">

                <div class="ops-card-header">

                    <div class="card-title-group">

                        <span class="card-num">
                            02 //
                        </span>

                        <h2 class="card-title">
                            ÉTAPES DE SUIVI DU COLIS
                        </h2>

                    </div>

                    <span class="card-badge-live">
                        SUIVI DE COMMANDE
                    </span>

                </div>


                <div class="tracking-timeline">


                    {{-- STEP 1 --}}

                    <div class="timeline-step completed">

                        <div class="step-indicator">

                            <span class="timeline-bullet"></span>

                            <span class="timeline-line"></span>

                        </div>

                        <div class="timeline-content">

                            <div class="timeline-meta-row">

                                <h3 class="timeline-title">
                                    1. COMMANDE CONFIRMÉE
                                </h3>

                                <span class="timeline-time">

                                    {{ $order->created_at
                                        ? $order->created_at->format('d/m/Y — H:i')
                                        : ''
                                    }}

                                </span>

                            </div>

                            <p class="timeline-desc">

                                Votre commande a été enregistrée
                                et le stock correspondant a été réservé.

                            </p>

                        </div>

                    </div>


                    {{-- STEP 2 --}}

                    <div class="timeline-step in-progress">

                        <div class="step-indicator">

                            <span class="timeline-bullet"></span>

                            <span class="timeline-line"></span>

                        </div>

                        <div class="timeline-content">

                            <div class="timeline-meta-row">

                                <h3 class="timeline-title">
                                    2. PRÉPARATION
                                </h3>

                                <span class="timeline-time in-progress-tag">
                                    EN COURS
                                </span>

                            </div>

                            <p class="timeline-desc">

                                Votre commande est en cours
                                de préparation avant expédition.

                            </p>

                        </div>

                    </div>


                    {{-- STEP 3 --}}

                    <div class="timeline-step upcoming">

                        <div class="step-indicator">

                            <span class="timeline-bullet"></span>

                            <span class="timeline-line"></span>

                        </div>

                        <div class="timeline-content">

                            <div class="timeline-meta-row">

                                <h3 class="timeline-title">
                                    3. EXPÉDITION
                                </h3>

                                <span class="timeline-time">
                                    À VENIR
                                </span>

                            </div>

                            <p class="timeline-desc">

                                Votre colis sera remis au transporteur
                                dès que la préparation sera terminée.

                            </p>

                        </div>

                    </div>


                    {{-- STEP 4 --}}

                    <div class="timeline-step upcoming">

                        <div class="step-indicator">

                            <span class="timeline-bullet"></span>

                        </div>

                        <div class="timeline-content">

                            <div class="timeline-meta-row">

                                <h3 class="timeline-title">
                                    4. LIVRAISON À DOMICILE
                                </h3>

                                <span class="timeline-time">
                                    PAIEMENT À RÉCEPTION
                                </span>

                            </div>

                            <p class="timeline-desc">

                                Votre commande sera livrée à l'adresse
                                indiquée lors du checkout.

                            </p>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 ACTIONS
            ================================================== --}}

            <div class="confirmation-actions">

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-invoice"
                >
                    <span>⤓</span>
                    IMPRIMER LA COMMANDE
                </button>


                <a
                    href="{{ route('shop.index') }}"
                    class="btn-return-shop"
                >
                    RETOUR À LA BOUTIQUE

                    <span>
                        →
                    </span>

                </a>

            </div>

        </div>


        {{-- =====================================================
             RIGHT COLUMN
        ====================================================== --}}

        <aside class="summary-column">

            <div class="manifest-card">


                {{-- HEADER --}}

                <div class="manifest-top">

                    <span class="manifest-tag">
                        MANIFESTE D'EXPÉDITION
                    </span>

                    <div class="manifest-heading-row">

                        <h2 class="manifest-title">
                            VOTRE COMMANDE
                        </h2>

                        <span class="manifest-count">
                            {{ $order->items->count() }}
                            ARTICLE{{ $order->items->count() > 1 ? 'S' : '' }}
                        </span>

                    </div>

                </div>


                {{-- ITEMS --}}

                <div class="manifest-items-list">

                    @forelse($order->items as $item)

                        <div class="manifest-item">


                            {{-- IMAGE --}}

                            <div class="item-thumbnail">

                                @if($item->product && $item->product->image)

                                    <img
                                        src="{{ asset('storage/' . $item->product->image) }}"
                                        alt="{{ $item->product->name }}"
                                    >

                                @else

                                    <div class="thumb-stub">
                                        NOAD
                                    </div>

                                @endif

                            </div>


                            {{-- INFO --}}

                            <div class="item-info">

                                <div class="item-head">

                                    <h3 class="item-name">

                                        {{ $item->product->name ?? 'PRODUIT NOAD' }}

                                    </h3>

                                    <span class="item-price">

                                        {{ number_format(
                                            ($item->price ?? 0) * ($item->quantity ?? 1),
                                            0,
                                            ',',
                                            ' '
                                        ) }}

                                        DA

                                    </span>

                                </div>


                                <div class="item-specs">

                                    @if($item->variant)

                                        <span>
                                            TAILLE :
                                            {{ strtoupper($item->variant->size) }}
                                        </span>

                                    @endif

                                    <span class="spec-dot">
                                        •
                                    </span>

                                    <span>
                                        QTÉ :
                                        {{ $item->quantity }}
                                    </span>

                                </div>


                                <span class="item-ref">

                                    RÉF.
                                    {{ $item->product->sku ?? 'NOAD-' . str_pad($item->product_id ?? $item->id, 4, '0', STR_PAD_LEFT) }}

                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="empty-items">
                            AUCUN ARTICLE
                        </div>

                    @endforelse

                </div>


                {{-- FINANCIALS --}}

                <div class="manifest-financials">

                    <div class="fin-row">

                        <span>
                            Sous-total articles
                        </span>

                        <span>
                            {{ number_format($order->subtotal ?? $order->total, 0, ',', ' ') }}
                            DA
                        </span>

                    </div>


                    <div class="fin-row">

                        <span>
                            Livraison
                        </span>

                        <span class="highlight-free">
                            À CONFIRMER
                        </span>

                    </div>


                    <div class="fin-row">

                        <span>
                            Paiement
                        </span>

                        <span>
                            À LA LIVRAISON
                        </span>

                    </div>

                </div>


                {{-- TOTAL --}}

                <div class="manifest-total">

                    <div class="total-text-group">

                        <span class="total-caption">
                            MONTANT TOTAL
                        </span>

                        <span class="total-title">
                            TOTAL À LA LIVRAISON
                        </span>

                    </div>

                    <div class="total-figure">

                        {{ number_format($order->total, 0, ',', ' ') }}

                        DA

                    </div>

                </div>


                {{-- NOTICE --}}

                <div class="confirmation-notice">

                    <span class="notice-shield">
                        🛡
                    </span>

                    <div>

                        <span class="notice-heading">
                            COMMANDE ENREGISTRÉE
                        </span>

                        <p class="notice-msg">

                            Votre commande a bien été enregistrée.
                            Conservez votre numéro de commande
                            pour tout contact avec NOAD.

                        </p>

                    </div>

                </div>


                {{-- FOOTER --}}

                <div class="manifest-footer-bar">

                    <span>
                        NO AD ADVANTAGE — ALGIERS HUB
                    </span>

                    <span>
                        NOAD
                    </span>

                </div>

            </div>

        </aside>

    </div>


    {{-- =========================================================
         RECOMMENDATIONS
    ========================================================== --}}

    <section class="recommendations-section">

        <div class="rec-header">

            <div>

                <span class="rec-pre">
                    SÉLECTION COMPLÉMENTAIRE
                </span>

                <h2 class="rec-title">
                    VOUS AIMEREZ AUSSI
                </h2>

            </div>

            <a
                href="{{ route('shop.index') }}"
                class="rec-link-all"
            >
                VOIR LE CATALOGUE ↗
            </a>

        </div>


        <div class="rec-grid">

            <article class="rec-card">

                <span class="rec-tag">
                    NOAD
                </span>

                <div class="rec-thumb">
                    <div class="thumb-stub">
                        NOAD
                    </div>
                </div>

                <div class="rec-body">

                    <div class="rec-meta">

                        <h3 class="rec-name">
                            DISCOVER THE COLLECTION
                        </h3>

                        <span class="rec-desc">
                            EXPLORE NOS PIÈCES
                        </span>

                    </div>

                    <div class="rec-footer">

                        <span class="rec-price">
                            NOAD
                        </span>

                        <a
                            href="{{ route('shop.index') }}"
                            class="btn-add-rec"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>


            <article class="rec-card">

                <span class="rec-tag red">
                    DROP
                </span>

                <div class="rec-thumb">
                    <div class="thumb-stub">
                        NOAD // DROP
                    </div>
                </div>

                <div class="rec-body">

                    <div class="rec-meta">

                        <h3 class="rec-name">
                            NOAD DROPS
                        </h3>

                        <span class="rec-desc">
                            PIÈCES EN ÉDITION LIMITÉE
                        </span>

                    </div>

                    <div class="rec-footer">

                        <span class="rec-price">
                            EXPLORE
                        </span>

                        <a
                            href="{{ route('drops.index') }}"
                            class="btn-add-rec"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>


            <article class="rec-card">

                <span class="rec-tag">
                    COLLECTION
                </span>

                <div class="rec-thumb">
                    <div class="thumb-stub">
                        NOAD // STORE
                    </div>
                </div>

                <div class="rec-body">

                    <div class="rec-meta">

                        <h3 class="rec-name">
                            THE COLLECTION
                        </h3>

                        <span class="rec-desc">
                            ESSENTIAL NOAD PIECES
                        </span>

                    </div>

                    <div class="rec-footer">

                        <span class="rec-price">
                            SHOP
                        </span>

                        <a
                            href="{{ route('shop.index') }}"
                            class="btn-add-rec"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>


            <article class="rec-card">

                <span class="rec-tag">
                    PRINCIPLE
                </span>

                <div class="rec-thumb">
                    <div class="thumb-stub">
                        NOAD
                    </div>
                </div>

                <div class="rec-body">

                    <div class="rec-meta">

                        <h3 class="rec-name">
                            DEFEND YOUR PRINCIPLE
                        </h3>

                        <span class="rec-desc">
                            NO AD ADVANTAGE
                        </span>

                    </div>

                    <div class="rec-footer">

                        <span class="rec-price">
                            NOAD
                        </span>

                        <a
                            href="{{ route('shop.index') }}"
                            class="btn-add-rec"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>

        </div>

    </section>


    {{-- =========================================================
         BRAND COMMITMENTS
    ========================================================== --}}

    <div class="commitments-banner">

        <div class="commitment-item">

            <span class="commit-icon">
                🚚
            </span>

            <div>

                <span class="commit-title">
                    EXPÉDITION
                </span>

                <span class="commit-desc">
                    Livraison dans les 58 Wilayas.
                </span>

            </div>

        </div>


        <div class="commitment-item">

            <span class="commit-icon">
                ↺
            </span>

            <div>

                <span class="commit-title">
                    ÉCHANGE
                </span>

                <span class="commit-desc">
                    Contactez NOAD pour toute demande.
                </span>

            </div>

        </div>


        <div class="commitment-item">

            <span class="commit-icon">
                🛡
            </span>

            <div>

                <span class="commit-title">
                    SUPPORT
                </span>

                <span class="commit-desc">
                    Une équipe disponible pour votre commande.
                </span>

            </div>

        </div>

    </div>

</div>


<style>

/* =========================================================
   BASE
========================================================= */

.order-confirmation-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 32px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
}

.order-confirmation-page a {
    color: inherit;
    text-decoration: none;
}


/* =========================================================
   HEADER
========================================================= */

.confirmation-header {
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 28px;
    margin-bottom: 40px;
}

.header-main-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    flex-wrap: wrap;
    margin-bottom: 28px;
}

.header-left {
    max-width: 720px;
}

.step-meta {
    font-family: monospace;
    font-size: 11px;
    letter-spacing: .15em;
    color: #888;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.step-badge {
    color: var(--accent, #B02E26);
    font-weight: bold;
}

.step-divider {
    margin: 0 8px;
    color: #444;
}

.confirmation-title {
    font-size: clamp(34px, 5.5vw, 54px);
    font-weight: 900;
    letter-spacing: .03em;
    line-height: 1;
    margin: 0 0 12px;
    text-transform: uppercase;
}

.confirmation-lead {
    font-size: 14px;
    color: #999;
    margin: 0;
    line-height: 1.5;
}

.order-code {
    font-family: monospace;
    color: #fff;
    background: #1a1a1a;
    border: 1px solid var(--border, #242424);
    padding: 2px 8px;
    font-weight: bold;
}

.order-status-card {
    background: #121212;
    border: 1px solid var(--border, #242424);
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    text-align: right;
}

.status-indicator-line {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent, #B02E26);
    display: inline-block;
    box-shadow: 0 0 0 3px rgba(176, 46, 38, .2);
}

.status-label {
    font-family: monospace;
    font-size: 11px;
    font-weight: bold;
    color: #fff;
    letter-spacing: .12em;
}

.status-sub {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: .1em;
}


/* =========================================================
   STEPPER
========================================================= */

.stepper-track {
    display: flex;
    align-items: center;
    gap: 16px;
    font-family: monospace;
    font-size: 11px;
    letter-spacing: .12em;
    flex-wrap: wrap;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.step-item.completed {
    color: #aaa;
}

.step-item.current {
    color: #fff;
    font-weight: bold;
}

.step-number {
    font-weight: bold;
}

.step-item.current .step-number {
    color: var(--accent, #B02E26);
}

.step-dot {
    color: var(--accent, #B02E26);
}

.step-check {
    color: #2ecc71;
}

.track-divider {
    width: 40px;
    height: 1px;
    background: #242424;
}

.track-divider.completed {
    background: #444;
}

.track-divider.current {
    background: var(--accent, #B02E26);
}

.stepper-brand {
    margin-left: auto;
    font-size: 10px;
    color: #555;
    letter-spacing: .15em;
}


/* =========================================================
   GRID
========================================================= */

.confirmation-grid {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    gap: 40px;
    align-items: flex-start;
}


/* =========================================================
   CARDS
========================================================= */

.ops-card,
.manifest-card {
    background: #101010;
    border: 1px solid var(--border, #242424);
}

.ops-card {
    padding: 28px;
    margin-bottom: 24px;
}

.ops-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border, #242424);
    margin-bottom: 24px;
}

.card-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-num {
    color: var(--accent, #B02E26);
    font-weight: 900;
    font-family: monospace;
    font-size: 13px;
}

.card-title {
    font-size: 14px;
    font-weight: 900;
    letter-spacing: .12em;
    margin: 0;
    color: #fff;
    text-transform: uppercase;
}

.card-icon {
    font-size: 14px;
    opacity: .6;
}

.card-badge-live {
    font-family: monospace;
    font-size: 10px;
    color: #2ecc71;
    letter-spacing: .12em;
    font-weight: bold;
}


/* =========================================================
   DELIVERY
========================================================= */

.contact-delivery-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.data-block {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.data-label {
    font-family: monospace;
    font-size: 10px;
    color: #666;
    letter-spacing: .12em;
    margin-bottom: 4px;
}

.data-val {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
}

.data-val.primary-name {
    font-size: 15px;
}

.data-sub {
    font-family: monospace;
    font-size: 12px;
    color: #888;
}

.delivery-badge {
    align-self: flex-start;
    margin-top: 6px;
    background: #1a1a1a;
    border: 1px solid var(--border, #242424);
    font-family: monospace;
    font-size: 9px;
    padding: 2px 6px;
    color: #aaa;
    letter-spacing: .1em;
}


/* =========================================================
   PAYMENT
========================================================= */

.payment-summary-strip {
    background: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.strip-icon {
    font-size: 20px;
}

.strip-text {
    flex: 1;
}

.strip-title {
    font-weight: 800;
    font-size: 13px;
    letter-spacing: .08em;
    color: #fff;
    display: block;
    margin-bottom: 2px;
}

.strip-desc {
    font-size: 11px;
    color: #777;
    margin: 0;
    line-height: 1.4;
}

.strip-amount {
    text-align: right;
    display: flex;
    flex-direction: column;
}

.amount-val {
    font-family: monospace;
    font-size: 16px;
    font-weight: 900;
    color: #fff;
}

.amount-sub {
    font-family: monospace;
    font-size: 9px;
    color: var(--accent, #B02E26);
    font-weight: bold;
    letter-spacing: .1em;
}


/* =========================================================
   TIMELINE
========================================================= */

.tracking-timeline {
    display: flex;
    flex-direction: column;
    padding-left: 4px;
}

.timeline-step {
    display: flex;
    gap: 20px;
    position: relative;
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 14px;
}

.timeline-bullet {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #333;
    border: 2px solid #101010;
    z-index: 2;
    margin-top: 4px;
}

.timeline-step.completed .timeline-bullet {
    background: var(--accent, #B02E26);
    box-shadow: 0 0 0 2px rgba(176, 46, 38, .2);
}

.timeline-step.in-progress .timeline-bullet {
    background: #fff;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, .15);
}

.timeline-line {
    width: 1px;
    flex: 1;
    background: var(--border, #242424);
    min-height: 48px;
}

.timeline-content {
    flex: 1;
    padding-bottom: 24px;
}

.timeline-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 4px;
}

.timeline-title {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .08em;
    color: #fff;
    margin: 0;
    text-transform: uppercase;
}

.timeline-step.upcoming .timeline-title {
    color: #777;
}

.timeline-time {
    font-family: monospace;
    font-size: 10px;
    color: #666;
    letter-spacing: .1em;
    white-space: nowrap;
}

.in-progress-tag {
    color: #fff;
    background: #1f1f1f;
    padding: 2px 6px;
    font-weight: bold;
}

.timeline-desc {
    font-size: 12px;
    color: #777;
    margin: 0;
    line-height: 1.45;
}


/* =========================================================
   ACTIONS
========================================================= */

.confirmation-actions {
    display: flex;
    gap: 16px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.btn-invoice,
.btn-return-shop {
    padding: 14px 24px;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: .12em;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.btn-invoice {
    border: 1px solid var(--border, #242424);
    background: #101010;
    color: #fff;
}

.btn-invoice:hover {
    border-color: #666;
}

.btn-return-shop {
    background: #fff;
    color: #000 !important;
    border: 1px solid #fff;
}

.btn-return-shop:hover {
    background: var(--accent, #B02E26);
    border-color: var(--accent, #B02E26);
    color: #fff !important;
}


/* =========================================================
   MANIFEST
========================================================= */

.manifest-card {
    padding: 28px;
}

.manifest-tag {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .15em;
    color: #666;
    display: block;
    margin-bottom: 4px;
}

.manifest-heading-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border, #242424);
    margin-bottom: 20px;
}

.manifest-title {
    font-size: 15px;
    font-weight: 900;
    letter-spacing: .12em;
    margin: 0;
    color: #fff;
}

.manifest-count {
    font-family: monospace;
    font-size: 11px;
    color: #666;
    white-space: nowrap;
}


/* =========================================================
   ITEMS
========================================================= */

.manifest-items-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.manifest-item {
    display: flex;
    gap: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #1a1a1a;
}

.item-thumbnail {
    width: 60px;
    height: 72px;
    background: #151515;
    border: 1px solid var(--border, #242424);
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumb-stub {
    font-family: monospace;
    font-size: 9px;
    color: #444;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
}

.item-name {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .05em;
    margin: 0;
    text-transform: uppercase;
}

.item-price {
    font-family: monospace;
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    white-space: nowrap;
}

.item-specs {
    font-family: monospace;
    font-size: 10px;
    color: #777;
    margin: 5px 0;
}

.spec-dot {
    margin: 0 4px;
    color: #444;
}

.item-ref {
    font-family: monospace;
    font-size: 9px;
    color: #555;
}

.empty-items {
    padding: 20px;
    text-align: center;
    font-family: monospace;
    color: #555;
}


/* =========================================================
   FINANCIALS
========================================================= */

.manifest-financials {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-family: monospace;
    font-size: 11px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border, #242424);
}

.fin-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #888;
}

.highlight-free {
    color: var(--accent, #B02E26);
    font-weight: bold;
}


/* =========================================================
   TOTAL
========================================================= */

.manifest-total {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid var(--border, #242424);
}

.total-caption {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: .1em;
    display: block;
    margin-bottom: 2px;
}

.total-title {
    font-size: 16px;
    font-weight: 900;
    letter-spacing: .1em;
    color: #fff;
}

.total-figure {
    font-family: monospace;
    font-size: 28px;
    font-weight: 900;
    color: #fff;
    line-height: 1;
    white-space: nowrap;
}


/* =========================================================
   NOTICE
========================================================= */

.confirmation-notice {
    margin-top: 20px;
    background: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 14px;
    display: flex;
    gap: 12px;
}

.notice-shield {
    font-size: 14px;
    margin-top: 2px;
}

.notice-heading {
    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    color: #fff;
    letter-spacing: .1em;
    display: block;
    margin-bottom: 4px;
}

.notice-msg {
    font-size: 11px;
    color: #777;
    margin: 0;
    line-height: 1.45;
}


/* =========================================================
   RECOMMENDATIONS
========================================================= */

.recommendations-section {
    margin-top: 64px;
    border-top: 1px solid var(--border, #242424);
    padding-top: 32px;
}

.rec-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 24px;
}

.rec-pre {
    font-family: monospace;
    font-size: 10px;
    color: #666;
    letter-spacing: .15em;
    display: block;
    margin-bottom: 4px;
}

.rec-title {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: .05em;
    margin: 0;
    text-transform: uppercase;
}

.rec-link-all {
    font-family: monospace;
    font-size: 11px;
    color: #888 !important;
    letter-spacing: .1em;
}

.rec-link-all:hover {
    color: #fff !important;
}

.rec-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.rec-card {
    background: #101010;
    border: 1px solid var(--border, #242424);
    position: relative;
    display: flex;
    flex-direction: column;
}

.rec-tag {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #000;
    border: 1px solid var(--border, #242424);
    font-family: monospace;
    font-size: 8px;
    padding: 2px 6px;
    color: #aaa;
    z-index: 2;
}

.rec-tag.red {
    background: var(--accent, #B02E26);
    border-color: var(--accent, #B02E26);
    color: #fff;
}

.rec-thumb {
    aspect-ratio: 1;
    background: #161616;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rec-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rec-name {
    font-size: 13px;
    font-weight: 800;
    margin: 0 0 2px;
    text-transform: uppercase;
}

.rec-desc {
    font-family: monospace;
    font-size: 10px;
    color: #666;
}

.rec-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #1a1a1a;
}

.rec-price {
    font-family: monospace;
    font-size: 12px;
    font-weight: 800;
    color: #fff;
}

.btn-add-rec {
    border: 1px solid var(--border, #242424);
    background: transparent;
    color: #aaa !important;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: monospace;
    font-size: 14px;
}

.btn-add-rec:hover {
    background: #fff;
    border-color: #fff;
    color: #000 !important;
}


/* =========================================================
   COMMITMENTS
========================================================= */

.commitments-banner {
    margin-top: 48px;
    border-top: 1px solid var(--border, #242424);
    border-bottom: 1px solid var(--border, #242424);
    padding: 24px 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.commitment-item {
    display: flex;
    align-items: center;
    gap: 14px;
}

.commit-icon {
    font-size: 20px;
}

.commit-title {
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .1em;
    color: #fff;
    display: block;
    margin-bottom: 2px;
}

.commit-desc {
    font-size: 11px;
    color: #777;
    line-height: 1.35;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1024px) {

    .confirmation-grid {
        grid-template-columns: 1fr;
    }

    .summary-column {
        order: -1;
    }

    .rec-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media (max-width: 800px) {

    .order-status-card {
        width: 100%;
        text-align: left;
    }

    .status-indicator-line {
        justify-content: flex-start;
    }

    .stepper-brand {
        margin-left: 0;
        width: 100%;
    }

    .commitments-banner {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 640px) {

    .order-confirmation-page {
        padding: 20px 16px 60px;
    }

    .confirmation-header {
        margin-bottom: 28px;
    }

    .contact-delivery-grid {
        grid-template-columns: 1fr;
    }

    .payment-summary-strip {
        flex-direction: column;
        align-items: flex-start;
    }

    .strip-amount {
        text-align: left;
    }

    .ops-card,
    .manifest-card {
        padding: 20px;
    }

    .timeline-meta-row {
        flex-direction: column;
        gap: 5px;
    }

    .rec-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .rec-grid {
        grid-template-columns: 1fr;
    }

}


@media print {

    .order-confirmation-page {
        max-width: none;
        padding: 0;
    }

    .confirmation-actions,
    .recommendations-section,
    .commitments-banner {
        display: none !important;
    }

    .confirmation-grid {
        grid-template-columns: 1fr 1fr;
    }

}

</style>

@endsection