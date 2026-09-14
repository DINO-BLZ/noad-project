@extends('layouts.app')

@section('content')

<div class="noad-drop-resistance-page">

    {{-- =========================================================
         01. HERO — DROP
    ========================================================== --}}

    <section class="drop-resistance-hero">

        <div class="drop-hero-overlay"></div>

        <div class="drop-hero-content">

            <div class="drop-breadcrumb font-mono">
                <a href="{{ route('home') }}">ACCUEIL</a>
                <span>/</span>
                <a href="{{ route('drops.index') }}">DROPS</a>
                <span>/</span>
                <span>{{ strtoupper($drop->name) }}</span>
            </div>

            <div class="drop-hero-topline font-mono">

                <span class="drop-code">
                    DROP // {{ $drop->code ?? '01' }}
                </span>

                <span class="drop-access-status">
                    <span class="status-dot"></span>
                    {{ strtoupper($drop->status) }}
                </span>

            </div>

            <h1 class="drop-hero-title">
                {{ strtoupper($drop->name) }}
            </h1>

            <p class="drop-hero-manifesto">
                {{ $drop->description }}
            </p>

            <div class="drop-hero-meta font-mono">

                <div class="hero-meta-item">

                    <span class="meta-label">
                        OUVERTURE
                    </span>

                    <span class="meta-value">
                        {{ $drop->start_date->format('d.m.Y — H:i') }}
                    </span>

                </div>

                <div class="hero-meta-separator">
                    //
                </div>

                <div class="hero-meta-item">

                    <span class="meta-label">
                        FERMETURE
                    </span>

                    <span class="meta-value">
                        {{ $drop->end_date->format('d.m.Y — H:i') }}
                    </span>

                </div>

                <div class="hero-meta-separator">
                    //
                </div>

                <div class="hero-meta-item">

                    <span class="meta-label">
                        ACCÈS
                    </span>

                    <span class="meta-value text-accent">
                        WHITELIST
                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
         02. MANIFESTE / STATEMENT
    ========================================================== --}}

    <section class="drop-statement-section">

        <div class="drop-section-container">

            <div class="statement-grid">

                <div class="statement-index font-mono">
                    01 //
                </div>

                <div class="statement-main">

                    <span class="section-kicker font-mono">
                        DROP PROTOCOL
                    </span>

                    <h2 class="statement-title">
                        UNE PIÈCE.<br>
                        UN PRINCIPE.
                    </h2>

                    <p class="statement-copy">
                        Chaque pièce de cette session est produite en quantité
                        limitée. L'accès est contrôlé afin de préserver
                        l'exclusivité du drop.
                    </p>

                    <div class="statement-meta font-mono">

                        <span>
                            {{ $drop->start_date->format('d.m.Y') }}
                        </span>

                        <span>—</span>

                        <span>
                            {{ $drop->end_date->format('d.m.Y') }}
                        </span>

                        <span class="statement-status">
                            {{ strtoupper($drop->status) }}
                        </span>

                    </div>

                    {{-- =================================================
                         WHITELIST
                    ================================================== --}}

                    @if($drop->status === 'active')

                        <div
                            id="whitelist"
                            class="drop-whitelist-panel"
                        >

                            <div class="whitelist-panel-header">

                                <span class="font-mono">
                                    ACCESS CONTROL
                                </span>

                                <span class="whitelist-indicator">
                                    ■
                                </span>

                            </div>

                            <div class="whitelist-panel-content">

                                @if(auth()->check() && !$isWhitelisted)

                                    <div class="whitelist-copy">

                                        <span class="whitelist-status font-mono">
                                            ACCÈS RESTREINT
                                        </span>

                                        <h3>
                                            DEMANDER L'ACCÈS
                                        </h3>

                                        <p>
                                            Votre compte n'est pas encore
                                            autorisé pour ce drop.
                                            Envoyez une demande de whitelist
                                            pour accéder aux pièces.
                                        </p>

                                    </div>

                                    <form
                                        action="{{ route('drops.request-whitelist', $drop) }}"
                                        method="POST"
                                        class="whitelist-form"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn-whitelist font-mono"
                                        >
                                            DEMANDER LA WHITELIST
                                            <span>→</span>
                                        </button>

                                    </form>

                                @elseif(auth()->check() && $isWhitelisted)

                                    <div class="whitelist-copy">

                                        <span class="whitelist-status whitelist-approved font-mono">
                                            ACCÈS AUTORISÉ
                                        </span>

                                        <h3>
                                            VOUS ÊTES WHITELISTÉ
                                        </h3>

                                        <p>
                                            Votre compte est autorisé à
                                            accéder à ce drop.
                                        </p>

                                    </div>

                                    <div class="whitelist-approved-mark font-mono">
                                        ACCESS // GRANTED
                                    </div>

                                @elseif(auth()->guest())

                                    <div class="whitelist-copy">

                                        <span class="whitelist-status font-mono">
                                            AUTHENTIFICATION REQUISE
                                        </span>

                                        <h3>
                                            ACCÈS RÉSERVÉ
                                        </h3>

                                        <p>
                                            Connectez-vous à votre compte
                                            pour demander l'accès à la
                                            whitelist.
                                        </p>

                                    </div>

                                    <a
                                        href="{{ route('login') }}"
                                        class="btn-whitelist font-mono"
                                    >
                                        SE CONNECTER
                                        <span>→</span>
                                    </a>

                                @endif

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
         03. COLLECTION — PRODUITS DU DROP
    ========================================================== --}}

    <section
        id="collection"
        class="drop-products-section"
    >

        <div class="drop-section-container">

            <div class="products-section-header">

                <div>

                    <span class="section-kicker font-mono">
                        03 // COLLECTION
                    </span>

                    <h2 class="products-section-title">
                        PIÈCES DU DROP
                    </h2>

                </div>

                <div class="products-section-count font-mono">

                    {{ $drop->products->count() }}
                    PRODUIT{{ $drop->products->count() > 1 ? 'S' : '' }}

                </div>

            </div>


            <div class="drop-products-grid">

                @forelse($drop->products as $product)

                    @php
                        $totalStock = $product->variants->sum('stock');
                    @endphp

                    <article class="prod-card">

                        <div class="prod-image-wrap">

                            <img
                                src="{{ $product->image
                                    ? asset('storage/' . $product->image)
                                    : asset('images/products/placeholder.jpg') }}"
                                alt="{{ $product->name }}"
                                class="prod-image"
                            >

                            @if($totalStock === 0)

                                <span class="prod-stock-tag font-mono">
                                    ÉPUISÉ
                                </span>

                            @endif

                            <a
                                href="{{ route('products.show', $product->slug) }}"
                                class="btn-quick-plus"
                                title="Voir le produit"
                                aria-label="Voir {{ $product->name }}"
                            >
                                +
                            </a>

                        </div>

                        <div class="prod-card-content">

                            <div class="prod-card-top">

                                <span class="prod-category font-mono">
                                    {{ strtoupper($product->category->name ?? 'VESTIAIRE') }}
                                </span>

                                <span class="prod-index font-mono">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>

                            </div>

                            <h3 class="prod-name">
                                {{ $product->name }}
                            </h3>

                            <div class="prod-card-bottom">

                                <span class="prod-price font-mono">
                                    {{ number_format($product->price, 0, ',', ' ') }}
                                    DA
                                </span>

                                @if($totalStock > 0)

                                    <span class="prod-availability font-mono">
                                        DISPONIBLE
                                    </span>

                                @else

                                    <span class="prod-availability prod-sold-out font-mono">
                                        SOLD OUT
                                    </span>

                                @endif

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="drop-empty-state">

                        <span class="font-mono">
                            03 // COLLECTION
                        </span>

                        <h3>
                            AUCUNE PIÈCE DISPONIBLE
                        </h3>

                        <p>
                            Les produits associés à ce drop ne sont pas
                            encore disponibles.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>

    </section>


    {{-- =========================================================
         04. COUNTDOWN
    ========================================================== --}}

    @if($drop->status === 'active')

        <section class="drop-countdown-section">

            <div class="drop-countdown-inner">

                <div class="countdown-label font-mono">
                    DROP CLOSING // COMPTE À REBOURS
                </div>

                <div
                    class="countdown-center-block"
                    data-end="{{ $drop->end_date->toIso8601String() }}"
                >

                    <div class="countdown-unit">

                        <strong
                            class="countdown-value"
                            data-unit="days"
                        >
                            00
                        </strong>

                        <span class="countdown-unit-label font-mono">
                            JOURS
                        </span>

                    </div>

                    <span class="countdown-separator">
                        :
                    </span>

                    <div class="countdown-unit">

                        <strong
                            class="countdown-value"
                            data-unit="hours"
                        >
                            00
                        </strong>

                        <span class="countdown-unit-label font-mono">
                            HEURES
                        </span>

                    </div>

                    <span class="countdown-separator">
                        :
                    </span>

                    <div class="countdown-unit">

                        <strong
                            class="countdown-value"
                            data-unit="minutes"
                        >
                            00
                        </strong>

                        <span class="countdown-unit-label font-mono">
                            MINUTES
                        </span>

                    </div>

                    <span class="countdown-separator">
                        :
                    </span>

                    <div class="countdown-unit">

                        <strong
                            class="countdown-value"
                            data-unit="seconds"
                        >
                            00
                        </strong>

                        <span class="countdown-unit-label font-mono">
                            SECONDES
                        </span>

                    </div>

                </div>


                <div class="countdown-footer">

                    <span class="font-mono">
                        FERMETURE
                    </span>

                    <span class="font-mono">
                        {{ $drop->end_date->format('d.m.Y — H:i') }}
                    </span>

                </div>


                @if(auth()->check() && !$isWhitelisted)

                    <a
                        href="#whitelist"
                        class="countdown-cta font-mono"
                    >
                        DEMANDER L'ACCÈS
                        <span>↓</span>
                    </a>

                @elseif(auth()->check() && $isWhitelisted)

                    <a
                        href="#collection"
                        class="countdown-cta font-mono"
                    >
                        VOIR LA COLLECTION
                        <span>↓</span>
                    </a>

                @elseif(auth()->guest())

                    <a
                        href="{{ route('login') }}"
                        class="countdown-cta font-mono"
                    >
                        SE CONNECTER
                        <span>→</span>
                    </a>

                @endif

            </div>

        </section>

    @endif


    {{-- =========================================================
         05. ENGINEERING / MANIFESTE
    ========================================================== --}}

    <section class="drop-engineering-section">

        <div class="drop-section-container">

            <div class="engineering-header">

                <div>

                    <span class="section-kicker font-mono">
                        05 // ENGINEERING
                    </span>

                    <h2 class="engineering-title">
                        BUILT WITH PURPOSE.
                    </h2>

                </div>

                <p class="engineering-intro">
                    NOAD ne produit pas pour remplir un catalogue.
                    Chaque pièce doit répondre à un principe.
                </p>

            </div>


            <div class="engineering-grid">

                <article class="engineering-card">

                    <span class="engineering-number font-mono">
                        01
                    </span>

                    <h3>
                        MATIÈRE
                    </h3>

                    <p>
                        Sélection des matières selon leur résistance,
                        leur structure et leur capacité à évoluer avec
                        le vêtement.
                    </p>

                </article>


                <article class="engineering-card">

                    <span class="engineering-number font-mono">
                        02
                    </span>

                    <h3>
                        CONSTRUCTION
                    </h3>

                    <p>
                        Des volumes fonctionnels et des constructions
                        pensées pour une utilisation quotidienne.
                    </p>

                </article>


                <article class="engineering-card">

                    <span class="engineering-number font-mono">
                        03
                    </span>

                    <h3>
                        CONTRÔLE
                    </h3>

                    <p>
                        Production limitée, contrôle des stocks et
                        accès maîtrisé aux éditions spéciales.
                    </p>

                </article>


                <article class="engineering-card">

                    <span class="engineering-number font-mono">
                        04
                    </span>

                    <h3>
                        PRINCIPLE
                    </h3>

                    <p>
                        Une identité construite autour d'une idée simple :
                        défendre ce en quoi nous croyons.
                    </p>

                </article>

            </div>

        </div>

    </section>

