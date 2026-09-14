@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DONNÉES DYNAMIQUES
    |--------------------------------------------------------------------------
    */

    $dropsCollection = collect($drops ?? []);

    $allDropsCount = $dropsCollection->count();

    $activeDrops = $dropsCollection->filter(
        fn ($drop) => $drop->status === 'active'
    );

    $upcomingDrops = $dropsCollection->filter(
        fn ($drop) => $drop->status === 'upcoming'
    );

    $archivedDrops = $dropsCollection->filter(
        fn ($drop) => $drop->status === 'ended'
    );

    $activeDropsCount = $activeDrops->count();
    $upcomingDropsCount = $upcomingDrops->count();
    $archivedDropsCount = $archivedDrops->count();

    /*
    |--------------------------------------------------------------------------
    | PREMIER DROP À METTRE EN AVANT
    |--------------------------------------------------------------------------
    |
    | Priorité :
    | 1. Premier drop actif
    | 2. Sinon premier drop disponible
    |
    */

    $featuredDrop = $activeDrops->first() ?? $dropsCollection->first();
@endphp


<div class="noad-calendar-drops-page">

    {{-- =========================================================
         01. BARRE TECHNIQUE SUPÉRIEURE
    ========================================================== --}}

    <div class="tech-top-bar">

        <div class="container-fluid-custom tech-bar-inner">

            <div class="tech-left">

                <span class="dot-red-sq">
                    ■
                </span>

                <span class="font-mono">
                    SYS.STATUS: OPERATIONAL PROTOCOL // ALGER - ZONE 01
                </span>

            </div>

            <div class="tech-right font-mono">

                <span>
                    ALLOCATION TOTALE RESTREINTE
                </span>

                <span class="meta-sep">
                    •
                </span>

                <span class="text-accent font-bold">
                    DEV/DA ONLY
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
         02. EN-TÊTE
    ========================================================== --}}

    <div class="container-custom">

        <div class="page-header-block">

            <div class="breadcrumbs-row font-mono">

                <a href="{{ route('home') }}">
                    ACCUEIL
                </a>

                <span class="sep">
                    /
                </span>

                <a href="{{ route('drops.index') }}">
                    DROPS
                </a>

                <span class="sep">
                    /
                </span>

                <span class="active">
                    CALENDRIER STRATÉGIQUE
                </span>

                <span class="tag-meta">
                    TERRACE APPAREL CODES // NO ADVANTAGE
                </span>

            </div>


            <div class="header-action-split">

                <div class="header-titles">

                    <h1 class="main-title">
                        CALENDRIER DES DROPS
                    </h1>

                    <p class="main-lead">
                        Éditions numérotées, pièces restreintes et manifestes
                        vestimentaires. L'accès anticipé requiert une validation whitelist.
                    </p>

                </div>


                {{-- =================================================
                     FILTRES DYNAMIQUES
                ================================================== --}}

                <div class="status-filters font-mono">

                    <button
                        type="button"
                        class="btn-filter active"
                        data-filter="all"
                    >
                        TOUS LES DROPS [{{ str_pad($allDropsCount, 2, '0', STR_PAD_LEFT) }}]
                    </button>

                    <button
                        type="button"
                        class="btn-filter"
                        data-filter="active"
                    >
                        ACTIFS [{{ str_pad($activeDropsCount, 2, '0', STR_PAD_LEFT) }}]
                    </button>

                    <button
                        type="button"
                        class="btn-filter"
                        data-filter="upcoming"
                    >
                        À VENIR [{{ str_pad($upcomingDropsCount, 2, '0', STR_PAD_LEFT) }}]
                    </button>

                    <button
                        type="button"
                        class="btn-filter"
                        data-filter="ended"
                    >
                        ARCHIVES [{{ str_pad($archivedDropsCount, 2, '0', STR_PAD_LEFT) }}]
                    </button>

                </div>

            </div>

        </div>


        {{-- =========================================================
             03. TOUS LES DROPS
        ========================================================== --}}

        <div id="drops-list">

            @forelse($dropsCollection as $index => $drop)

                @php
                    $isFeatured = $index === 0;

                    $dropStatus = $drop->status ?? 'upcoming';

                    $statusLabel = match ($dropStatus) {
                        'active' => 'EN COURS',
                        'upcoming' => 'À VENIR',
                        'ended' => 'ARCHIVÉ',
                        default => strtoupper($dropStatus),
                    };

                    $statusClass = match ($dropStatus) {
                        'active' => 'status-active',
                        'upcoming' => 'status-upcoming',
                        'ended' => 'status-ended',
                        default => 'status-unknown',
                    };

                    $dropImage = $drop->banner_image
                        ?? $drop->image
                        ?? 'images/drops/drop01-resistance.jpg';

                    $productsCount = $drop->products_count
                        ?? ($drop->products?->count() ?? 0);

                    $startDate = $drop->start_date;
                    $endDate = $drop->end_date;

                    $dropUrl = route('drops.show', $drop->slug);
                @endphp


                {{-- =================================================
                     PREMIER DROP : FEATURED
                ================================================== --}}

                @if($isFeatured)

                    <article
                        class="hero-drop-card drop-item"
                        data-status="{{ $dropStatus }}"
                    >

                        {{-- VISUEL --}}

                        <div
                            class="hero-media-col"
                            style="
                                background-image:
                                    url('{{ asset($dropImage) }}');
                            "
                        >

                            <div class="media-overlay"></div>

                            <div class="media-top-badges">

                                <span class="badge-live-pulse {{ $statusClass }}">

                                    <span class="pulse-dot">
                                        ●
                                    </span>

                                    {{ $statusLabel }}

                                </span>

                                <span class="badge-numbered font-mono">

                                    DROP
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}

                                </span>

                            </div>


                            <div class="media-bottom-coords font-mono">

                                NOAD // DROP SYSTEM

                            </div>

                        </div>


                        {{-- INFORMATIONS --}}

                        <div class="hero-info-col">

                            <div class="hero-info-header">

                                <div class="series-meta font-mono">

                                    <span>
                                        {{ $drop->season
                                            ? strtoupper($drop->season)
                                            : 'NOAD DROP'
                                        }}
                                    </span>

                                    <span>
                                        SERIES //
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                </div>


                                <div class="delivery-status-tag font-mono">

                                    {{ strtoupper($statusLabel) }}

                                </div>


                                <h2 class="hero-drop-title">

                                    {{ strtoupper($drop->name) }}

                                </h2>


                                @if($drop->description)

                                    <p class="hero-drop-manifesto">

                                        {{ $drop->description }}

                                    </p>

                                @endif

                            </div>


                            {{-- TÉLÉMÉTRIE --}}

                            <div class="allocation-tracker">

                                <div class="tracker-header font-mono">

                                    <span class="tracker-title">

                                        <span class="glyph-box">
                                            ▤
                                        </span>

                                        STATUT DU DROP

                                    </span>

                                    <span class="tracker-percent text-accent font-bold">

                                        {{ strtoupper($statusLabel) }}

                                    </span>

                                </div>


                                <div class="tracker-bar">

                                    @php
                                        $statusProgress = match ($dropStatus) {
                                            'active' => 65,
                                            'upcoming' => 15,
                                            'ended' => 100,
                                            default => 0,
                                        };
                                    @endphp

                                    <div
                                        class="tracker-fill"
                                        style="width: {{ $statusProgress }}%;"
                                    ></div>

                                </div>


                                <div class="tracker-footer font-mono">

                                    <span>

                                        @if($startDate)
                                            {{ $startDate->format('d.m.Y') }}
                                        @else
                                            DATE À VENIR
                                        @endif

                                        —

                                        @if($endDate)
                                            {{ $endDate->format('d.m.Y') }}
                                        @else
                                            —
                                        @endif

                                    </span>


                                    <span class="text-accent font-bold">

                                        {{ $productsCount }}
                                        {{ $productsCount > 1 ? 'PRODUITS' : 'PRODUIT' }}

                                    </span>

                                </div>

                            </div>


                            {{-- INFORMATIONS PRODUITS --}}

                            <div class="featured-pieces-grid">

                                <div class="piece-box">

                                    <span class="piece-type font-mono">
                                        ÉTAT
                                    </span>

                                    <h4 class="piece-name">
                                        {{ strtoupper($statusLabel) }}
                                    </h4>

                                    <span class="piece-price font-mono font-bold">
                                        {{ $productsCount }}
                                        {{ $productsCount > 1 ? 'PRODUITS' : 'PRODUIT' }}
                                    </span>

                                </div>


                                <div class="piece-box">

                                    <span class="piece-type font-mono">
                                        PÉRIODE
                                    </span>

                                    <h4 class="piece-name">

                                        @if($startDate && $endDate)

                                            {{ $startDate->format('d.m.Y') }}
                                            —
                                            {{ $endDate->format('d.m.Y') }}

                                        @elseif($startDate)

                                            À PARTIR DU
                                            {{ $startDate->format('d.m.Y') }}

                                        @else

                                            DATE À VENIR

                                        @endif

                                    </h4>

                                    <span class="piece-price font-mono font-bold">
                                        NOAD
                                    </span>

                                </div>

                            </div>


                            {{-- CTA --}}

                            <div class="hero-actions-row">

                                <a
                                    href="{{ $dropUrl }}"
                                    class="btn-cta-primary font-mono"
                                >

                                    ACCÉDER AU DROP

                                    <span class="arrow-glyph">
                                        →
                                    </span>

                                </a>

                                <a
                                    href="{{ $dropUrl }}"
                                    class="btn-cta-ghost font-mono"
                                >

                                    VOIR LES DÉTAILS

                                    <span class="glyph-doc">
                                        ↗
                                    </span>

                                </a>

                            </div>

                        </div>

                    </article>

                @else

                    {{-- =================================================
                         AUTRES DROPS
                    ================================================== --}}

                    <article
                        class="upcoming-card drop-item dynamic-drop-card"
                        data-status="{{ $dropStatus }}"
                    >

                        <div
                            class="upcoming-thumb
                                {{ $dropStatus === 'ended' ? 'locked-thumb' : '' }}"
                            style="
                                background-image:
                                    url('{{ asset($dropImage) }}');
                            "
                        >

                            <div class="upcoming-overlay"></div>


                            <span
                                class="
                                    upcoming-badge
                                    font-mono
                                    {{ $dropStatus === 'ended'
                                        ? 'badge-gray'
                                        : ''
                                    }}
                                "
                            >

                                {{ strtoupper($statusLabel) }}

                            </span>


                            <span class="upcoming-release font-mono">

                                @if($startDate && $endDate)

                                    {{ $startDate->format('d.m.Y') }}
                                    —
                                    {{ $endDate->format('d.m.Y') }}

                                @elseif($startDate)

                                    À PARTIR DU
                                    {{ $startDate->format('d.m.Y') }}

                                @else

                                    DATE À VENIR

                                @endif

                            </span>


                            <div class="upcoming-volume-pill font-mono">

                                <span>
                                    CONTENU
                                </span>

                                <strong>
                                    {{ $productsCount }}
                                    {{ $productsCount > 1 ? 'PRODUITS' : 'PRODUIT' }}
                                </strong>

                            </div>

                        </div>


                        <div class="upcoming-body">

                            <div class="cycle-meta-row font-mono">

                                <span>
                                    DROP //
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <span
                                    class="
                                        {{ $dropStatus === 'active'
                                            ? 'text-accent'
                                            : 'text-muted'
                                        }}
                                    "
                                >
                                    {{ strtoupper($statusLabel) }}
                                </span>

                            </div>


                            <h3 class="upcoming-title">

                                {{ strtoupper($drop->name) }}

                            </h3>


                            @if($drop->description)

                                <p class="upcoming-desc">

                                    {{ $drop->description }}

                                </p>

                            @else

                                <p class="upcoming-desc">

                                    Édition NOAD numérotée.
                                    Consultez la fiche du drop pour découvrir
                                    les pièces disponibles et les conditions d'accès.

                                </p>

                            @endif


                            <div class="tech-spec-row font-mono">

                                <div class="spec-col">

                                    <span class="spec-k">
                                        STATUT
                                    </span>

                                    <span class="spec-v">
                                        {{ strtoupper($statusLabel) }}
                                    </span>

                                </div>


                                <div class="spec-col">

                                    <span class="spec-k">
                                        PRODUITS
                                    </span>

                                    <span class="spec-v">
                                        {{ $productsCount }}
                                    </span>

                                </div>


                                <div class="spec-col">

                                    <span class="spec-k">
                                        CODE
                                    </span>

                                    <span class="spec-v">
                                        {{ $drop->code ?? '—' }}
                                    </span>

                                </div>

                            </div>


                            <a
                                href="{{ $dropUrl }}"
                                class="btn-whitelist-apply font-mono"
                            >

                                VOIR LE DROP

                                <span class="shield-glyph">
                                    →
                                </span>

                            </a>

                        </div>

                    </article>

                @endif

            @empty

                {{-- =================================================
                     AUCUN DROP
                ================================================== --}}

                <div class="drops-empty-state">

                    <span class="empty-code font-mono">
                        SYS.DROPS // 404
                    </span>

                    <h2>
                        AUCUN DROP DISPONIBLE
                    </h2>

                    <p>
                        Aucun drop n'est actuellement enregistré
                        dans le calendrier NOAD.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- =========================================================
             04. LES 3 ENGAGEMENTS OPÉRATIONNELS
        ========================================================== --}}

        <div class="commitments-strip">

            <div class="commitment-item">

                <span class="commit-number font-bold text-accent">
                    01
                </span>

                <div class="commit-body">

                    <h5 class="commit-title font-mono">
                        CANDIDATURE VÉRIFIÉE
                    </h5>

                    <p class="commit-desc">
                        Les accès anticipés sont réservés aux utilisateurs
                        autorisés selon les conditions du drop.
                    </p>

                </div>

            </div>


            <div class="commitment-item">

                <span class="commit-number font-bold text-accent">
                    02
                </span>

                <div class="commit-body">

                    <h5 class="commit-title font-mono">
                        FENÊTRE PRIORITAIRE
                    </h5>

                    <p class="commit-desc">
                        Les membres validés peuvent accéder aux éditions
                        restreintes avant leur ouverture publique.
                    </p>

                </div>

            </div>


            <div class="commitment-item">

                <span class="commit-number font-bold text-accent">
                    03
                </span>

                <div class="commit-body">

                    <h5 class="commit-title font-mono">
                        ÉDITIONS LIMITÉES
                    </h5>

                    <p class="commit-desc">
                        Chaque drop est pensé comme une édition distincte
                        avec une disponibilité contrôlée.
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         05. FOOTER
         Aucun route fantôme.
    ========================================================== --}}

    <footer class="noad-global-footer">

        <div class="container-custom">

            <div class="footer-newsletter-row">

                <div class="footer-brand-lockup">

                    <div class="noad-logo font-mono">

                        <span>
                            NO
                        </span>

                        <span>
                            AD
                        </span>

                    </div>


                    <div class="brand-text">

                        <span class="brand-name">
                            NOAD
                        </span>

                        <span class="brand-sub">
                            NO ADVANTAGE
                        </span>

                        <span class="brand-motto font-mono">
                            DEFEND YOUR PRINCIPLES
                        </span>

                    </div>

                </div>

            </div>


            <div class="footer-links-nav font-mono">

                <a href="{{ route('shop.index') }}">
                    BOUTIQUE
                </a>

                <a
                    href="{{ route('drops.index') }}"
                    class="active"
                >
                    DROPS
                </a>

            </div>


            <div class="footer-legal-bar font-mono">

                <div class="legal-links">

                    <span>
                        NOAD — NO ADVANTAGE
                    </span>

                    <span class="sep">
                        /
                    </span>

                    <span>
                        DEFEND YOUR PRINCIPLES
                    </span>

                </div>


                <div class="social-links">

                    <a href="#">
                        INSTAGRAM
                    </a>

                    <a href="#">
                        TIKTOK
                    </a>

                    <a href="#">
                        YOUTUBE
                    </a>

                    <a href="#">
                        X
                    </a>

                </div>

            </div>

        </div>

    </footer>

