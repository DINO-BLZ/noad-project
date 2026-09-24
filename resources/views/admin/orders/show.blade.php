@extends('layouts.admin')

@section('content')

@php
    use App\Enums\OrderStatus;

    $currentStatus = $order->status instanceof OrderStatus
        ? $order->status
        : OrderStatus::tryFrom((string) $order->status);

    $statusLabels = [
        OrderStatus::Pending->value => 'EN ATTENTE DE PAIEMENT',
        OrderStatus::Paid->value => 'COMMANDE PAYÉE',
        OrderStatus::Shipped->value => 'EXPÉDIÉE',
        OrderStatus::Delivered->value => 'LIVRÉE',
        OrderStatus::Cancelled->value => 'COMMANDE ANNULÉE',
    ];

    $statusClasses = [
        OrderStatus::Pending->value => 'pending',
        OrderStatus::Paid->value => 'paid',
        OrderStatus::Shipped->value => 'shipped',
        OrderStatus::Delivered->value => 'delivered',
        OrderStatus::Cancelled->value => 'cancelled',
    ];

    $currentStatusValue = $currentStatus?->value;

    $currentStatusLabel = $statusLabels[$currentStatusValue ?? '']
        ?? 'STATUT INCONNU';

    $currentStatusClass = $statusClasses[$currentStatusValue ?? '']
        ?? 'unknown';

    $allowedTransitions = [];

    if ($currentStatus instanceof OrderStatus) {
        foreach (OrderStatus::cases() as $candidateStatus) {
            if ($currentStatus->canTransitionTo($candidateStatus)) {
                $allowedTransitions[] = $candidateStatus;
            }
        }
    }

    $itemsCount = $order->items?->count() ?? 0;
@endphp

