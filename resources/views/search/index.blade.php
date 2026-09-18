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
                    value="{{ $query }}"
                    placeholder="RECHERCHER UNE PIÈCE, MATIÈRE, DROP..."
                    class="search-giant-input"
                    autocomplete="off"
                    autofocus
                >

                @if($query)
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

        <div class="search-filter-bar">

            <div class="filter-tabs-group font-mono">

                <span class="filter-tab active">
                    TOUT ({{ $products->total() }})
                </span>

            </div>

            <div class="results-tally font-mono">

                <span class="tally-count font-bold">
                    {{ $products->total() }}
                    RÉSULTAT{{ $products->total() > 1 ? 'S' : '' }}
                    TROUVÉ{{ $products->total() > 1 ? 'S' : '' }}
                </span>

                @if($query)

                    &nbsp; POUR "{{ strtoupper($query) }}"

                @else

                    &nbsp; POUR TOUT LE CATALOGUE

                @endif

            </div>

        </div>

    </div>

    {{-- =====================================================
         03. SECTION : PRODUITS CORRESPONDANTS
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

        @if($products->count())

            <div class="search-products-grid">

                @foreach($products as $product)

                    <article class="search-product-card">

                        <a
                            href="{{ route('products.show', $product->slug) }}"
                            class="search-product-link"
                        >

                            <div class="card-thumb-holder">

                                <span class="tactical-tag tag-gray font-mono">
                                    NOAD
                                </span>

                                @if($product->image)

                                    <img
                                        src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                    >

                                @else

                                    <div class="product-image-placeholder font-mono">
                                        NO IMAGE
                                    </div>

                                @endif

                            </div>

                            <div class="card-content-holder">

                                @if($product->category)

                                    <span class="prod-spec font-mono">
                                        {{ strtoupper($product->category->name) }}
                                    </span>

                                @else

                                    <span class="prod-spec font-mono">
                                        NOAD
                                    </span>

                                @endif

                                <h3 class="prod-name">
                                    {{ strtoupper($product->name) }}
                                </h3>

                                <div class="prod-row-bottom font-mono">

                                    <span class="prod-color">
                                        NOAD
                                    </span>

                                    <span class="prod-price font-bold">
                                        {{ number_format($product->price, 0, ',', ' ') }} DA
                                    </span>

                                </div>

                            </div>

                        </a>

                    </article>

                @endforeach

            </div>

        @else

            <div class="search-empty-state">

                <div class="empty-code font-mono">
                    SEARCH // 404
                </div>

                <h3 class="empty-title">
                    AUCUN PRODUIT TROUVÉ
                </h3>

                @if($query)

                    <p class="empty-text">
                        Aucun produit ne correspond à
                        <strong>"{{ $query }}"</strong>.
                    </p>

                @else

                    <p class="empty-text">
                        Aucun produit n'est actuellement disponible.
                    </p>

                @endif

                <a
                    href="{{ route('shop.index') }}"
                    class="empty-action font-mono"
                >
                    RETOUR À LA BOUTIQUE
                    <span>→</span>
                </a>

            </div>

        @endif

        @if($products->hasPages())

            <div class="search-pagination font-mono">

                {{ $products->links() }}

            </div>

        @endif

    </section>

    {{-- =====================================================
         04. RECHERCHES POPULAIRES
    ====================================================== --}}

    <div class="popular-searches-box font-mono">

        <div class="popular-head">

            <div class="pop-left">

                <span class="glyph-trend text-accent">
                    ↗
                </span>

                RECHERCHES POPULAIRES

            </div>

            <div class="pop-right text-muted">
                SUGGESTIONS
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
                href="{{ route('search.index', ['q' => 'Drop 01']) }}"
                class="pop-tag"
            >
                DROP 01
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
         05. BANDEAU DE MARQUE
    ====================================================== --}}

    <div class="brand-statement-banner">

        <div class="statement-brand-lockup">

            <div class="brand-monogram font-mono">
                <span>NO</span>
                <span>AD</span>
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
                DEFEND YOUR PRINCIPLE
            </span>

            <span class="statement-sub font-mono">
                Toutes les pièces sont confectionnées selon un cahier des charges rigide.
            </span>

        </div>

    </div>

</div>

{{-- =========================================================
     06. FOOTER GLOBAL NOAD
========================================================== --}}