</div>


<style>

/* ==========================================================================
   NOAD DROPS CALENDAR
   ========================================================================== */

.noad-calendar-drops-page {
    width: 100%;
    min-height: 100vh;
    background-color: #0c0c0c;
    color: #e5e5e5;
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    box-sizing: border-box;
}

.noad-calendar-drops-page *,
.noad-calendar-drops-page *::before,
.noad-calendar-drops-page *::after {
    box-sizing: border-box;
}

.noad-calendar-drops-page a {
    color: inherit;
    text-decoration: none;
}

.font-mono {
    font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
}

.font-bold {
    font-weight: 700;
}

.text-accent {
    color: #d32f2f !important;
}

.text-muted {
    color: #777777 !important;
}

.container-custom {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 40px;
}

.container-fluid-custom {
    max-width: 100%;
    padding: 0 40px;
}


/* ==========================================================================
   01. BARRE TECHNIQUE
   ========================================================================== */

.tech-top-bar {
    background-color: #070707;
    border-bottom: 1px solid #1a1a1a;
    padding: 10px 0;
    font-size: 10px;
    letter-spacing: 0.12em;
}

.tech-bar-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.tech-left {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #999999;
}

.dot-red-sq {
    color: #d32f2f;
    font-size: 8px;
}

.tech-right {
    color: #777777;
    display: flex;
    gap: 8px;
}