<div class="admin-order-detail-page">

    {{-- =========================================================
         01. BARRE SUPÉRIEURE SYSTÈME & FIL D'ARIANE
    ========================================================== --}}

    <div class="terminal-status-strip">

        <div class="status-left">
            <nav class="breadcrumb-nav">

                <a href="{{ route('admin.dashboard') }}" class="bread-link">
                    ADMINISTRATION
                </a>

                <span class="bread-sep">/</span>

                <a href="{{ route('admin.orders.index') }}" class="bread-link">
                    COMMANDES
                </a>

                <span class="bread-sep">/</span>

                <span class="bread-current">
                    #{{ $order->reference ?? $order->id }}
                </span>

            </nav>
        </div>

        <div class="status-right">

            <span class="server-node-indicator">
                <span class="dot-live">●</span>
                NŒUD SYSTÈME : ALGER-CENTRE-01
            </span>

            <span class="sep">|</span>

            <span class="sys-clock">
                HORODATAGE SYSTÈME :
                {{ now()->format('d.m.Y H:i') }}
            </span>

        </div>

    </div>


    {{-- =========================================================
         02. EN-TÊTE PRINCIPAL DE LA COMMANDE
    ========================================================== --}}

    <header class="order-main-head">

        <div class="head-info">

            <div class="order-title-stack">

                <h1 class="order-code-title">
                    COMMANDE #{{ $order->reference ?? $order->id }}
                </h1>

                <span class="order-sub-proto">
                    // PROTOCOLE DE LIVRAISON
                </span>

            </div>


            <div class="badges-meta-row">

                <span class="status-pill {{ $currentStatusClass }}">
                    <span class="icon-tag">📦</span>
                    {{ $currentStatusLabel }}
                </span>

                <span class="gen-meta">
                    GÉNÉRÉ LE
                    <strong>
                        {{ $order->created_at
                            ? $order->created_at->format('d/m/Y À H:i')
                            : 'DATE INCONNUE'
                        }}
                    </strong>
                </span>

            </div>

        </div>


        <div class="head-actions-grid">

            <a
                href="{{ route('admin.orders.label', $order->id) }}"
                target="_blank"
                class="btn-head-action"
            >
                <span class="btn-icon">🖨</span>
                BORDEREAU
            </a>

            <a
                href="{{ route('admin.orders.invoice', $order->id) }}"
                target="_blank"
                class="btn-head-action"
            >
                <span class="btn-icon">📄</span>
                FACTURE PDF
            </a>

            @if($order->phone)
                <a
                    href="tel:{{ $order->phone }}"
                    class="btn-head-action"
                >
                    <span class="btn-icon">📞</span>
                    APPELER
                </a>
            @endif

            @if(
                $currentStatus instanceof OrderStatus
                && $currentStatus->canTransitionTo(OrderStatus::Cancelled)
            )
                <form
                    action="{{ route('admin.orders.cancel', $order->id) }}"
                    method="POST"
                    class="inline-form"
                    onsubmit="return confirm('Confirmer l\'annulation de cette commande ?');"
                >
                    @csrf

                    <button type="submit" class="btn-head-danger">
                        <span class="btn-icon">✕</span>
                        ANNULER
                    </button>
                </form>
            @endif

        </div>

    </header>


    {{-- =========================================================
         MESSAGES
    ========================================================== --}}

    @if(session('success'))
        <div class="admin-feedback success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="admin-feedback error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif


    {{-- =========================================================
         03. GRILLE PRINCIPALE
    ========================================================== --}}

    <div class="order-layout-grid">


        {{-- =====================================================
             COLONNE GAUCHE
        ====================================================== --}}

        <div class="col-left-flow">


            {{-- =================================================
                 BLOC 1 : ARTICLES COMMANDÉS
            ================================================== --}}

            <section class="ops-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon">🗂</span>

                        <h2 class="panel-title">
                            ARTICLES COMMANDÉS
                            ({{ sprintf('%02d', $itemsCount) }} UNITÉS)
                        </h2>

                    </div>

                    <span class="panel-meta-tag">
                        COMMANDE #{{ $order->reference ?? $order->id }}
                    </span>

                </div>


                <div class="order-items-list">

                    @forelse($order->items as $item)

                        <div class="order-item-row">

                            <div class="item-thumb-col">

                                <span class="stock-tag">
                                    ARTICLE
                                </span>

                                <div class="thumb-box">
                                    <span class="thumb-placeholder">
                                        NOAD
                                    </span>
                                </div>

                            </div>


                            <div class="item-info-col">

                                <h3 class="item-product-name">
                                    {{ $item->product_name ?? 'ARTICLE NOAD' }}
                                </h3>

                                <div class="item-specs-line">

                                    @if(!empty($item->size))
                                        <span>
                                            TAILLE :
                                            <strong>{{ $item->size }}</strong>
                                        </span>

                                        <span class="sep">/</span>
                                    @endif

                                    @if(!empty($item->variant_id))
                                        <span>
                                            VARIANTE :
                                            <span class="mono">
                                                #{{ $item->variant_id }}
                                            </span>
                                        </span>
                                    @endif

                                </div>

                                <span class="item-fabric-desc">
                                    Article enregistré dans la commande.
                                </span>

                            </div>


                            <div class="item-numbers-col">

                                <span class="item-qty">
                                    QTÉ :
                                    {{ (int) $item->quantity }}
                                </span>

                                <span class="item-price">
                                    {{ number_format((float) $item->price, 0, ',', ' ') }}
                                    DA
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="empty-items-state">
                            AUCUN ARTICLE ASSOCIÉ À CETTE COMMANDE
                        </div>

                    @endforelse

                </div>


                {{-- =================================================
                     RÉCAPITULATIF FINANCIER
                ================================================== --}}

                <div class="order-financial-breakdown">

                    <div class="fin-row">

                        <span class="fin-lbl">
                            TOTAL DE LA COMMANDE
                        </span>

                        <span class="fin-val">
                            {{ number_format((float) $order->total, 0, ',', ' ') }}
                            DA
                        </span>

                    </div>


                    <div class="fin-row">

                        <span class="fin-lbl">
                            MODE DE PAIEMENT
                        </span>

                        <span class="fin-val">
                            {{ strtoupper($order->payment_method ?? 'NON DÉFINI') }}
                        </span>

                    </div>


                    <div class="fin-row">

                        <span class="fin-lbl">
                            STATUT DU PAIEMENT
                        </span>

                        <span class="fin-val">
                            {{ strtoupper($order->payment_status?->value ?? $order->payment_status ?? 'NON DÉFINI') }}
                        </span>

                    </div>


                    <div class="fin-total-hero">

                        <div class="total-title-stack">

                            <span class="hero-total-label">
                                TOTAL À PERCEVOIR
                            </span>

                            <span class="hero-total-sub">
                                Montant enregistré sur la commande
                            </span>

                        </div>

                        <span class="hero-total-amount">
                            {{ number_format((float) $order->total, 0, ',', ' ') }}
                            DA
                        </span>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 BLOC 2 : DISPATCH & TRAÇABILITÉ
            ================================================== --}}

            <section class="ops-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon">🚚</span>

                        <h2 class="panel-title">
                            DISPATCH &amp; TRAÇABILITÉ LOGISTIQUE
                        </h2>

                    </div>

                    <span class="panel-meta-tag">
                        WORKFLOW COMMANDE
                    </span>

                </div>


                <form
                    action="{{ route('admin.orders.updateStatus', $order->id) }}"
                    method="POST"
                    class="dispatch-form-body"
                >

                    @csrf
                    @method('PATCH')


                    <div class="dispatch-fields-grid">


                        {{-- TRACKING --}}

                        <div class="field-block">

                            <div class="field-head-meta">

                                <label for="tracking-id">
                                    NUMÉRO DE SUIVI COLIS
                                    (TRACKING ID)
                                </label>

                                <span class="carrier-tag">
                                    SUIVI
                                </span>

                            </div>


                            <div class="input-copy-wrap">

                                <input
                                    type="text"
                                    id="tracking-id"
                                    name="tracking_number"
                                    value="{{ $order->tracking_number ?? '' }}"
                                    readonly
                                >

                                @if($order->tracking_number)
                                    <button
                                        type="button"
                                        class="btn-copy-input"
                                        title="Copier le tracking"
                                        onclick="navigator.clipboard.writeText(document.getElementById('tracking-id').value)"
                                    >
                                        ⎘
                                    </button>
                                @endif

                            </div>


                            <span class="field-subtext">
                                @if($order->tracking_number)
                                    Numéro de suivi enregistré sur la commande.
                                @else
                                    Aucun numéro de suivi enregistré.
                                @endif
                            </span>

                        </div>


                        {{-- STATUT --}}

                        <div class="field-block">

                            <label for="order-status-select">
                                ÉTAPE DU PROCESSUS COMMANDE
                            </label>

                            <div class="select-box">

                                <select
                                    id="order-status-select"
                                    name="status"
                                    @if(count($allowedTransitions) === 0) disabled @endif
                                >

                                    @if(count($allowedTransitions) > 0)

                                        @foreach($allowedTransitions as $nextStatus)

                                            <option
                                                value="{{ $nextStatus->value }}"
                                                {{ old('status') === $nextStatus->value ? 'selected' : '' }}
                                            >
                                                {{ $statusLabels[$nextStatus->value] }}
                                            </option>

                                        @endforeach

                                    @else

                                        <option value="" selected disabled>
                                            AUCUNE TRANSITION DISPONIBLE
                                        </option>

                                    @endif

                                </select>

                            </div>

                            <span class="field-subtext">

                                @if($currentStatus === OrderStatus::Pending)
                                    Actions disponibles : paiement confirmé ou annulation.
                                @elseif($currentStatus === OrderStatus::Paid)
                                    Actions disponibles : expédition ou annulation.
                                @elseif($currentStatus === OrderStatus::Shipped)
                                    Action disponible : livraison.
                                @elseif($currentStatus === OrderStatus::Delivered)
                                    Commande livrée. Aucun changement de statut autorisé.
                                @elseif($currentStatus === OrderStatus::Cancelled)
                                    Commande annulée. Aucun changement de statut autorisé.
                                @else
                                    Statut de commande invalide.
                                @endif

                            </span>

                        </div>

                    </div>


                    {{-- NOTES INTERNES --}}

                    <div class="field-block full-width">

                        <div class="field-head-meta">

                            <label for="internal-notes">
                                NOTES INTERNES ATELIER &amp;
                                CONSIGNES DE SÉCURITÉ
                            </label>

                            <span class="private-tag">
                                NON VISIBLE DU CLIENT
                            </span>

                        </div>

                        <textarea
                            id="internal-notes"
                            name="internal_notes"
                            rows="2"
                            placeholder="Consignes particulières..."
                        >{{ old('internal_notes', $order->internal_notes ?? '') }}</textarea>

                    </div>


                    <div class="dispatch-submit-row">

                        @if(count($allowedTransitions) > 0)

                            <button
                                type="submit"
                                class="btn-submit-status"
                            >
                                <span class="btn-icon">↻</span>
                                ACTUALISER L'ÉTAT ET ENVOYER NOTIFICATION
                            </button>

                        @else

                            <span class="no-transition-message">
                                AUCUNE ACTION DE STATUT DISPONIBLE
                            </span>

                        @endif

                    </div>

                </form>

            </section>


            {{-- =================================================
                 BLOC 3 : JOURNAL D'AUDIT
            ================================================== --}}

            <section class="ops-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon">🕒</span>

                        <h2 class="panel-title">
                            JOURNAL D'AUDIT &amp;
                            CHRONOLOGIE OPÉRATIONNELLE
                        </h2>

                    </div>

                    <span class="panel-meta-tag mono">
                        COMMANDE #{{ $order->reference ?? $order->id }}
                    </span>

                </div>


                <div class="timeline-stack">

                    @forelse($order->auditLogs->sortByDesc('created_at') as $log)

                        <div class="timeline-entry {{ $loop->first ? 'active' : '' }}">

                            <div class="timeline-glyph-col">

                                <span class="glyph-badge {{ $loop->first ? 'red' : '' }}">
                                    {{ $loop->first ? '📦' : '🗂' }}
                                </span>

                                @if(!$loop->last)
                                    <div class="timeline-vertical-line"></div>
                                @endif

                            </div>


                            <div class="timeline-content-col">

                                <div class="entry-header">

                                    <h3 class="entry-title">
                                        {{ $log->action }}
                                    </h3>

                                    <span class="entry-time {{ $loop->first ? 'red' : '' }}">

                                        {{ $log->created_at
                                            ? $log->created_at->format('H:i — d.m.Y')
                                            : 'DATE INCONNUE'
                                        }}

                                    </span>

                                </div>


                                <p class="entry-desc">
                                    {{ $log->description }}
                                </p>


                                <span class="entry-meta">
                                    OPÉRATEUR :
                                    {{ $log->operator ?? 'SYS_ADMIN' }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="timeline-entry active">

                            <div class="timeline-glyph-col">

                                <span class="glyph-badge red">
                                    🕒
                                </span>

                            </div>

                            <div class="timeline-content-col">

                                <div class="entry-header">

                                    <h3 class="entry-title">
                                        AUCUN ÉVÉNEMENT ENREGISTRÉ
                                    </h3>

                                </div>

                                <p class="entry-desc">
                                    Aucun événement d'audit n'est actuellement
                                    associé à cette commande.
                                </p>

                                <span class="entry-meta">
                                    OPÉRATEUR : SYS_ADMIN
                                </span>

                            </div>

                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        {{-- =====================================================
             COLONNE DROITE
        ====================================================== --}}

        <div class="col-right-cards">


            {{-- =================================================
                 CARTE 1 : DESTINATAIRE
            ================================================== --}}

            <section class="ops-panel side-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon">📍</span>

                        <h2 class="panel-title">
                            DESTINATAIRE &amp; LIVRAISON
                        </h2>

                    </div>

                </div>


                <div class="dest-info-body">


                    {{-- NOM --}}

                    <div class="dest-top-row">

                        <div>

                            <span class="dest-sub-label">
                                NOM COMPLET
                            </span>

                            <h3 class="dest-hero-name">
                                {{ $order->full_name ?? 'NOM NON RENSEIGNÉ' }}
                            </h3>

                        </div>


                        @if($order->phone)
                            <a
                                href="tel:{{ $order->phone }}"
                                class="btn-call-dest"
                                title="Appeler"
                            >
                                📞
                            </a>
                        @endif

                    </div>


                    {{-- WILAYA --}}

                    <div class="dest-field-block">

                        <span class="dest-sub-label">
                            WILAYA
                        </span>

                        <div class="wilaya-highlight-box">

                            <div class="wilaya-text-col">

                                <span class="wilaya-name">
                                    {{ $order->wilaya ?? 'WILAYA NON RENSEIGNÉE' }}
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- ADRESSE --}}

                    <div class="dest-field-block">

                        <span class="dest-sub-label">
                            ADRESSE DE LIVRAISON
                        </span>

                        <div class="address-box">

                            <span class="address-icon">
                                📍
                            </span>

                            <div class="address-lines">

                                <strong class="text-white">
                                    {{ $order->address ?? 'ADRESSE NON RENSEIGNÉE' }}
                                </strong>

                            </div>

                        </div>

                    </div>


                    {{-- TÉLÉPHONE --}}

                    <div class="dest-field-block">

                        <span class="dest-sub-label">
                            TÉLÉPHONE PORTABLE
                        </span>

                        <div class="contact-info-row">

                            <span class="phone-number">
                                {{ $order->phone ?? 'NON RENSEIGNÉ' }}
                            </span>

                        </div>

                    </div>


                    {{-- EMAIL --}}

                    @if($order->user)

                        <div class="dest-field-block">

                            <span class="dest-sub-label">
                                ADRESSE COURRIEL
                            </span>

                            <div class="contact-info-row">

                                <span class="email-address">
                                    {{ $order->user->email }}
                                </span>

                                <a
                                    href="mailto:{{ $order->user->email }}"
                                    class="btn-mini-send"
                                    title="Envoyer mail"
                                >
                                    ✉
                                </a>

                            </div>

                        </div>

                    @endif

                </div>

            </section>


            {{-- =================================================
                 CARTE 2 : PAIEMENT
            ================================================== --}}

            <section class="ops-panel side-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon">💵</span>

                        <h2 class="panel-title">
                            MODALITÉ DE PAIEMENT
                        </h2>

                    </div>

                    <span class="panel-meta-tag">
                        {{ strtoupper($order->payment_method ?? 'NON DÉFINI') }}
                    </span>

                </div>


                <div class="payment-body">

                    <div class="method-box">

                        <span class="method-badge">
                            💵
                        </span>

                        <div class="method-text">

                            <span class="method-title">
                                {{ strtoupper($order->payment_method ?? 'MODE DE PAIEMENT NON DÉFINI') }}
                            </span>

                            <p class="method-desc">
                                Mode de paiement enregistré sur la commande.
                            </p>

                        </div>

                    </div>


                    <div class="collection-highlight-card">

                        <div class="coll-head">

                            <span class="coll-lbl">
                                MONTANT EXACT À COLLECTER
                            </span>

                            <span class="coll-badge">
                                {{ $currentStatus === OrderStatus::Delivered
                                    ? 'ENCAISSÉ'
                                    : 'EXIGIBLE'
                                }}
                            </span>

                        </div>


                        <div class="coll-amount-row">

                            <span class="coll-amount">
                                {{ number_format((float) $order->total, 0, ',', ' ') }}
                            </span>

                            <span class="coll-curr">
                                DA
                            </span>

                        </div>


                        <span class="coll-sub">

                            STATUT COMMANDE :
                            {{ $currentStatusLabel }}

                        </span>

                    </div>


                    <div class="reconciliation-row">

                        <span class="reconcil-status">
                            <span class="dot-dim">●</span>
                            STATUT ENCAISSEMENT
                        </span>

                        <span class="reconcil-val">
                            {{ $currentStatus === OrderStatus::Delivered
                                ? 'ENCAISSÉ'
                                : 'EN ATTENTE'
                            }}
                        </span>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 CARTE 3 : INFORMATIONS COMMANDE
            ================================================== --}}

            <section class="ops-panel side-panel cert-panel">

                <div class="panel-header-row">

                    <div class="panel-title-group">

                        <span class="panel-icon red">
                            #
                        </span>

                        <h2 class="panel-title">
                            INFORMATIONS COMMANDE
                        </h2>

                    </div>

                </div>


                <div class="cert-body">

                    <div class="collection-tag-row">

                        <span class="cert-coll-label">
                            RÉFÉRENCE
                        </span>

                        <span class="drop-pill">
                            #{{ $order->reference ?? $order->id }}
                        </span>

                    </div>


                    <h3 class="series-hero-name">
                        {{ $currentStatusLabel }}
                    </h3>


                    <p class="series-desc">
                        Cette commande utilise le workflow officiel
                        des statuts NOAD.
                    </p>


                    <div class="cert-specs-grid">

                        <div class="spec-node">

                            <span class="spec-lbl">
                                STATUT ACTUEL
                            </span>

                            <div class="spec-val-row">

                                <span class="spec-val">
                                    {{ strtoupper($currentStatusValue ?? 'INCONNU') }}
                                </span>

                            </div>

                        </div>


                        <div class="spec-node">

                            <span class="spec-lbl">
                                ARTICLES
                            </span>

                            <div class="spec-val-row">

                                <span class="spec-val">
                                    {{ $itemsCount }}
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="hash-block">

                        <span class="hash-label">
                            CRÉÉE LE
                        </span>

                        <code class="hash-code">
                            {{ $order->created_at?->format('d/m/Y H:i:s') ?? 'DATE INCONNUE' }}
                        </code>

                    </div>

                </div>

            </section>

        </div>

    </div>


    {{-- =========================================================
         04. PIED DE PAGE TECHNIQUE
    ========================================================== --}}

    <footer class="order-footer-system">

        <div class="footer-left">

            <span>
                NOAD ADMINISTRATION
            </span>

            <span class="sep">/</span>

            <span>
                COMMANDE #{{ $order->reference ?? $order->id }}
            </span>

        </div>


        <div class="footer-right">

            <a href="#top" class="btn-scroll-top">
                HAUT DE PAGE ↑
            </a>

        </div>

    </footer>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}