<footer class="noad-search-footer">

    <div class="container-search">

        <div class="footer-top-row">

            <div class="brand-id-col">

                <div class="noad-logo-sq font-mono">
                    <span>NO</span>
                    <span>AD</span>
                </div>

                <div class="brand-text font-mono">

                    <span class="brand-main">
                        NOAD
                    </span>

                    <span class="brand-desc">
                        NO ADVANTAGE
                    </span>

                    <span class="brand-motto text-accent">
                        DEFEND YOUR PRINCIPLE
                    </span>

                </div>

            </div>

            <div class="footer-newsletter-col">

                <span class="nl-title font-mono">
                    REJOIGNEZ LA COMMUNAUTÉ
                </span>

                <p class="nl-sub">
                    Les prochaines sorties, drops et actualités NOAD.
                </p>

                <div class="nl-form-inline">

                    <input
                        type="email"
                        placeholder="Votre adresse e-mail"
                        class="input-nl font-mono"
                        disabled
                        aria-label="Adresse e-mail newsletter"
                    >

                    <button
                        type="button"
                        class="btn-nl-arrow font-mono"
                        disabled
                        aria-label="Newsletter indisponible"
                    >
                        →
                    </button>

                </div>

                <span class="newsletter-status font-mono">
                    NEWSLETTER // BIENTÔT DISPONIBLE
                </span>

            </div>

        </div>

        <nav class="footer-links-row font-mono">

            <a href="{{ route('shop.index') }}">
                BOUTIQUE
            </a>

            <a href="{{ route('search.index') }}">
                RECHERCHE
            </a>

        </nav>

        <div class="footer-legal-bar font-mono">

            <div class="legal-items">

                <span>NOAD</span>

                <span class="sep">/</span>

                <span>NO ADVANTAGE</span>

                <span class="sep">/</span>

                <span>ALGIERS</span>

            </div>

            <div class="social-items">

                <a href="#" aria-label="Instagram">
                    INSTAGRAM
                </a>

                <a href="#" aria-label="TikTok">
                    TIKTOK
                </a>

                <a href="#" aria-label="YouTube">
                    YOUTUBE
                </a>

                <a href="#" aria-label="X">
                    X
                </a>

            </div>

        </div>

    </div>

</footer>
</div>

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

.noad-search-experience *,
.noad-search-experience *::before,
.noad-search-experience *::after {
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

.search-main-form {
    width: 100%;
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
    min-width: 0;
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
    display: inline-flex;
    align-items: center;
    background-color: #d32f2f;
    border: 1px solid #d32f2f;
    color: #ffffff;
    font-size: 10px;
    letter-spacing: 0.12em;
    padding: 8px 16px;
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
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.search-product-card {
    min-width: 0;
    background-color: #121212;
    border: 1px solid #1f1f1f;
    display: flex;
    flex-direction: column;
    transition: border-color 0.2s;
}

.search-product-card:hover {
    border-color: #444444;
}

.search-product-link {
    display: flex;
    flex-direction: column;
    height: 100%;
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

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #444444;
    font-size: 10px;
    letter-spacing: 0.15em;
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

.tag-gray {
    background-color: rgba(18, 18, 18, 0.9);
    border: 1px solid #333333;
    color: #cccccc;
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
    gap: 12px;
}

.prod-color {
    color: #777777;
    font-size: 10px;
}

.prod-price {
    color: #ffffff;
    white-space: nowrap;
}


/* =====================================================================
   ÉTAT VIDE
====================================================================== */

.search-empty-state {
    min-height: 300px;
    border: 1px solid #1f1f1f;
    background-color: #121212;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 48px 24px;
}

.empty-code {
    color: #d32f2f;
    font-size: 10px;
    letter-spacing: 0.2em;
    margin-bottom: 14px;
}

.empty-title {
    color: #ffffff;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0 0 10px 0;
    text-transform: uppercase;
}

.empty-text {
    max-width: 520px;
    color: #777777;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 0 24px 0;
}

.empty-text strong {
    color: #ffffff;
}

.empty-action {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background-color: #ffffff;
    color: #000000 !important;
    padding: 11px 18px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    transition: all 0.2s;
}

.empty-action:hover {
    background-color: #d32f2f;
    color: #ffffff !important;
}


/* =====================================================================
   PAGINATION
====================================================================== */

.search-pagination {
    margin-top: 32px;
    display: flex;
    justify-content: center;
}

.search-pagination nav {
    display: flex;
    justify-content: center;
}

.search-pagination nav > div:first-child {
    display: none;
}

.search-pagination nav > div:last-child {
    display: flex;
    align-items: center;
    gap: 6px;
}

.search-pagination a,
.search-pagination span {
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #141414;
    border: 1px solid #222222;
    color: #888888;
    font-size: 10px;
    letter-spacing: 0.05em;
}

.search-pagination a:hover {
    color: #ffffff;
    border-color: #444444;
}

.search-pagination span[aria-current="page"] {
    background-color: #d32f2f;
    border-color: #d32f2f;
    color: #ffffff;
}


/* =====================================================================
   04. RECHERCHES POPULAIRES
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
   05. BANDEAU DE MARQUE
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
   06. FOOTER GLOBAL
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
    min-width: 0;
    background-color: #0e0e0e;
    border: none;
    outline: none;
    color: #555555;
    padding: 12px 14px;
    font-size: 11px;
}

.btn-nl-arrow {
    background-color: #333333;
    color: #777777;
    border: none;
    padding: 0 20px;
    font-size: 16px;
}

.newsletter-status {
    display: block;
    margin-top: 8px;
    color: #555555;
    font-size: 8px;
    letter-spacing: 0.12em;
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
    flex-wrap: wrap;
}


/* =====================================================================
   RESPONSIVE — TABLET
====================================================================== */

@media (max-width: 1080px) {

    .search-products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}


/* =====================================================================
   RESPONSIVE — MOBILE
====================================================================== */

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

    .section-title-strip {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .section-title-right {
        display: none;
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