.meta-sep {
    color: #444444;
}


/* ==========================================================================
   02. HEADER
   ========================================================================== */

.page-header-block {
    padding: 36px 0 32px;
}

.breadcrumbs-row {
    font-size: 10px;
    letter-spacing: 0.15em;
    color: #777777;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.breadcrumbs-row a:hover {
    color: #ffffff;
}

.breadcrumbs-row .sep {
    color: #444444;
}

.breadcrumbs-row .active {
    color: #ffffff;
    font-weight: bold;
}

.breadcrumbs-row .tag-meta {
    margin-left: 12px;
    color: #d32f2f;
    background-color: #140d0d;
    padding: 2px 6px;
    border: 1px solid #331515;
}

.header-action-split {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
    flex-wrap: wrap;
}

.main-title {
    font-size: clamp(40px, 5.2vw, 68px);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.95;
    margin: 0 0 12px;
    color: #ffffff;
    text-transform: uppercase;
}

.main-lead {
    font-size: 14px;
    color: #888888;
    max-width: 680px;
    margin: 0;
    line-height: 1.5;
}


/* ==========================================================================
   FILTRES
   ========================================================================== */

.status-filters {
    display: flex;
    gap: 6px;
    background-color: #101010;
    border: 1px solid #202020;
    padding: 4px;
    flex-wrap: wrap;
}