<style>

.admin-order-detail-page {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #0c0c0c;
}

.admin-order-detail-page a {
    color: inherit;
    text-decoration: none;
}

.mono {
    font-family: monospace;
}


/* =============================================================
   MESSAGES
============================================================= */

.admin-feedback {
    margin-bottom: 20px;
    padding: 12px 16px;
    border: 1px solid var(--border, #242424);
    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.06em;
}

.admin-feedback.success {
    border-color: #2ecc71;
    color: #2ecc71;
}

.admin-feedback.error {
    border-color: var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
}


/* =============================================================
   BARRE SYSTÈME
============================================================= */

.terminal-status-strip {
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

.status-left {
    display: flex;
    align-items: center;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bread-link {
    color: #888;
    transition: color 0.2s;
}

.bread-link:hover {
    color: #fff;
}

.bread-sep {
    color: #444;
}

.bread-current {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}

.status-right {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #777;
}

.server-node-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #aaa;
}

.dot-live {
    color: var(--accent, #d32f2f);
    font-size: 9px;
}

.sep {
    color: #333;
}


/* =============================================================
   HEADER COMMANDE
============================================================= */

.order-main-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 24px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 20px;
}

.head-info {
    max-width: 800px;
}

.order-title-stack {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.order-code-title {
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 900;
    letter-spacing: 0.03em;
    line-height: 1.05;
    margin: 0;
    color: #fff;
}

.order-sub-proto {
    font-family: monospace;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: #777;
    font-weight: bold;
}

.badges-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.status-pill {
    border: 1px solid;
    font-family: monospace;
    font-size: 9px;
    font-weight: bold;
    padding: 3px 8px;
    letter-spacing: 0.1em;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-pill.pending {
    background-color: rgba(180, 140, 40, 0.12);
    border-color: #b48c28;
    color: #b48c28;
}

.status-pill.paid {
    background-color: rgba(46, 204, 113, 0.12);
    border-color: #2ecc71;
    color: #2ecc71;
}

.status-pill.shipped {
    background-color: rgba(80, 130, 200, 0.12);
    border-color: #5082c8;
    color: #5082c8;
}

.status-pill.delivered {
    background-color: rgba(46, 204, 113, 0.12);
    border-color: #2ecc71;
    color: #2ecc71;
}

.status-pill.cancelled,
.status-pill.unknown {
    background-color: rgba(211, 47, 47, 0.15);
    border-color: var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
}

.gen-meta {
    font-size: 11px;
    color: #777;
}

.gen-meta strong {
    color: #aaa;
}

.head-actions-grid {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-head-action {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    color: #ccc;
    padding: 10px 14px;
    font-family: monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-head-action:hover {
    color: #fff;
    border-color: #666;
}

.btn-head-danger {
    background-color: transparent;
    border: 1px solid var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
    padding: 10px 14px;
    font-family: monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-head-danger:hover {
    background-color: var(--accent, #d32f2f);
    color: #fff;
}

.inline-form {
    margin: 0;
}


/* =============================================================
   GRID PRINCIPALE
============================================================= */

.order-layout-grid {
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    gap: 24px;
    align-items: flex-start;
    margin-bottom: 40px;
}

.col-left-flow,
.col-right-cards {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

@media (max-width: 1100px) {
    .order-layout-grid {
        grid-template-columns: 1fr;
    }
}


/* =============================================================
   PANNEAUX
============================================================= */

.ops-panel {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    padding: 24px;
}

.panel-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 14px;
    margin-bottom: 18px;
    gap: 12px;
}

.panel-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel-icon {
    font-size: 12px;
    opacity: 0.7;
}

.panel-icon.red {
    color: var(--accent, #d32f2f);
    opacity: 1;
}

.panel-title {
    font-size: 14px;
    font-weight: 900;
    letter-spacing: 0.1em;
    margin: 0;
    color: #fff;
    text-transform: uppercase;
}

.panel-meta-tag {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: 0.1em;
}


/* =============================================================
   ARTICLES
============================================================= */

.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.order-item-row {
    display: grid;
    grid-template-columns: 60px 1fr auto;
    gap: 16px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 14px 18px;
    align-items: center;
}

.item-thumb-col {
    position: relative;
    width: 60px;
}

.thumb-box {
    width: 60px;
    height: 70px;
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    display: flex;
    align-items: center;
    justify-content: center;
}

.thumb-placeholder {
    font-family: monospace;
    font-size: 10px;
    color: #444;
    font-weight: bold;
}

.stock-tag {
    position: absolute;
    top: -6px;
    left: -6px;
    font-family: monospace;
    font-size: 7px;
    padding: 1px 4px;
    z-index: 2;
    background-color: #1a1a1a;
    border: 1px solid #333;
    color: #aaa;
}

.item-info-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.item-product-name {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.05em;
    margin: 0;
}

.item-specs-line {
    font-family: monospace;
    font-size: 10px;
    color: #888;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.item-specs-line strong {
    color: #fff;
}

.item-fabric-desc {
    font-size: 11px;
    color: #666;
    line-height: 1.35;
}

.item-numbers-col {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.item-qty {
    font-family: monospace;
    font-size: 10px;
    color: #777;
}

.item-price {
    font-family: monospace;
    font-size: 16px;
    font-weight: 900;
    color: #fff;
}

.empty-items-state {
    padding: 30px 15px;
    border: 1px dashed #333;
    text-align: center;
    font-family: monospace;
    font-size: 10px;
    color: #666;
}


/* =============================================================
   FINANCES
============================================================= */

.order-financial-breakdown {
    border-top: 1px solid var(--border, #242424);
    padding-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.fin-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: monospace;
    font-size: 11px;
    color: #888;
    gap: 20px;
}

.fin-val {
    color: #fff;
    font-weight: bold;
    white-space: nowrap;
}

.fin-total-hero {
    margin-top: 12px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    border-left: 4px solid var(--accent, #d32f2f);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}

.total-title-stack {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.hero-total-label {
    font-size: 13px;
    font-weight: 900;
    letter-spacing: 0.08em;
    color: #fff;
    text-transform: uppercase;
}

.hero-total-sub {
    font-size: 10px;
    color: #666;
}

.hero-total-amount {
    font-family: monospace;
    font-size: 32px;
    font-weight: 900;
    color: var(--accent, #d32f2f);
}


/* =============================================================
   DISPATCH
============================================================= */

.dispatch-fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 18px;
}

.field-block {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-block.full-width {
    grid-column: 1 / -1;
    margin-bottom: 20px;
}

.field-head-meta {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
}

.field-block label {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.1em;
    color: #888;
    text-transform: uppercase;
}

.carrier-tag,
.private-tag {
    font-family: monospace;
    font-size: 8px;
    color: #666;
    letter-spacing: 0.08em;
}

.input-copy-wrap {
    display: flex;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
}

.input-copy-wrap input {
    background: transparent;
    border: none;
    color: #fff;
    font-family: monospace;
    font-size: 12px;
    font-weight: bold;
    padding: 10px 12px;
    width: 100%;
    outline: none;
}

.btn-copy-input {
    background-color: #141414;
    border: none;
    border-left: 1px solid var(--border, #242424);
    color: #aaa;
    padding: 0 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-copy-input:hover {
    color: #fff;
    background-color: #202020;
}

.select-box select {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #fff;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 12px;
    font-weight: 700;
    padding: 10px 12px;
    width: 100%;
    outline: none;
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.select-box select:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.field-block textarea {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #fff;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 12px;
    padding: 10px 12px;
    outline: none;
    resize: vertical;
}

.field-subtext {
    font-size: 10px;
    color: #666;
    line-height: 1.3;
}

.dispatch-submit-row {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.btn-submit-status {
    background-color: #fff;
    color: #000;
    border: 1px solid #fff;
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.12em;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit-status:hover {
    background-color: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
    color: #fff;
}

.no-transition-message {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: 0.08em;
}


/* =============================================================
   TIMELINE
============================================================= */

.timeline-stack {
    display: flex;
    flex-direction: column;
}

.timeline-entry {
    display: grid;
    grid-template-columns: 32px 1fr;
    gap: 16px;
}

.timeline-glyph-col {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.glyph-badge {
    width: 24px;
    height: 24px;
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    color: #aaa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    flex-shrink: 0;
}

.glyph-badge.red {
    background-color: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
    color: #fff;
}

.timeline-vertical-line {
    width: 1px;
    flex-grow: 1;
    background-color: #242424;
    margin: 6px 0;
}

.timeline-content-col {
    padding-bottom: 24px;
}

.entry-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4px;
    flex-wrap: wrap;
    gap: 8px;
}

.entry-title {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.05em;
    margin: 0;
}

.entry-time {
    font-family: monospace;
    font-size: 10px;
    color: #777;
}

.entry-time.red {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}

.entry-desc {
    font-size: 11px;
    color: #888;
    margin: 0 0 6px;
    line-height: 1.4;
}

.entry-meta {
    font-family: monospace;
    font-size: 9px;
    color: #555;
    letter-spacing: 0.08em;
}


/* =============================================================
   DESTINATAIRE
============================================================= */

.dest-top-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 14px;
    margin-bottom: 16px;
}

.dest-sub-label {
    font-family: monospace;
    font-size: 9px;
    color: #777;
    letter-spacing: 0.1em;
    display: block;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.dest-hero-name {
    font-size: 18px;
    font-weight: 900;
    letter-spacing: 0.06em;
    color: #fff;
    margin: 0 0 2px;
}

.btn-call-dest {
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    color: #fff;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
}

.btn-call-dest:hover {
    background-color: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
}

.dest-field-block {
    margin-bottom: 16px;
}

.wilaya-highlight-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 10px 14px;
}

.wilaya-text-col {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.wilaya-name {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
}

.address-box {
    display: flex;
    gap: 10px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 12px 14px;
    align-items: flex-start;
}

.address-icon {
    color: #666;
    font-size: 11px;
    margin-top: 2px;
}

.address-lines {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 11px;
}

.text-white {
    color: #fff;
}

.contact-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 10px 14px;
    gap: 10px;
}

.phone-number,
.email-address {
    font-family: monospace;
    font-size: 11px;
    font-weight: bold;
    color: #fff;
    word-break: break-word;
}

.btn-mini-send {
    color: #888;
    font-size: 12px;
    transition: color 0.2s;
}

.btn-mini-send:hover {
    color: #fff;
}


/* =============================================================
   PAIEMENT
============================================================= */

.method-box {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 18px;
}

.method-badge {
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    padding: 6px;
    font-size: 14px;
}

.method-title {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    display: block;
    margin-bottom: 4px;
}

.method-desc {
    font-size: 10px;
    color: #777;
    margin: 0;
    line-height: 1.35;
}

.collection-highlight-card {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 16px;
    margin-bottom: 16px;
}

.coll-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.coll-lbl {
    font-family: monospace;
    font-size: 9px;
    color: #888;
    letter-spacing: 0.1em;
}

.coll-badge {
    background-color: var(--accent, #d32f2f);
    color: #fff;
    font-family: monospace;
    font-size: 8px;
    font-weight: bold;
    padding: 1px 5px;
}

.coll-amount-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 6px;
}

.coll-amount {
    font-family: monospace;
    font-size: 30px;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}

.coll-curr {
    font-family: monospace;
    font-size: 18px;
    font-weight: 900;
    color: var(--accent, #d32f2f);
}

.coll-sub {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    display: block;
}

.reconciliation-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: monospace;
    font-size: 9px;
    border-top: 1px solid #181818;
    padding-top: 10px;
    gap: 10px;
}

.reconcil-status {
    color: #777;
}

.dot-dim {
    color: #555;
}

.reconcil-val {
    color: #aaa;
    font-weight: bold;
}


/* =============================================================
   INFORMATIONS COMMANDE
============================================================= */

.cert-body {
    display: flex;
    flex-direction: column;
}

.collection-tag-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.cert-coll-label {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: 0.1em;
}

.drop-pill {
    background-color: #1a1a1a;
    border: 1px solid #333;
    font-family: monospace;
    font-size: 8px;
    color: #fff;
    padding: 1px 5px;
}

.series-hero-name {
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 0.08em;
    color: #fff;
    margin: 0 0 6px;
}

.series-desc {
    font-size: 11px;
    color: #777;
    margin: 0 0 18px;
    line-height: 1.4;
}

.cert-specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}

.spec-node {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.spec-lbl {
    font-family: monospace;
    font-size: 8px;
    color: #666;
    letter-spacing: 0.1em;
}

.spec-val-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.spec-val {
    font-family: monospace;
    font-size: 14px;
    font-weight: 900;
    color: #fff;
}

.hash-block {
    background-color: #080808;
    border: 1px solid var(--border, #242424);
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.hash-label {
    font-family: monospace;
    font-size: 8px;
    color: #666;
    letter-spacing: 0.1em;
}

.hash-code {
    font-family: monospace;
    font-size: 9px;
    color: #888;
    word-break: break-all;
}


/* =============================================================
   FOOTER
============================================================= */

.order-footer-system {
    border-top: 1px solid var(--border, #242424);
    padding-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: monospace;
    font-size: 9px;
    color: #555;
    flex-wrap: wrap;
    gap: 12px;
}

.footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-scroll-top {
    color: #777;
    transition: color 0.2s;
}

.btn-scroll-top:hover {
    color: #fff;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 700px) {

    .admin-order-detail-page {
        padding: 12px 14px 50px;
    }

    .status-right {
        width: 100%;
        justify-content: flex-start;
    }

    .order-item-row {
        grid-template-columns: 60px 1fr;
    }

    .item-numbers-col {
        grid-column: 2;
        align-items: flex-start;
    }

    .dispatch-fields-grid {
        grid-template-columns: 1fr;
    }

    .field-block.full-width {
        grid-column: auto;
    }

    .cert-specs-grid {
        grid-template-columns: 1fr;
    }

    .fin-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 4px;
    }

    .panel-header-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .footer-left {
        flex-wrap: wrap;
    }

}

</style>

@endsection