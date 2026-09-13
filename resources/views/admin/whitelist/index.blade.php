@extends('layouts.app')

@section('content')

<div class="admin-whitelist-page">

    {{-- =========================================================
         01. BARRE SUPÉRIEURE / SOUS-NAVIGATION ADMIN
    ========================================================== --}}

    <nav class="admin-subnav-strip">

        <div class="subnav-links">

            @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" class="subnav-item">
                    TABLEAU DE BORD
                </a>
            @endif

            <a href="{{ route('admin.whitelist.index') }}" class="subnav-item active">
                WHITELIST

                <span class="badge-counter">
                    {{ $pendingCount }}
                </span>
            </a>

            @if(Route::has('admin.drops.index'))
                <a href="{{ route('admin.drops.index') }}" class="subnav-item">
                    GESTION DROPS
                </a>
            @endif

            <a href="#audit-log" class="subnav-item">
                JOURNAL D'AUDIT
            </a>

            <a href="#anti-bot" class="subnav-item">
                REGISTRE ANTI-BOT
            </a>

        </div>

        <div class="subnav-meta">

            <span class="system-status">
                <span class="dot-live">●</span>
                SYSTÈME EN LIGNE // V.2.4B
            </span>

            <span class="session-id">
                SESSION ADMIN
            </span>

        </div>

    </nav>


    {{-- =========================================================
         02. EN-TÊTE
    ========================================================== --}}

    <header class="whitelist-head-section">

        <div class="head-left-col">

            <span class="protocol-tag">
                TERRACE PROTOCOL // CONTRÔLE D'ACCÈS ANTI-REVENTE
            </span>

            <h1 class="page-title">
                GESTION DES CANDIDATURES WHITELIST
            </h1>

            <p class="page-lead">
                Validation manuelle et automatisée des accès réservés.
                Filtre strict d'intégrité anti-scalping et assignation
                univoque des codes d'acquisition.
            </p>

        </div>

        <div class="head-right-col">

            <div class="countdown-widget-card">

                <div class="countdown-head">
                    <span class="timer-icon">⏱</span>

                    <span class="countdown-label">
                        CLÔTURE DROP
                    </span>
                </div>

                <div class="countdown-digits">
                    <span class="timer-value">
                        ACTIVE
                    </span>
                </div>

            </div>

        </div>

    </header>


    {{-- =========================================================
         03. KPIs
    ========================================================== --}}

    <section class="whitelist-kpi-grid">

        <div class="kpi-card">

            <div class="kpi-header-row">

                <div class="kpi-index-wrap">
                    <span class="kpi-num">01 //</span>
                    <span class="kpi-label">VOLUME GLOBAL</span>
                </div>

                <span class="kpi-glyph">⬡</span>

            </div>

            <div class="kpi-value-row">

                <span class="kpi-val">
                    {{ number_format($totalCount, 0, ',', ' ') }}
                </span>

            </div>

            <span class="kpi-subtext">
                CANDIDATURES TOTALES SOUMISES
            </span>

        </div>


        <div class="kpi-card">

            <div class="kpi-header-row">

                <div class="kpi-index-wrap">
                    <span class="kpi-num">02 //</span>
                    <span class="kpi-label">SÉCURISÉES</span>
                </div>

                <span class="kpi-glyph secure">🛡</span>

            </div>

            <div class="kpi-value-row">

                <span class="kpi-val">
                    {{ number_format($approvedCount, 0, ',', ' ') }}
                </span>

            </div>

            <span class="kpi-subtext">
                APPROUVÉES
            </span>

        </div>


        <div class="kpi-card alert-card">

            <div class="kpi-header-row">

                <div class="kpi-index-wrap">
                    <span class="kpi-num alert">03 //</span>
                    <span class="kpi-label">EN VÉRIFICATION</span>
                </div>

                <span class="kpi-glyph alert">📋</span>

            </div>

            <div class="kpi-value-row">

                <span class="kpi-val alert-val">
                    {{ number_format($pendingCount, 0, ',', ' ') }}
                </span>

            </div>

            <span class="kpi-subtext">
                EN ATTENTE DE REVUE
            </span>

        </div>


        <div class="kpi-card">

            <div class="kpi-header-row">

                <div class="kpi-index-wrap">
                    <span class="kpi-num">04 //</span>
                    <span class="kpi-label">NEUTRALISÉES</span>
                </div>

                <span class="kpi-glyph">🛡</span>

            </div>

            <div class="kpi-value-row">

                <span class="kpi-val">
                    {{ number_format($rejectedCount, 0, ',', ' ') }}
                </span>

            </div>

            <span class="kpi-subtext">
                CANDIDATURES REJETÉES
            </span>

        </div>

    </section>


    {{-- =========================================================
         04. FILTRES
    ========================================================== --}}

    <div class="whitelist-toolbar-container">

        <form
            action="{{ route('admin.whitelist.index') }}"
            method="GET"
            class="toolbar-top-row"
        >

            <div class="search-input-box">

                <span class="search-lens">
                    🔍
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="RECHERCHE CANDIDAT, EMAIL, TÉLÉPHONE OU WILAYA..."
                    class="input-search-tactical"
                >

            </div>


            <div class="filter-tabs-group">

                <a
                    href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                    class="tab-pill {{ !request('status') ? 'active' : '' }}"
                >
                    TOUS ({{ $totalCount }})
                </a>

                <a
                    href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}"
                    class="tab-pill {{ request('status') === 'pending' ? 'active' : '' }}"
                >
                    EN ATTENTE ({{ $pendingCount }})
                </a>

                <a
                    href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}"
                    class="tab-pill {{ request('status') === 'approved' ? 'active' : '' }}"
                >
                    APPROUVÉS ({{ $approvedCount }})
                </a>

                <a
                    href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}"
                    class="tab-pill {{ request('status') === 'rejected' ? 'active' : '' }}"
                >
                    REJETÉS ({{ $rejectedCount }})
                </a>

            </div>


            <div class="drop-selector-box">

                <select
                    name="drop_id"
                    class="select-drop-picker"
                    onchange="this.form.submit()"
                >

                    <option value="">
                        TOUS LES DROPS
                    </option>

                    <option
                        value="2"
                        {{ request('drop_id') == 2 ? 'selected' : '' }}
                    >
                        DROP 02
                    </option>

                    <option
                        value="1"
                        {{ request('drop_id') == 1 ? 'selected' : '' }}
                    >
                        DROP 01
                    </option>

                </select>

            </div>

        </form>


        <div class="toolbar-bottom-row">

            <div class="selection-status-indicator">

                <span class="label-muted">
                    RÉSULTATS ACTUELS :
                </span>

                <span class="badge-selection-count">
                    {{ $candidates->count() }}
                    CANDIDATS
                </span>

            </div>


            <div class="bulk-actions-buttons">

                @if(Route::has('admin.whitelist.bulk'))
                    <form
                        action="{{ route('admin.whitelist.bulk') }}"
                        method="POST"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="btn-bulk-solid"
                        >
                            <span class="icon">✓</span>
                            APPROUVER LA SÉLECTION
                        </button>

                    </form>
                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
         05. TABLEAU
    ========================================================== --}}

    <div class="whitelist-table-wrapper">

        <table class="whitelist-table">

            <thead>

                <tr>

                    <th class="col-checkbox">
                        #
                    </th>

                    <th>
                        CANDIDAT // CONTACT
                    </th>

                    <th>
                        WILAYA
                    </th>

                    <th>
                        PRODUIT
                    </th>

                    <th>
                        DATE
                    </th>

                    <th>
                        STATUT
                    </th>

                    <th class="text-right">
                        ACTIONS
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($candidates as $candidate)

                    <tr>

                        <td class="col-checkbox">
                            {{ $candidate->id }}
                        </td>


                        <td>

                            <div class="candidate-identity-stack">

                                <span class="candidate-fullname">
                                    {{ strtoupper($candidate->fullname ?? $candidate->name ?? 'CANDIDAT') }}
                                </span>

                                <span class="candidate-email">
                                    {{ $candidate->email ?? '—' }}
                                </span>

                                <span class="candidate-phone">
                                    {{ $candidate->phone ?? '—' }}
                                </span>

                            </div>

                        </td>


                        <td>

                            <div class="locality-stack">

                                <span class="wilaya-title">
                                    {{ $candidate->wilaya ?? '—' }}
                                </span>

                            </div>

                        </td>


                        <td>

                            <div class="product-target-stack">

                                <span class="product-name">
                                    {{ $candidate->product_name ?? '—' }}
                                </span>

                                @if(isset($candidate->variant))
                                    <span class="product-variant">
                                        {{ $candidate->variant }}
                                    </span>
                                @endif

                            </div>

                        </td>


                        <td>

                            <span class="candidate-email">

                                {{ optional($candidate->created_at)->format('d/m/Y H:i') }}

                            </span>

                        </td>


                        <td>

                            @if($candidate->status === 'approved')

                                <span class="status-badge approved">
                                    ✓ APPROUVÉ
                                </span>

                            @elseif($candidate->status === 'rejected')

                                <span class="status-badge rejected">
                                    ⊘ REJETÉ
                                </span>

                            @else

                                <span class="status-badge pending">
                                    ■ EN ATTENTE
                                </span>

                            @endif

                        </td>


                        <td class="text-right">

                            <div class="decision-actions-row">

                                @if($candidate->status !== 'approved' && Route::has('admin.whitelist.approve'))

                                    <form
                                        action="{{ route('admin.whitelist.approve', $candidate->id) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn-decision-valider"
                                        >
                                            VALIDER
                                        </button>

                                    </form>

                                @endif


                                @if($candidate->status !== 'rejected' && Route::has('admin.whitelist.reject'))

                                    <form
                                        action="{{ route('admin.whitelist.reject', $candidate->id) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn-decision-refuser"
                                        >
                                            REFUSER
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            style="text-align:center; padding:40px; color:#777;"
                        >
                            AUCUNE CANDIDATURE TROUVÉE.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>


        {{-- Pagination Laravel --}}

        @if($candidates->hasPages())

            <div class="table-pagination-footer">

                <div class="pagination-info">

                    AFFICHAGE :
                    {{ $candidates->firstItem() ?? 0 }}
                    À
                    {{ $candidates->lastItem() ?? 0 }}
                    SUR
                    {{ $candidates->total() }}

                </div>

                <div class="pagination-controls">

                    {{ $candidates->withQueryString()->links() }}

                </div>

            </div>

        @endif

    </div>


    {{-- =========================================================
         06. JOURNAL D'AUDIT
    ========================================================== --}}

    <footer
        class="whitelist-audit-section"
        id="audit-log"
    >

        <div class="audit-section-header">

            <div class="audit-header-title">

                <span class="terminal-glyph">
                    📜
                </span>

                <span class="audit-title-text">
                    JOURNAL D'AUDIT DES VALIDATIONS
                </span>

            </div>

            <div class="audit-live-status">

                <span class="dot-live">
                    ●
                </span>

                SYSTÈME ACTIF

            </div>

        </div>


        <div class="audit-cards-grid">

            <div class="audit-event-card">

                <div class="event-card-head">

                    <span class="event-time">
                        SYSTEM
                    </span>

                    <span class="event-status-tag success">
                        ONLINE
                    </span>

                </div>

                <p class="event-description">
                    Le système de gestion des candidatures
                    whitelist est opérationnel.
                </p>

                <div class="event-card-foot">

                    <span>
                        NOAD ADMIN
                    </span>

                    <span>
                        SECURE
                    </span>

                </div>

            </div>


            <div class="audit-event-card">

                <div class="event-card-head">

                    <span class="event-time">
                        MODERATION
                    </span>

                    <span class="event-status-tag alert">
                        ACTIVE
                    </span>

                </div>

                <p class="event-description">
                    Les candidatures peuvent être validées
                    ou rejetées par les administrateurs autorisés.
                </p>

                <div class="event-card-foot">

                    <span>
                        ACCESS CONTROL
                    </span>

                    <span>
                        ENABLED
                    </span>

                </div>

            </div>


            <div class="audit-event-card">

                <div class="event-card-head">

                    <span class="event-time">
                        DATABASE
                    </span>

                    <span class="event-status-tag muted">
                        CONNECTED
                    </span>

                </div>

                <p class="event-description">
                    Les données affichées proviennent directement
                    de la table des candidatures whitelist.
                </p>

                <div class="event-card-foot">

                    <span>
                        MYSQL
                    </span>

                    <span>
                        ACTIVE
                    </span>

                </div>

            </div>

        </div>

    </footer>