.btn-filter {
    background: transparent;
    border: none;
    color: #888888;
    font-family: inherit;
    font-size: 10px;
    letter-spacing: 0.12em;
    padding: 8px 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-filter:hover {
    color: #ffffff;
}

.btn-filter.active {
    background-color: #ffffff;
    color: #000000;
    font-weight: bold;
}


/* ==========================================================================
   HERO FEATURED
   ========================================================================== */

.hero-drop-card {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    background-color: #121212;
    border: 1px solid #242424;
    margin-bottom: 64px;
}

.hero-media-col {
    position: relative;
    min-height: 480px;
    background-size: cover;
    background-position: center;
    background-color: #161616;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px;
}

.media-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            180deg,
            rgba(12, 12, 12, 0.3) 0%,
            rgba(12, 12, 12, 0.1) 40%,
            rgba(12, 12, 12, 0.92) 100%
        );
    z-index: 1;
}

.media-top-badges,
.media-bottom-coords {
    position: relative;
    z-index: 2;
}

.badge-live-pulse {
    background-color: #d32f2f;
    color: #ffffff;
    font-family: monospace;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 0.12em;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 8px;
}

.badge-live-pulse.status-ended {
    background-color: #333333;
}

.badge-live-pulse.status-upcoming {
    background-color: #666666;
}

