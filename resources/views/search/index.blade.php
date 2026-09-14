```blade
@extends('layouts.app')

@section('content')

<div class="noad-search-experience">

    {{-- =========================================================
         01. EN-TÊTE SYSTÈME / PROTOCOLE
    ========================================================== --}}

    <div class="search-sys-strip">

        <div class="container-search sys-flex">

            <div class="sys-meta-left font-mono">
                <span class="dot-red-sq">■</span>
                ARCHIVE SYSTÈME // PROTOCOLE RECHERCHE
            </div>

            <div class="sys-meta-right font-mono">
                VERSION 01.04
            </div>

        </div>

    </div>


    <div class="container-search">

        {{-- =====================================================
             02. CHAMP DE RECHERCHE PRINCIPAL
        ====================================================== --}}

        <div class="search-input-section">

            <form
                action="{{ route('search.index') }}"
                method="GET"
                class="search-main-form"
            >

                <div class="search-bar-wrap">

                    <span class="search-ico font-mono">
                        ⌕
                    </span>

                    <input
                        type="text"
                        name="q"
                        value="{{ request('q', 'JACKET') }}"
                        placeholder="RECHERCHER UNE PIÈCE, MATIÈRE, DROP..."
                        class="search-giant-input"
                        autocomplete="off"
                        autofocus
                    >

                    @if(request('q'))
                        <a
                            href="{{ route('search.index') }}"
                            class="search-clear-btn"
                            title="Effacer la recherche"
                        >
                            ✕
                        </a>
                    @endif

                </div>

            </form>


            {{-- Filtres et compteur --}}

            <div class="search-filter-bar">

                <div class="filter-tabs-group font-mono">

                    <a
                        href="{{ route('search.index', [
                            'q' => request('q', 'JACKET'),
                            'filter' => 'all'
                        ]) }}"
                        class="filter-tab {{ (!request('filter') || request('filter') == 'all') ? 'active' : '' }}"
                    >
                        TOUT ({{ $productsCount ?? 4 }})
                    </a>

                    <a
                        href="{{ route('search.index', [
                            'q' => request('q', 'JACKET'),
                            'filter' => 'vestes'
                        ]) }}"
                        class="filter-tab {{ request('filter') == 'vestes' ? 'active' : '' }}"
                    >
                        VESTES (2)
                    </a>

                    <a
                        href="{{ route('search.index', [
                            'q' => request('q', 'JACKET'),
                            'filter' => 'drops'
                        ]) }}"
                        class="filter-tab {{ request('filter') == 'drops' ? 'active' : '' }}"
                    >
                        DROPS (1)
                    </a>

                    <a
                        href="{{ route('search.index', [
                            'q' => request('q', 'JACKET'),
                            'filter' => 'archives'
                        ]) }}"
                        class="filter-tab {{ request('filter') == 'archives' ? 'active' : '' }}"
                    >
                        ARCHIVES (1)
                    </a>

                </div>


                <div class="results-tally font-mono">

                    <span class="tally-count font-bold">
                        {{ $productsCount ?? 4 }} RÉSULTATS TROUVÉS
                    </span>

                    POUR "{{ strtoupper(request('q', 'JACKET')) }}"

                </div>

            </div>

        </div>


        {{-- =====================================================
             03. SECTION 01 : PRODUITS CORRESPONDANTS
        ====================================================== --}}

        <section class="search-results-section">

            <div class="section-title-strip font-mono">

                <div class="section-title-left">

                    <span class="sec-num">
                        SECTION 01
                    </span>

                    <h2 class="sec-heading">
                        PRODUITS CORRESPONDANTS
                    </h2>

                </div>

                <div class="section-title-right">
                    CATALOGUE PIÈCES
                </div>

            </div>


            <div class="search-products-grid">

                {{-- Produit 01 : Harrington Jacket --}}

                <div class="search-product-card">

                    <div class="card-thumb-holder">

                        <span class="tactical-tag tag-red font-mono">
                            DROP 01 // ARCHIVE
                        </span>

                        <img
                            src="{{ asset('images/products/harrington-matrix.jpg') }}"
                            alt="Harrington Jacket"
                        >

                        <button
                            type="button"
                            class="btn-quick-plus"
                            title="Ajouter à la sélection"
                        >
                            +
                        </button>

                    </div>


                    <div class="card-content-holder">

                        <span class="prod-spec font-mono">
                            VESTE COUPE DROITE
                        </span>

                        <h3 class="prod-name">
                            HARRINGTON JACKET
                        </h3>

                        <div class="prod-row-bottom font-mono">

                            <span class="prod-color">
                                BLACK
                            </span>

                            <span class="prod-price font-bold">
                                16 000 DA
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Produit 02 : Track Jacket --}}

                <div class="search-product-card">

                    <div class="card-thumb-holder">

                        <span class="tactical-tag tag-gray font-mono">
                            ESSENTIEL
                        </span>

                        <img
                            src="{{ asset('images/products/track-jacket-stadium.jpg') }}"
                            alt="Track Jacket"
                        >

                        <button
                            type="button"
                            class="btn-quick-plus"
                            title="Ajouter à la sélection"
                        >
                            +
                        </button>

                    </div>


                    <div class="card-content-holder">

                        <span class="prod-spec font-mono">
                            TECHNICAL TRAINING
                        </span>

                        <h3 class="prod-name">
                            TRACK JACKET
                        </h3>

                        <div class="prod-row-bottom font-mono">

                            <span class="prod-color">
                                CHARCOAL
                            </span>

                            <span class="prod-price font-bold">
                                14 000 DA
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Produit 03 : Anorak Jacket --}}

                <div class="search-product-card">

                    <div class="card-thumb-holder">

                        <span class="tactical-tag tag-gray font-mono">
                            TECHWEAR
                        </span>

                        <img
                            src="{{ asset('images/products/anorak-jacket-olive.jpg') }}"
                            alt="Anorak Jacket"
                        >

                        <button
                            type="button"
                            class="btn-quick-plus"
                            title="Ajouter à la sélection"
                        >
                            +
                        </button>

                    </div>


                    <div class="card-content-holder">

                        <span class="prod-spec font-mono">
                            WATERPROOF MEMBRANE
                        </span>

                        <h3 class="prod-name">
                            ANORAK JACKET
                        </h3>

                        <div class="prod-row-bottom font-mono">

                            <span class="prod-color">
                                OLIVE
                            </span>

                            <span class="prod-price font-bold">
                                18 000 DA
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Produit 04 : Utility Jacket --}}

                <div class="search-product-card">

                    <div class="card-thumb-holder">

                        <span class="tactical-tag tag-red font-mono">
                            LIMITÉ
                        </span>

                        <img
                            src="{{ asset('images/products/utility-jacket-black.jpg') }}"
                            alt="Utility Jacket"
                        >

                        <button
                            type="button"
                            class="btn-quick-plus"
                            title="Ajouter à la sélection"
                        >
                            +
                        </button>

                    </div>


                    <div class="card-content-holder">

                        <span class="prod-spec font-mono">
                            HEAVYWEIGHT CANVAS
                        </span>

                        <h3 class="prod-name">
                            UTILITY JACKET
                        </h3>

                        <div class="prod-row-bottom font-mono">

                            <span class="prod-color">
                                BLACK
                            </span>

                            <span class="prod-price font-bold">
                                20 000 DA
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             04. SECTION 02 : DROPS & CONTENUS LIÉS
        ====================================================== --}}

        <section class="search-linked-section">

            <div class="section-title-strip font-mono">

                <div class="section-title-left">

                    <span class="sec-num">
                        SECTION 02
                    </span>

                    <h2 class="sec-heading">
                        DROPS & CONTENUS LIÉS
                    </h2>

                </div>

                <div class="section-title-right">
                    ARCHIVES ET JOURNAL
                </div>

            </div>


            <div class="linked-cards-duo">

                {{-- Carte Drop Lié --}}

                <div class="linked-drop-card">

                    <div class="linked-card-head font-mono">

                        <span class="badge-tag-red">
                            DROP ARCHIVÉ
                        </span>

                        <span class="badge-edition">
                            ÉDITION LIMITÉE
                        </span>

                    </div>


                    <h3 class="linked-drop-title">
                        DROP 01 : THE RESISTANCE
                    </h3>


                    <p class="linked-drop-desc">
                        La genèse du vestiaire NOAD.
                        Comprend l'introduction emblématique de la Harrington Jacket,
                        conçue avec une armature en toile technique et des finitions
                        britanniques austères.
                    </p>


                    <div
                        class="linked-drop-banner"
                        style="background-image: url('{{ asset('images/drops/drop01-wide-preview.jpg') }}');"
                    ></div>


                    <div class="linked-card-footer font-mono">

                        <div class="linked-piece-badge">

                            <span class="glyph-check">
                                ✓
                            </span>

                            HARRINGTON JACKET INCLUSE

                        </div>


                        <a
                            href="{{ route('drops.show', 1) }}"
                            class="btn-linked-action"
                        >
                            VOIR LE DROP
                            <span class="arrow">→</span>
                        </a>

                    </div>

                </div>


                {{-- Carte Journal --}}

                <div class="linked-journal-card">

                    <div class="linked-card-head font-mono">

                        <span class="badge-tag-dark">
                            JOURNAL NOAD
                        </span>

                        <span class="badge-reading-time">
                            LECTURE 4 MIN
                        </span>

                    </div>


                    <span class="article-category font-mono text-accent">
                        SAVOIR-FAIRE & MATIÈRES
                    </span>


                    <h3 class="linked-journal-title">
                        QUALITY IN EVERY DETAIL :
                        CONCEPTION D'UNE VESTE BRITISH CASUAL
                    </h3>


                    <p class="linked-journal-desc">
                        Exploration des techniques d'assemblage, de la sélection
                        des gabardines lourdes aux fermetures éclair en acier brossé.
                        Une plongée dans le processus de fabrication de nos vestes
                        de terrasse.
                    </p>


                    {{-- Bloc Rapport d'atelier --}}

                    <div class="report-box-callout">

                        <div class="report-logo font-mono">

                            <span>
                                NO
                            </span>

                            <span>
                                AD
                            </span>

                        </div>


                        <div class="report-text font-mono">

                            <span class="rep-title">
                                ATELIER NOAD // ARCHIVE #04
                            </span>

                            <span class="rep-sub">
                                Rapport technique de confection
                            </span>

                        </div>

                    </div>


                    <div class="linked-card-footer font-mono">

                        <a
                            href="{{ route('journal.show', 1) }}"
                            class="link-journal-read"
                        >
                            LIRE L'ARTICLE
                            <span class="arrow">→</span>
                        </a>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             05. RECHERCHES POPULAIRES
        ====================================================== --}}

        <div class="popular-searches-box font-mono">

            <div class="popular-head">

                <div class="pop-left">

                    <span class="glyph-trend text-accent">
                        📈
                    </span>

                    RECHERCHES POPULAIRES

                </div>


                <div class="pop-right text-muted">
                    SUGGESTIONS INSTANTANÉES
                </div>

            </div>


            <div class="popular-tags-list">

                <a
                    href="{{ route('search.index', ['q' => 'Cargo Pant']) }}"
                    class="pop-tag"
                >
                    CARGO PANT
                    <span class="arrow-up">↗</span>
                </a>


                <a
                    href="{{ route('search.index', ['q' => 'Logo Hoodie']) }}"
                    class="pop-tag"
                >
                    LOGO HOODIE
                    <span class="arrow-up">↗</span>
                </a>


                <a
                    href="{{ route('search.index', ['q' => 'Whitelist Drop 01']) }}"
                    class="pop-tag"
                >
                    WHITELIST DROP 01
                    <span class="arrow-up">↗</span>
                </a>


                <a
                    href="{{ route('search.index', ['q' => 'Signature Cap']) }}"
                    class="pop-tag"
                >
                    SIGNATURE CAP
                    <span class="arrow-up">↗</span>
                </a>


                <a
                    href="{{ route('search.index', ['q' => 'Polo Burgundy']) }}"
                    class="pop-tag"
                >
                    POLO BURGUNDY
                    <span class="arrow-up">↗</span>
                </a>

            </div>

        </div>


        {{-- =====================================================
             06. BANDEAU DE MARQUE
        ====================================================== --}}

        <div class="brand-statement-banner">

            <div class="statement-brand-lockup">

                <div class="brand-monogram font-mono">

                    <span>
                        NO
                    </span>

                    <span>
                        AD
                    </span>

                </div>


                <div class="statement-titles">

                    <span class="statement-name">
                        NOAD ATHLETIC & TERRACE
                    </span>

                    <span class="statement-loc font-mono">
                        ALGIERS // LONDON HERITAGE
                    </span>

                </div>

            </div>


            <div class="statement-right">

                <span class="statement-slogan font-mono">
                    DEFEND YOUR PRINCIPLES
                </span>

                <span class="statement-sub font-mono">
                    Toutes les pièces sont confectionnées selon un cahier des charges rigide.
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
         07. FOOTER GLOBAL NOAD
    ========================================================== --}}

    <footer class="noad-search-footer">

        <div class="container-search">

            <div class="footer-top-row">

                {{-- Identité de marque --}}

                <div class="brand-id-col">

                    <div class="noad-logo-sq font-mono">

                        <span>
                            NO
                        </span>

                        <span>
                            AD
                        </span>

                    </div>


                    <div class="brand-text font-mono">

                        <span class="brand-main">
                            NOAD
                        </span>

                        <span class="brand-desc">
                            NO ADVANTAGE
                        </span>

                        <span class="brand-motto text-accent">
                            DEFEND YOUR PRINCIPLES
                        </span>

                    </div>

                </div>


                {{-- Newsletter --}}

                <div class="footer-newsletter-col">

                    <span class="nl-title font-mono">
                        REJOIGNEZ LA COMMUNAUTÉ
                    </span>

                    <p class="nl-sub">
                        Recevez les prochaines sorties, drops et actualités NOAD.
                    </p>


                    <form
                        action="{{ route('newsletter.subscribe') }}"
                        method="POST"
                        class="nl-form-inline"
                    >

                        @csrf

                        <input
                            type="email"
                            name="email"
                            placeholder="Votre adresse e-mail"
                            required
                            class="input-nl font-mono"
                        >

                        <button
                            type="submit"
                            class="btn-nl-arrow font-mono"
                        >
                            →
                        </button>

                    </form>

                </div>

            </div>


            {{-- Navigation principale --}}

            <nav class="footer-links-row font-mono">

                <a href="{{ route('shop.index') }}">
                    BOUTIQUE
                </a>

                <a href="{{ route('drops.index') }}">
                    DROPS
                </a>

                <a href="{{ route('collections.index') }}">
                    COLLECTIONS
                </a>

                <a href="{{ route('journal.index') }}">
                    JOURNAL
                </a>

                <a href="{{ route('about') }}">
                    À PROPOS
                </a>

                <a href="{{ route('contact') }}">
                    CONTACT
                </a>

            </nav>


            {{-- Barre légale et réseaux --}}

            <div class="footer-legal-bar font-mono">

                <div class="legal-items">

                    <a href="{{ route('shipping') }}">
                        LIVRAISON
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('returns') }}">
                        RETOURS
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('faq') }}">
                        FAQ
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('cgv') }}">
                        CGV
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('legal') }}">
                        MENTIONS LÉGALES
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('privacy') }}">
                        POLITIQUE DE CONFIDENTIALITÉ
                    </a>

                </div>


                <div class="social-items">

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


{{-- ========================================================================
     NOAD SEARCH & RESULTS — TERRACE BRUTALISM STYLESHEET
======================================================================== --}}

<style>

    /* =====================================================================
       GLOBAL
    ====================================================================== */

    .noad-search-experience {
        width: 100%;
        min-height: 100vh;
        background-color: #0d0d0d;
        color: #e5e5e5;
        font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
        box-sizing: border-box;
    }

    .noad-search-experience a {
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

    .container-search {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 40px;
    }


    /* =====================================================================
       01. BARRE SYSTÈME
    ====================================================================== */

    .search-sys-strip {
        background-color: #070707;
        border-bottom: 1px solid #1c1c1c;
        padding: 10px 0;
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #777777;
    }

    .sys-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dot-red-sq {
        color: #d32f2f;
        font-size: 8px;
        margin-right: 6px;
    }


    /* =====================================================================
       02. CHAMP DE RECHERCHE
    ====================================================================== */

    .search-input-section {
        padding: 44px 0 32px 0;
    }

    .search-bar-wrap {
        display: flex;
        align-items: center;
        background-color: #141414;
        border: 1px solid #282828;
        padding: 16px 24px;
        margin-bottom: 24px;
        transition: border-color 0.2s;
    }

    .search-bar-wrap:focus-within {
        border-color: #555555;
    }

    .search-ico {
        font-size: 24px;
        color: #888888;
        margin-right: 16px;
    }

    .search-giant-input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #ffffff;
        font-family: 'Barlow Condensed', sans-serif;
        font-size: clamp(32px, 4vw, 46px);
        font-weight: 900;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .search-giant-input::placeholder {
        color: #333333;
    }

    .search-clear-btn {
        font-size: 18px;
        color: #666666;
        padding: 6px;
        transition: color 0.2s;
    }

    .search-clear-btn:hover {
        color: #ffffff;
    }

    .search-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #1f1f1f;
        padding-bottom: 18px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .filter-tabs-group {
        display: flex;
        gap: 8px;
    }

    .filter-tab {
        background-color: #141414;
        border: 1px solid #222222;
        color: #888888;
        font-size: 10px;
        letter-spacing: 0.12em;
        padding: 8px 16px;
        transition: all 0.2s;
    }

    .filter-tab:hover {
        color: #ffffff;
        border-color: #444444;
    }

    .filter-tab.active {
        background-color: #d32f2f;
        border-color: #d32f2f;
        color: #ffffff;
        font-weight: bold;
    }

    .results-tally {
        font-size: 11px;
        letter-spacing: 0.12em;
        color: #888888;
    }

    .tally-count {
        color: #ffffff;
    }


    /* =====================================================================
       03. PRODUITS CORRESPONDANTS
    ====================================================================== */

    .search-results-section {
        padding: 36px 0 54px 0;
    }

    .section-title-strip {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        border-bottom: 1px solid #222222;
        padding-bottom: 12px;
        margin-bottom: 24px;
    }

    .sec-num {
        font-size: 9px;
        letter-spacing: 0.2em;
        color: #d32f2f;
        display: block;
        margin-bottom: 2px;
    }

    .sec-heading {
        font-size: 22px;
        font-weight: 900;
        letter-spacing: 0.04em;
        margin: 0;
        color: #ffffff;
        text-transform: uppercase;
    }

    .section-title-right {
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #666666;
    }

    .search-products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .search-product-card {
        background-color: #121212;
        border: 1px solid #1f1f1f;
        display: flex;
        flex-direction: column;
        transition: border-color 0.2s;
    }

    .search-product-card:hover {
        border-color: #444444;
    }

    .card-thumb-holder {
        position: relative;
        height: 320px;
        background-color: #161616;
        overflow: hidden;
    }

    .card-thumb-holder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s;
    }

    .search-product-card:hover .card-thumb-holder img {
        transform: scale(1.02);
    }

    .tactical-tag {
        position: absolute;
        top: 10px;
        left: 10px;
        font-size: 8px;
        letter-spacing: 0.12em;
        padding: 3px 8px;
        z-index: 2;
    }

    .tag-red {
        background-color: #d32f2f;
        color: #ffffff;
        font-weight: bold;
    }

    .tag-gray {
        background-color: rgba(18, 18, 18, 0.9);
        border: 1px solid #333333;
        color: #cccccc;
    }

    .btn-quick-plus {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        background: rgba(0, 0, 0, 0.85);
        border: 1px solid #333333;
        color: #ffffff;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        transition: all 0.2s;
    }

    .btn-quick-plus:hover {
        background-color: #d32f2f;
        border-color: #d32f2f;
    }

    .card-content-holder {
        padding: 16px 18px;
    }

    .prod-spec {
        font-size: 8px;
        letter-spacing: 0.15em;
        color: #666666;
        display: block;
        margin-bottom: 4px;
    }

    .prod-name {
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin: 0 0 10px 0;
        color: #ffffff;
    }

    .prod-row-bottom {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
    }

    .prod-color {
        color: #777777;
        font-size: 10px;
    }

    .prod-price {
        color: #ffffff;
    }


    /* =====================================================================
       04. DROPS & CONTENUS LIÉS
    ====================================================================== */

    .search-linked-section {
        padding: 24px 0 54px 0;
    }

    .linked-cards-duo {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .linked-drop-card,
    .linked-journal-card {
        background-color: #121212;
        border: 1px solid #1f1f1f;
        padding: 32px;
        display: flex;
        flex-direction: column;
    }

    .linked-card-head {
        display: flex;
        justify-content: space-between;
        font-size: 8px;
        letter-spacing: 0.15em;
        margin-bottom: 16px;
    }

    .badge-tag-red {
        background-color: #d32f2f;
        color: #ffffff;
        padding: 3px 8px;
        font-weight: bold;
    }

    .badge-tag-dark {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        color: #cccccc;
        padding: 3px 8px;
    }

    .badge-edition,
    .badge-reading-time {
        color: #777777;
    }

    .linked-drop-title,
    .linked-journal-title {
        font-size: 24px;
        font-weight: 900;
        letter-spacing: 0.04em;
        line-height: 1.1;
        margin: 0 0 12px 0;
        color: #ffffff;
        text-transform: uppercase;
    }

    .linked-drop-desc,
    .linked-journal-desc {
        font-size: 13px;
        color: #888888;
        line-height: 1.55;
        margin: 0 0 20px 0;
    }

    .linked-drop-banner {
        height: 140px;
        background-size: cover;
        background-position: center;
        background-color: #181818;
        border: 1px solid #222222;
        margin-bottom: 24px;
    }

    .linked-card-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        letter-spacing: 0.12em;
        border-top: 1px solid #1a1a1a;
        padding-top: 18px;
    }

    .linked-piece-badge {
        color: #aaaaaa;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .glyph-check {
        color: #d32f2f;
    }

    .btn-linked-action {
        background-color: #ffffff;
        color: #000000;
        padding: 10px 18px;
        font-weight: bold;
        letter-spacing: 0.12em;
        transition: all 0.2s;
    }

    .btn-linked-action:hover {
        background-color: #d32f2f;
        color: #ffffff;
    }

    .article-category {
        font-size: 9px;
        letter-spacing: 0.15em;
        display: block;
        margin-bottom: 6px;
    }

    .report-box-callout {
        background-color: #0e0e0e;
        border: 1px solid #202020;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .report-logo {
        display: flex;
        flex-direction: column;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        border: 1px solid #333333;
        padding: 4px 6px;
        color: #ffffff;
    }

    .report-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .rep-title {
        font-size: 10px;
        color: #ffffff;
        font-weight: bold;
        letter-spacing: 0.1em;
    }

    .rep-sub {
        font-size: 9px;
        color: #666666;
    }

    .link-journal-read {
        color: #ffffff;
        font-weight: bold;
        letter-spacing: 0.15em;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }

    .link-journal-read:hover {
        color: #d32f2f;
    }


    /* =====================================================================
       05. RECHERCHES POPULAIRES
    ====================================================================== */

    .popular-searches-box {
        background-color: #121212;
        border: 1px solid #1f1f1f;
        padding: 24px 28px;
        margin-bottom: 32px;
    }

    .popular-head {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        letter-spacing: 0.15em;
        margin-bottom: 16px;
    }

    .pop-left {
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .popular-tags-list {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pop-tag {
        background-color: #161616;
        border: 1px solid #242424;
        color: #cccccc;
        font-size: 10px;
        letter-spacing: 0.12em;
        padding: 8px 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .pop-tag:hover {
        background-color: #1e1e1e;
        color: #ffffff;
        border-color: #444444;
    }

    .arrow-up {
        color: #777777;
        font-size: 12px;
    }


    /* =====================================================================
       06. BANDEAU DE MARQUE
    ====================================================================== */

    .brand-statement-banner {
        background-color: #101010;
        border: 1px solid #1f1f1f;
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 64px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .statement-brand-lockup {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .brand-monogram {
        display: flex;
        flex-direction: column;
        font-size: 14px;
        font-weight: 900;
        line-height: 1;
        border: 1px solid #333333;
        padding: 4px 6px;
        color: #ffffff;
    }

    .statement-titles {
        display: flex;
        flex-direction: column;
    }

    .statement-name {
        font-size: 14px;
        font-weight: 900;
        letter-spacing: 0.08em;
        color: #ffffff;
    }

    .statement-loc {
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #666666;
    }

    .statement-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .statement-slogan {
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.15em;
        color: #d32f2f;
    }

    .statement-sub {
        font-size: 10px;
        color: #777777;
    }


    /* =====================================================================
       07. FOOTER GLOBAL
    ====================================================================== */

    .noad-search-footer {
        background-color: #080808;
        border-top: 1px solid #1a1a1a;
        padding: 54px 0 36px 0;
    }

    .footer-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid #1a1a1a;
        padding-bottom: 36px;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 28px;
    }

    .brand-id-col {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .noad-logo-sq {
        display: flex;
        flex-direction: column;
        font-size: 16px;
        font-weight: 900;
        line-height: 1;
        border: 1px solid #282828;
        padding: 6px 8px;
        color: #ffffff;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .brand-main {
        font-size: 16px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: 0.1em;
    }

    .brand-desc {
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #666666;
    }

    .brand-motto {
        font-size: 9px;
        letter-spacing: 0.12em;
    }

    .footer-newsletter-col {
        max-width: 440px;
        width: 100%;
    }

    .nl-title {
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #ffffff;
        display: block;
        margin-bottom: 4px;
    }

    .nl-sub {
        font-size: 12px;
        color: #777777;
        margin: 0 0 14px 0;
    }

    .nl-form-inline {
        display: flex;
        border: 1px solid #222222;
    }

    .input-nl {
        flex: 1;
        background-color: #0e0e0e;
        border: none;
        outline: none;
        color: #ffffff;
        padding: 12px 14px;
        font-size: 11px;
    }

    .btn-nl-arrow {
        background-color: #ffffff;
        color: #000000;
        border: none;
        padding: 0 20px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-nl-arrow:hover {
        background-color: #d32f2f;
        color: #ffffff;
    }

    .footer-links-row {
        display: flex;
        gap: 24px;
        font-size: 11px;
        letter-spacing: 0.15em;
        color: #888888;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .footer-links-row a:hover {
        color: #ffffff;
    }

    .footer-legal-bar {
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        letter-spacing: 0.12em;
        color: #555555;
        flex-wrap: wrap;
        gap: 16px;
    }

    .legal-items {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .legal-items a:hover,
    .social-items a:hover {
        color: #ffffff;
    }

    .social-items {
        display: flex;
        gap: 16px;
    }


    /* =====================================================================
       RESPONSIVE — TABLET
    ====================================================================== */

    @media (max-width: 1080px) {

        .search-products-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }


    /* =====================================================================
       RESPONSIVE — MOBILE
    ====================================================================== */

    @media (max-width: 900px) {

        .linked-cards-duo {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 600px) {

        .container-search {
            padding: 0 20px;
        }

        .search-products-grid {
            grid-template-columns: 1fr;
        }

        .search-input-section {
            padding-top: 28px;
        }

        .search-bar-wrap {
            padding: 14px 16px;
        }

        .search-giant-input {
            font-size: 30px;
        }

        .search-filter-bar {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-tabs-group {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .filter-tab {
            flex-shrink: 0;
        }

        .section-title-strip {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        .section-title-right {
            display: none;
        }

        .linked-drop-card,
        .linked-journal-card {
            padding: 22px;
        }

        .linked-drop-title,
        .linked-journal-title {
            font-size: 20px;
        }

        .popular-searches-box {
            padding: 20px;
        }

        .popular-head {
            flex-direction: column;
            gap: 8px;
        }

        .brand-statement-banner {
            padding: 20px;
            align-items: flex-start;
        }

        .statement-right {
            text-align: left;
        }

        .footer-top-row {
            flex-direction: column;
        }

        .footer-newsletter-col {
            max-width: none;
        }

        .footer-links-row {
            gap: 16px;
        }

        .footer-legal-bar {
            flex-direction: column;
        }

    }

</style>

@endsection
```