</div>


{{-- =============================================================
     STYLES
============================================================= --}}

<style>

    .noad-drop-resistance-page {
        width: 100%;
        min-height: 100vh;
        background: #0a0a0a;
        color: #f5f5f0;
        font-family: 'Archivo', Arial, sans-serif;
    }

    .noad-drop-resistance-page a {
        color: inherit;
        text-decoration: none;
    }

    .font-mono {
        font-family:
            'SFMono-Regular',
            Consolas,
            'Liberation Mono',
            monospace;
    }

    .text-accent {
        color: #d32f2f !important;
    }

    .drop-section-container {
        width: min(1440px, calc(100% - 80px));
        margin: 0 auto;
    }


    /* =========================================================
       HERO
    ========================================================== */

    .drop-resistance-hero {
        position: relative;
        min-height: 720px;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
        background:
            linear-gradient(
                180deg,
                #111 0%,
                #090909 65%,
                #0a0a0a 100%
            );
    }

    .drop-hero-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                90deg,
                rgba(0, 0, 0, .9),
                rgba(0, 0, 0, .35)
            );
    }

    .drop-hero-content {
        position: relative;
        z-index: 2;
        width: min(1440px, calc(100% - 80px));
        margin: 0 auto;
        padding: 80px 0 70px;
    }

    .drop-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 45px;
        color: #777;
        font-size: 10px;
        letter-spacing: .14em;
    }

    .drop-breadcrumb a:hover {
        color: #fff;
    }

    .drop-hero-topline {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
        color: #888;
        font-size: 10px;
        letter-spacing: .16em;
    }

    .drop-access-status {
        color: #d32f2f;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin-right: 6px;
        background: #d32f2f;
    }

    .drop-hero-title {
        max-width: 1100px;
        margin: 0;
        font-size: clamp(56px, 10vw, 150px);
        font-weight: 900;
        line-height: .86;
        letter-spacing: -.055em;
    }

    .drop-hero-manifesto {
        max-width: 680px;
        margin: 42px 0 0;
        color: #aaa;
        font-size: 16px;
        line-height: 1.65;
    }

    .drop-hero-meta {
        display: flex;
        align-items: center;
        gap: 22px;
        margin-top: 55px;
        padding-top: 20px;
        border-top: 1px solid #292929;
        color: #777;
    }

    .hero-meta-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .meta-label {
        font-size: 8px;
        letter-spacing: .15em;
        color: #555;
    }

    .meta-value {
        color: #ddd;
        font-size: 11px;
        letter-spacing: .05em;
    }

    .hero-meta-separator {
        color: #333;
    }


    /* =========================================================
       STATEMENT
    ========================================================== */

    .drop-statement-section {
        padding: 130px 0;
        border-top: 1px solid #1c1c1c;
    }

    .statement-grid {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 50px;
    }

    .statement-index {
        color: #d32f2f;
        font-size: 12px;
        letter-spacing: .1em;
    }

    .statement-main {
        max-width: 900px;
    }

    .section-kicker {
        display: block;
        margin-bottom: 18px;
        color: #777;
        font-size: 9px;
        letter-spacing: .2em;
    }

    .statement-title {
        margin: 0;
        font-size: clamp(48px, 7vw, 100px);
        line-height: .88;
        letter-spacing: -.045em;
    }

    .statement-copy {
        max-width: 650px;
        margin: 40px 0 0;
        color: #888;
        font-size: 15px;
        line-height: 1.7;
    }

    .statement-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 35px;
        color: #777;
        font-size: 10px;
    }

    .statement-status {
        color: #d32f2f;
    }


    /* =========================================================
       WHITELIST
    ========================================================== */

    .drop-whitelist-panel {
        margin-top: 55px;
        border: 1px solid #292929;
        background: #101010;
    }

    .whitelist-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-bottom: 1px solid #292929;
        color: #777;
        font-size: 9px;
        letter-spacing: .15em;
    }

    .whitelist-indicator {
        color: #d32f2f;
    }

    .whitelist-panel-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        padding: 30px;
    }

    .whitelist-copy {
        max-width: 650px;
    }

    .whitelist-status {
        color: #d32f2f;
        font-size: 9px;
        letter-spacing: .15em;
    }

    .whitelist-approved {
        color: #72a96e;
    }

    .whitelist-copy h3 {
        margin: 10px 0;
        font-size: 24px;
        letter-spacing: -.02em;
    }

    .whitelist-copy p {
        margin: 0;
        color: #777;
        font-size: 12px;
        line-height: 1.6;
    }

    .btn-whitelist {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 20px;
        padding: 16px 20px;
        border: 1px solid #d32f2f;
        background: #d32f2f;
        color: #fff;
        font-size: 10px;
        letter-spacing: .1em;
        transition: .2s ease;
    }

    .btn-whitelist:hover {
        background: transparent;
        color: #d32f2f;
    }

    .whitelist-approved-mark {
        flex-shrink: 0;
        color: #72a96e;
        font-size: 10px;
        letter-spacing: .1em;
    }


    /* =========================================================
       PRODUCTS
    ========================================================== */

    .drop-products-section {
        padding: 100px 0 130px;
        border-top: 1px solid #1c1c1c;
    }

    .products-section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 30px;
        margin-bottom: 45px;
    }

    .products-section-title {
        margin: 0;
        font-size: clamp(40px, 6vw, 80px);
        line-height: .9;
        letter-spacing: -.045em;
    }

    .products-section-count {
        color: #666;
        font-size: 10px;
        letter-spacing: .12em;
    }

    .drop-products-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 2px;
        background: #292929;
        border: 1px solid #292929;
    }

    .prod-card {
        min-width: 0;
        background: #0a0a0a;
    }

    .prod-image-wrap {
        position: relative;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        background: #111;
    }

    .prod-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        filter: grayscale(15%);
        transition: transform .35s ease;
    }

    .prod-card:hover .prod-image {
        transform: scale(1.025);
    }

    .prod-stock-tag {
        position: absolute;
        top: 14px;
        left: 14px;
        padding: 7px 9px;
        background: #d32f2f;
        color: #fff;
        font-size: 8px;
        letter-spacing: .12em;
    }

    .btn-quick-plus {
        position: absolute;
        right: 14px;
        bottom: 14px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.35);
        background: rgba(0,0,0,.75);
        color: #fff;
        font-size: 25px;
        line-height: 1;
        transition: .2s ease;
    }

    .btn-quick-plus:hover {
        background: #fff;
        color: #000;
    }

    .prod-card-content {
        padding: 18px;
    }

    .prod-card-top,
    .prod-card-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .prod-category {
        color: #666;
        font-size: 8px;
        letter-spacing: .12em;
    }

    .prod-index {
        color: #444;
        font-size: 9px;
    }

    .prod-name {
        margin: 12px 0 24px;
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -.02em;
    }

    .prod-price {
        color: #eee;
        font-size: 11px;
    }

    .prod-availability {
        color: #777;
        font-size: 8px;
        letter-spacing: .1em;
    }

    .prod-sold-out {
        color: #d32f2f;
    }

    .drop-empty-state {
        grid-column: 1 / -1;
        padding: 80px 30px;
        background: #0a0a0a;
        text-align: center;
    }

    .drop-empty-state > span {
        color: #d32f2f;
        font-size: 9px;
        letter-spacing: .15em;
    }

    .drop-empty-state h3 {
        margin: 15px 0 10px;
        font-size: 28px;
    }

    .drop-empty-state p {
        margin: 0;
        color: #666;
        font-size: 12px;
    }


    /* =========================================================
       COUNTDOWN
    ========================================================== */

    .drop-countdown-section {
        padding: 100px 0;
        border-top: 1px solid #1c1c1c;
        background: #0d0d0d;
    }

    .drop-countdown-inner {
        width: min(1000px, calc(100% - 80px));
        margin: 0 auto;
        text-align: center;
    }

    .countdown-label {
        margin-bottom: 35px;
        color: #666;
        font-size: 9px;
        letter-spacing: .2em;
    }

    .countdown-center-block {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 20px;
    }

    .countdown-unit {
        min-width: 100px;
    }

    .countdown-value {
        display: block;
        font-family: 'Archivo', Arial, sans-serif;
        font-size: clamp(48px, 7vw, 90px);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -.05em;
    }

    .countdown-unit-label {
        display: block;
        margin-top: 12px;
        color: #555;
        font-size: 8px;
        letter-spacing: .15em;
    }

    .countdown-separator {
        color: #444;
        font-size: 50px;
        line-height: .9;
    }

    .countdown-footer {
        display: flex;
        justify-content: space-between;
        margin-top: 35px;
        padding-top: 15px;
        border-top: 1px solid #242424;
        color: #555;
        font-size: 9px;
        letter-spacing: .1em;
    }

    .countdown-cta {
        display: inline-flex;
        gap: 15px;
        margin-top: 35px;
        padding: 14px 20px;
        border: 1px solid #333;
        color: #aaa;
        font-size: 9px;
        letter-spacing: .12em;
        transition: .2s ease;
    }

    .countdown-cta:hover {
        border-color: #d32f2f;
        color: #d32f2f;
    }


    /* =========================================================
       ENGINEERING
    ========================================================== */

    .drop-engineering-section {
        padding: 120px 0;
        border-top: 1px solid #1c1c1c;
    }

    .engineering-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: end;
        margin-bottom: 60px;
    }

    .engineering-title {
        margin: 0;
        font-size: clamp(42px, 6vw, 80px);
        line-height: .9;
        letter-spacing: -.05em;
    }

    .engineering-intro {
        max-width: 500px;
        margin: 0;
        color: #777;
        font-size: 14px;
        line-height: 1.7;
    }

    .engineering-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #292929;
        border: 1px solid #292929;
    }

    .engineering-card {
        min-height: 250px;
        padding: 25px;
        background: #0a0a0a;
    }

    .engineering-number {
        color: #d32f2f;
        font-size: 10px;
    }

    .engineering-card h3 {
        margin: 50px 0 15px;
        font-size: 14px;
        letter-spacing: .08em;
    }

    .engineering-card p {
        margin: 0;
        color: #666;
        font-size: 11px;
        line-height: 1.65;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1000px) {

        .drop-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .engineering-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .engineering-header {
            grid-template-columns: 1fr;
            gap: 30px;
        }

    }


    @media (max-width: 700px) {

        .drop-section-container,
        .drop-hero-content {
            width: calc(100% - 36px);
        }

        .drop-resistance-hero {
            min-height: 620px;
        }

        .drop-hero-content {
            padding-bottom: 45px;
        }

        .drop-hero-title {
            font-size: 58px;
        }

        .drop-hero-meta {
            flex-wrap: wrap;
        }

        .hero-meta-separator {
            display: none;
        }

        .statement-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .statement-title {
            font-size: 50px;
        }

        .whitelist-panel-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-whitelist {
            width: 100%;
            justify-content: space-between;
        }

        .products-section-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .drop-products-grid {
            grid-template-columns: 1fr;
        }

        .countdown-center-block {
            gap: 8px;
        }

        .countdown-unit {
            min-width: 55px;
        }

        .countdown-value {
            font-size: 42px;
        }

        .countdown-separator {
            font-size: 30px;
        }

        .countdown-footer {
            flex-direction: column;
            gap: 8px;
        }

        .engineering-grid {
            grid-template-columns: 1fr;
        }

    }