.badge-numbered {
    background-color: rgba(0, 0, 0, 0.8);
    border: 1px solid #333333;
    color: #cccccc;
    font-size: 9px;
    padding: 6px 10px;
    display: inline-block;
}

.media-bottom-coords {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #888888;
}

.hero-info-col {
    padding: 44px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.series-meta {
    font-size: 10px;
    letter-spacing: 0.2em;
    color: #777777;
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

.delivery-status-tag {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #2ecc71;
    font-weight: bold;
    margin-bottom: 8px;
}

.hero-drop-title {
    font-size: clamp(34px, 4.2vw, 50px);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 1;
    margin: 0 0 16px;
    color: #ffffff;
    text-transform: uppercase;
}

.hero-drop-manifesto {
    font-size: 13px;
    color: #888888;
    line-height: 1.55;
    margin: 0 0 28px;
}


/* ==========================================================================
   ALLOCATION
   ========================================================================== */

.allocation-tracker {
    background-color: #0c0c0c;
    border: 1px solid #202020;
    padding: 16px 20px;
    margin-bottom: 24px;
}

.tracker-header {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    letter-spacing: 0.12em;
    margin-bottom: 10px;
    gap: 15px;
}

.tracker-bar {
    width: 100%;
    height: 4px;
    background-color: #1a1a1a;
    overflow: hidden;
    margin-bottom: 10px;
}

.tracker-fill {
    height: 100%;
    background-color: #d32f2f;
}

.tracker-footer {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    font-size: 9px;
    color: #777777;
    letter-spacing: 0.1em;
}


/* ==========================================================================
   INFORMATIONS
   ========================================================================== */

.featured-pieces-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 32px;
}

.piece-box {
    background-color: #0e0e0e;
    border: 1px solid #1f1f1f;
    padding: 14px 16px;
}

.piece-type {
    font-size: 8px;
    letter-spacing: 0.15em;
    color: #666666;
    display: block;
    margin-bottom: 4px;
}

.piece-name {
    font-size: 14px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 8px;
    letter-spacing: 0.04em;
}

.piece-price {
    font-size: 14px;
    color: #ffffff;
}


/* ==========================================================================
   CTA
   ========================================================================== */

.hero-actions-row {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.btn-cta-primary {
    background-color: #ffffff;
    color: #000000;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    padding: 14px 24px;
    border: 1px solid #ffffff;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-cta-primary:hover {
    background-color: #d32f2f;
    border-color: #d32f2f;
    color: #ffffff;
}

.btn-cta-ghost {
    background-color: transparent;
    color: #aaaaaa;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 0.12em;
    padding: 14px 20px;
    border: 1px solid #282828;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-cta-ghost:hover {
    color: #ffffff;
    border-color: #666666;
}


/* ==========================================================================
   CARTES DYNAMIQUES
   ========================================================================== */

.dynamic-drop-card {
    margin-bottom: 28px;
}

.upcoming-card {
    background-color: #121212;
    border: 1px solid #242424;
    display: flex;
    flex-direction: column;
}

.upcoming-thumb {
    min-height: 300px;
    position: relative;
    background-size: cover;
    background-position: center;
    background-color: #1a1a1a;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.upcoming-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.2) 0%,
            rgba(0, 0, 0, 0.8) 100%
        );
}

.upcoming-badge {
    position: relative;
    z-index: 2;
    background-color: #ffffff;
    color: #000000;
    font-size: 9px;
    font-weight: bold;
    padding: 4px 10px;
    width: fit-content;
}

.upcoming-badge.badge-gray {
    background-color: #222222;
    color: #888888;
    border: 1px solid #333333;
}

.upcoming-release {
    position: relative;
    z-index: 2;
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #cccccc;
}

.upcoming-volume-pill {
    position: absolute;
    right: 20px;
    bottom: 20px;
    z-index: 2;
    background: rgba(14, 14, 14, 0.9);
    border: 1px solid #333333;
    padding: 6px 12px;
    font-size: 8px;
    text-align: right;
}

.upcoming-volume-pill strong {
    display: block;
    font-size: 11px;
    color: #ffffff;
}

.upcoming-body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.cycle-meta-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    font-size: 9px;
    letter-spacing: 0.12em;
    color: #777777;
    margin-bottom: 8px;
}