</div>


{{-- =========================================================
     CSS
========================================================== --}}

<style>

.admin-whitelist-page {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #0c0c0c;
}

.admin-whitelist-page a {
    color: inherit;
    text-decoration: none;
}

.admin-subnav-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #242424;
    padding-bottom: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 14px;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
}

.subnav-links {
    display: flex;
    gap: 18px;
    align-items: center;
    flex-wrap: wrap;
}

.subnav-item {
    color: #777;
    display: flex;
    align-items: center;
    gap: 6px;
}

.subnav-item:hover {
    color: #fff;
}

.subnav-item.active {
    color: #fff;
    font-weight: bold;
    border-bottom: 2px solid var(--accent, #d32f2f);
    padding-bottom: 4px;
}

.badge-counter {
    background: var(--accent, #d32f2f);
    color: #fff;
    padding: 1px 5px;
    font-size: 9px;
    font-weight: 800;
}

.subnav-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    color: #666;
}

.system-status {
    color: #fff;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dot-live {
    color: var(--accent, #d32f2f);
    font-size: 9px;
}

.whitelist-head-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #242424;
    padding-bottom: 24px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 20px;
}

.head-left-col {
    max-width: 860px;
}

.protocol-tag {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .15em;
    color: var(--accent, #d32f2f);
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
}

.page-title {
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 900;
    letter-spacing: .03em;
    line-height: 1.05;
    margin: 0 0 8px;
    color: #fff;
    text-transform: uppercase;
}

.page-lead {
    font-size: 13px;
    color: #888;
    margin: 0;
    line-height: 1.45;
}

.countdown-widget-card {
    background: #101010;
    border: 1px solid #242424;
    padding: 14px 20px;
    min-width: 220px;
}

.countdown-head {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: monospace;
    font-size: 10px;
    color: #888;
}

.countdown-digits {
    margin-top: 6px;
    font-family: monospace;
    font-size: 20px;
    font-weight: 900;
    color: #fff;
}

.whitelist-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

@media(max-width:1050px) {
    .whitelist-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:600px) {
    .whitelist-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-card {
    background: #101010;
    border: 1px solid #242424;
    padding: 18px 20px;
}

.kpi-card.alert-card {
    border-top: 2px solid var(--accent, #d32f2f);
}

.kpi-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.kpi-index-wrap {
    display: flex;
    gap: 6px;
}

.kpi-num,
.kpi-label,
.kpi-subtext {
    font-family: monospace;
}

.kpi-num {
    font-size: 10px;
    color: #666;
}

.kpi-num.alert {
    color: var(--accent, #d32f2f);
}

.kpi-label {
    font-size: 9px;
    color: #888;
}

.kpi-glyph {
    color: #555;
}

.kpi-glyph.alert {
    color: var(--accent, #d32f2f);
}

.kpi-val {
    font-family: monospace;
    font-size: 34px;
    font-weight: 900;
    color: #fff;
}

.kpi-val.alert-val {
    color: var(--accent, #d32f2f);
}

.kpi-subtext {
    display: block;
    margin-top: 6px;
    font-size: 9px;
    color: #666;
}

.whitelist-toolbar-container {
    background: #101010;
    border: 1px solid #242424;
    padding: 16px 20px;
    margin-bottom: 24px;
}

.toolbar-top-row {
    display: grid;
    grid-template-columns: 1.8fr auto auto;
    gap: 16px;
    align-items: center;
}

@media(max-width:1100px) {
    .toolbar-top-row {
        grid-template-columns: 1fr;
    }
}

.search-input-box {
    display: flex;
    align-items: center;
    background: #0c0c0c;
    border: 1px solid #242424;
    padding: 0 12px;
}

.input-search-tactical {
    width: 100%;
    background: transparent;
    border: none;
    color: #fff;
    font-family: monospace;
    font-size: 10px;
    padding: 10px 0;
    outline: none;
}

.filter-tabs-group {
    display: flex;
    gap: 4px;
    background: #0c0c0c;
    border: 1px solid #242424;
    padding: 4px;
}

.tab-pill {
    padding: 6px 12px;
    font-family: monospace;
    font-size: 9px;
    color: #777;
    white-space: nowrap;
}

.tab-pill.active {
    background: #1a1a1a;
    color: #fff;
}

.select-drop-picker {
    background: #0c0c0c;
    border: 1px solid #242424;
    color: #fff;
    padding: 10px 14px;
    font-family: monospace;
}

.toolbar-bottom-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #181818;
    padding-top: 14px;
    margin-top: 16px;
    flex-wrap: wrap;
    gap: 14px;
}

.selection-status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: monospace;
    font-size: 9px;
}

.label-muted {
    color: #666;
}

.badge-selection-count {
    background: #161616;
    border: 1px solid #242424;
    color: #ccc;
    padding: 3px 8px;
}

.bulk-actions-buttons form {
    margin: 0;
}

.btn-bulk-solid,
.btn-decision-valider {
    background: #fff;
    color: #000;
    border: 1px solid #fff;
    cursor: pointer;
    font-weight: 900;
}

.btn-bulk-solid {
    padding: 9px 16px;
    font-size: 10px;
}

.whitelist-table-wrapper {
    background: #101010;
    border: 1px solid #242424;
    margin-bottom: 32px;
    overflow-x: auto;
}

.whitelist-table {
    width: 100%;
    border-collapse: collapse;
    font-family: monospace;
    font-size: 10px;
    text-align: left;
}

.whitelist-table th {
    background: #0c0c0c;
    padding: 12px 14px;
    color: #666;
    font-size: 8px;
    border-bottom: 1px solid #242424;
}

.whitelist-table td {
    padding: 16px 14px;
    border-bottom: 1px solid #161616;
    vertical-align: middle;
}

.whitelist-table tr:hover td {
    background: #141414;
}

.col-checkbox {
    width: 40px;
    text-align: center;
}

.candidate-identity-stack,
.locality-stack,
.product-target-stack {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.candidate-fullname,
.product-name,
.wilaya-title {
    color: #fff;
    font-weight: 900;
}

.candidate-fullname {
    font-size: 13px;
}

.candidate-email,
.candidate-phone,
.locality-sub,
.product-variant {
    font-size: 9px;
    color: #777;
}

.status-badge {
    display: inline-flex;
    padding: 4px 8px;
    font-size: 8px;
    font-weight: 800;
}

.status-badge.pending {
    background: rgba(211,47,47,.12);
    border: 1px solid var(--accent,#d32f2f);
    color: var(--accent,#d32f2f);
}

.status-badge.approved {
    background: #161616;
    border: 1px solid #333;
    color: #ccc;
}

.status-badge.rejected {
    background: #1a1a1a;
    border: 1px solid #333;
    color: #888;
}

.decision-actions-row {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
}

.decision-actions-row form {
    margin: 0;
}

.btn-decision-valider,
.btn-decision-refuser {
    padding: 6px 10px;
    font-family: monospace;
    font-size: 9px;
}

.btn-decision-refuser {
    background: #121212;
    border: 1px solid #242424;
    color: #aaa;
    cursor: pointer;
}

.text-right {
    text-align: right;
}

.table-pagination-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-top: 1px solid #242424;
    font-family: monospace;
    font-size: 9px;
    color: #666;
    flex-wrap: wrap;
    gap: 12px;
}

.whitelist-audit-section {
    border-top: 1px solid #242424;
    padding-top: 24px;
}

.audit-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-family: monospace;
    font-size: 9px;
}

.audit-header-title {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #888;
}

.terminal-glyph {
    color: var(--accent,#d32f2f);
}

.audit-live-status {
    color: #2ecc71;
}

.audit-cards-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 16px;
}

@media(max-width:900px) {
    .audit-cards-grid {
        grid-template-columns: 1fr;
    }
}

.audit-event-card {
    background: #101010;
    border: 1px solid #242424;
    padding: 14px 16px;
    min-height: 100px;
}

.event-card-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-family: monospace;
    font-size: 9px;
}

.event-time {
    color: #888;
}

.event-status-tag {
    font-size: 8px;
    font-weight: bold;
}

.event-status-tag.success {
    color: #2ecc71;
}

.event-status-tag.alert {
    color: var(--accent,#d32f2f);
}

.event-status-tag.muted {
    color: #aaa;
}

.event-description {
    font-size: 11px;
    color: #777;
    line-height: 1.4;
}

.event-card-foot {
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #161616;
    padding-top: 6px;
    font-family: monospace;
    font-size: 8px;
    color: #555;
}

</style>

@endsection