</style>


{{-- =============================================================
     COUNTDOWN JS
============================================================= --}}

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const countdown = document.querySelector(
            '.countdown-center-block'
        );

        if (!countdown) {
            return;
        }

        const endValue = countdown.dataset.end;

        if (!endValue) {
            return;
        }

        const endDate = new Date(endValue);

        if (Number.isNaN(endDate.getTime())) {
            return;
        }

        const daysElement = countdown.querySelector(
            '[data-unit="days"]'
        );

        const hoursElement = countdown.querySelector(
            '[data-unit="hours"]'
        );

        const minutesElement = countdown.querySelector(
            '[data-unit="minutes"]'
        );

        const secondsElement = countdown.querySelector(
            '[data-unit="seconds"]'
        );


        function pad(value) {
            return String(value).padStart(2, '0');
        }


        function updateCountdown() {

            const now = new Date();

            const difference =
                endDate.getTime() - now.getTime();


            if (difference <= 0) {

                daysElement.textContent = '00';
                hoursElement.textContent = '00';
                minutesElement.textContent = '00';
                secondsElement.textContent = '00';

                return;
            }


            const totalSeconds =
                Math.floor(difference / 1000);


            const days =
                Math.floor(totalSeconds / 86400);


            const hours =
                Math.floor(
                    (totalSeconds % 86400) / 3600
                );


            const minutes =
                Math.floor(
                    (totalSeconds % 3600) / 60
                );


            const seconds =
                totalSeconds % 60;


            daysElement.textContent = pad(days);
            hoursElement.textContent = pad(hours);
            minutesElement.textContent = pad(minutes);
            secondsElement.textContent = pad(seconds);

        }


        updateCountdown();

        setInterval(updateCountdown, 1000);

    });

</script>

@endsection