.upcoming-title {
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0 0 12px;
    color: #ffffff;
    text-transform: uppercase;
}

.upcoming-desc {
    font-size: 13px;
    color: #888888;
    line-height: 1.5;
    margin: 0 0 24px;
}

.tech-spec-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-top: 1px solid #1a1a1a;
    border-bottom: 1px solid #1a1a1a;
    padding: 12px 0;
    margin-bottom: 24px;
}

.spec-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.spec-k {
    font-size: 8px;
    letter-spacing: 0.12em;
    color: #666666;
}

.spec-v {
    font-size: 12px;
    color: #ffffff;
}

.btn-whitelist-apply {
    background-color: #ffffff;
    color: #000000;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    padding: 14px;
    text-align: center;
    border: 1px solid #ffffff;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: auto;
}

.btn-whitelist-apply:hover {
    background-color: #d32f2f;
    border-color: #d32f2f;
    color: #ffffff;
}


/* ==========================================================================
   ÉTAT VIDE
   ========================================================================== */

.drops-empty-state {
    border: 1px solid #242424;
    background-color: #101010;
    padding: 80px 40px;
    text-align: center;
    margin-bottom: 64px;
}

.empty-code {
    display: block;
    color: #d32f2f;
    font-size: 10px;
    letter-spacing: 0.18em;
    margin-bottom: 14px;
}

.drops-empty-state h2 {
    margin: 0 0 10px;
    color: #ffffff;
    font-size: 32px;
    letter-spacing: 0.05em;
}

.drops-empty-state p {
    margin: 0;
    color: #777777;
    font-size: 13px;
}


/* ==========================================================================
   ENGAGEMENTS
   ========================================================================== */

.commitments-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    border-top: 1px solid #202020;
    padding: 48px 0;
    margin-bottom: 40px;
}

.commitment-item {
    display: flex;
    gap: 20px;
}

.commit-number {
    font-size: 38px;
    line-height: 1;
}

.commit-title {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.12em;
    margin: 0 0 8px;
    color: #ffffff;
}

.commit-desc {
    font-size: 12px;
    color: #777777;
    line-height: 1.5;
    margin: 0;
}


/* ==========================================================================
   FOOTER
   ========================================================================== */

.noad-global-footer {
    background-color: #080808;
    border-top: 1px solid #1a1a1a;
    padding: 56px 0 36px;
}

.footer-newsletter-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 40px;
    margin-bottom: 32px;
}

.footer-brand-lockup {
    display: flex;
    gap: 16px;
    align-items: center;
}

.noad-logo {
    display: flex;
    flex-direction: column;
    font-size: 16px;
    font-weight: 900;
    line-height: 1;
    border: 1px solid #333333;
    padding: 6px 8px;
    color: #ffffff;
}

.brand-name {
    font-size: 18px;
    font-weight: 900;
    letter-spacing: 0.1em;
    display: block;
    color: #ffffff;
}

.brand-sub {
    font-size: 10px;
    letter-spacing: 0.15em;
    color: #777777;
    display: block;
}

.brand-motto {
    font-size: 9px;
    letter-spacing: 0.12em;
    color: #d32f2f;
    display: block;
    margin-top: 4px;
}

.footer-links-nav {
    display: flex;
    gap: 28px;
    font-size: 11px;
    letter-spacing: 0.15em;
    color: #888888;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.footer-links-nav a:hover {
    color: #ffffff;
}

.footer-links-nav a.active {
    color: #d32f2f;
    font-weight: bold;
}

.footer-legal-bar {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    letter-spacing: 0.12em;
    color: #666666;
    flex-wrap: wrap;
    gap: 16px;
}

.legal-links {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.legal-links a:hover,
.social-links a:hover {
    color: #ffffff;
}

.social-links {
    display: flex;
    gap: 16px;
}


/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 980px) {

    .hero-drop-card {
        grid-template-columns: 1fr;
    }

    .hero-media-col {
        min-height: 420px;
    }

}

@media (max-width: 860px) {

    .commitments-strip {
        grid-template-columns: 1fr;
        gap: 32px;
    }

}

@media (max-width: 760px) {

    .container-custom,
    .container-fluid-custom {
        padding-left: 20px;
        padding-right: 20px;
    }

    .tech-bar-inner {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .hero-info-col {
        padding: 32px 24px;
    }

    .featured-pieces-grid {
        grid-template-columns: 1fr;
    }

    .tech-spec-row {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .tracker-footer {
        flex-direction: column;
    }

    .footer-legal-bar {
        flex-direction: column;
    }

}

@media (max-width: 560px) {

    .main-title {
        font-size: 40px;
    }

    .status-filters {
        width: 100%;
    }

    .btn-filter {
        flex: 1;
        min-width: 45%;
    }

    .hero-media-col {
        min-height: 340px;
        padding: 16px;
    }

    .hero-info-col {
        padding: 24px 18px;
    }

    .hero-actions-row {
        flex-direction: column;
    }

    .btn-cta-primary,
    .btn-cta-ghost {
        width: 100%;
        justify-content: center;
    }

    .footer-brand-lockup {
        align-items: flex-start;
    }

}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterButtons = document.querySelectorAll('.btn-filter');
    const dropItems = document.querySelectorAll('.drop-item');

    filterButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const filter = this.dataset.filter;

            filterButtons.forEach(function (item) {
                item.classList.remove('active');
            });

            this.classList.add('active');


            dropItems.forEach(function (drop) {

                const status = drop.dataset.status;

                if (filter === 'all' || status === filter) {
                    drop.style.display = '';
                } else {
                    drop.style.display = 'none';
                }

            });

        });

    });

});
</script>

@endsection