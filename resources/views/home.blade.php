@extends('layouts.app')

@section('content')

<div class="noad-home-page">

    {{-- =========================================================
         01. HERO
    ========================================================== --}}

    <section
        class="home-hero-section"
        style="background-image: url('{{ asset('images/hero/noad-hero-bg.jpg') }}');"
    >

        <div class="hero-gradient-overlay"></div>

        <div class="hero-content-wrap">

            <div class="hero-season-tag font-mono">
                AUTUMN / WINTER 26
                <span class="rule-line"></span>
            </div>

            <h1 class="hero-main-title">
                DEFEND YOUR<br>
                PRINCIPLES
            </h1>

            <p class="hero-lead-text">
                Plus qu'un style. Une vision.
                Des pièces pour ceux qui ne suivent pas.
            </p>

            <div class="hero-cta-row">
                
                    <a href="{{ route('shop.index') }}"
                    class="btn-hero-cta font-mono"
                >
                    DÉCOUVRIR LA COLLECTION
                    <span class="arrow">→</span>
                </a>
            </div>

        </div>

        <div class="hero-bottom-strip font-mono">

            <div class="hero-slider-counter">
                <span class="curr-slide">01</span>
                <span class="sep-line"></span>
                <span class="total-slides">03</span>
            </div>

            <div class="hero-scroll-indicator">
                <span class="scroll-arrow">↑</span>
                SCROLL
            </div>

        </div>

    </section>


    {{-- =========================================================
         02. DERNIÈRE COLLECTION
         DONNÉES : $newProducts
    ========================================================== --}}

    @if(isset($newProducts) && $newProducts->isNotEmpty())

        <section class="section-collection-preview">

            <div class="container-home">

                <div class="collection-split-layout">

                    {{-- Introduction --}}

                    <div class="collection-intro-col">

                        <span class="section-kicker font-mono">
                            DERNIÈRE COLLECTION
                            <span class="rule-line"></span>
                        </span>

                        <h2 class="collection-heading">
                            NEW<br>
                            ARRIVALS
                        </h2>

                        <p class="collection-subtext">
                            Découvrez les dernières pièces ajoutées
                            à l'univers NOAD.
                        </p>

                        
                            <a href="{{ route('shop.index') }}"
                            class="link-editorial font-mono"
                        >
                            VOIR LA COLLECTION
                            <span class="arrow">→</span>
                        </a>

                    </div>


                    {{-- Produits dynamiques --}}

                    <div class="collection-products-grid">

                        @foreach($newProducts as $product)

                            <article class="home-prod-card">

                                
                                    <a href="{{ route('products.show', $product) }}"
                                    class="home-prod-card__link"
                                >

                                    <div class="card-media">

                                        @if($product->image)
                                            <img
                                                src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="card-media__placeholder">
                                                NO IMAGE
                                            </div>
                                        @endif

                                    </div>

                                    <div class="card-meta font-mono">

                                        <h3 class="prod-name">
                                            {{ strtoupper($product->name) }}
                                        </h3>

                                        @if($product->category)
                                            <span class="prod-color">
                                                {{ strtoupper($product->category->name) }}
                                            </span>
                                        @endif

                                        <div class="prod-price font-bold">
                                            {{ number_format($product->price, 0, ',', ' ') }} DA
                                        </div>

                                    </div>

                                </a>

                            </article>

                        @endforeach

                    </div>

                </div>

            </div>

        </section>

    @endif


    {{-- =========================================================
         03. NEXT DROP
         SOURCE : $activeDrop ?? $upcomingDrop
    ========================================================== --}}

    @php
        $homeDrop = $activeDrop ?? $upcomingDrop ?? null;
    @endphp

    @if($homeDrop)

        <section class="section-next-drop">

            <div class="container-home">

                <div class="drop-banner-box">

                    {{-- Informations du Drop --}}

                    <div class="drop-info-col">

                        <span class="drop-badge font-mono">
                            <span class="dot-red-sq">■</span>
                            NEXT DROP
                        </span>

                        <h2 class="drop-banner-title">
                            {{ strtoupper($homeDrop->name) }}
                        </h2>

                        @if($homeDrop->season)
                            <span class="drop-season font-mono">
                                {{ strtoupper($homeDrop->season) }}
                            </span>
                        @endif

                        <div class="drop-meta-schedule font-mono">

                            @if($homeDrop->start_date)
                                <div class="meta-item">
                                    <span class="glyph">📅</span>
                                    {{ $homeDrop->start_date->format('d.m.Y — H:i') }}
                                </div>
                            @endif

                            @if($homeDrop->is_whitelist_only ?? false)
                                <div class="meta-item">
                                    <span class="glyph">🔒</span>
                                    WHITELIST ONLY
                                </div>
                            @endif

                        </div>

                        <div class="drop-actions-row">

                            @if(Route::has('whitelist.index'))
                                
                                    <a href="{{ route('whitelist.index') }}"
                                    class="btn-request-access font-mono"
                                >
                                    REQUEST ACCESS
                                    <span class="arrow">→</span>
                                </a>
                            @endif

                            
                                <a href="{{ route('drops.show', $homeDrop->slug) }}"
                                class="link-learn-more font-mono"
                            >
                                LEARN MORE
                            </a>

                        </div>

                    </div>


                    {{-- Image + Countdown --}}

                    <div
                        class="drop-countdown-col"
                        @if($homeDrop->image)
                            style="background-image: url('{{ asset('storage/' . $homeDrop->image) }}');"
                        @else
                            style="background-image: url('{{ asset('images/drops/drop01-stadium.jpg') }}');"
                        @endif
                    >

                        <div class="col-vignette"></div>

                        {{--

                            IMPORTANT :
                            Le countdown est piloté par end_date.

                            data-end contient la date réelle du Drop.
                            Le JS lit cette valeur et calcule
                            automatiquement le temps restant.

                        --}}

                        <div
                            class="countdown-hud-panel font-mono"
                            data-end="{{ $homeDrop->end_date?->toIso8601String() }}"
                        >

                            <span class="hud-label">
                                DROP OPENS IN
                            </span>

                            <div class="countdown-digits">

                                <div class="digit-box">

                                    <span
                                        class="digit-num"
                                        data-countdown-hours
                                    >
                                        00
                                    </span>

                                    <span class="digit-unit">
                                        HOURS
                                    </span>

                                </div>

                                <span class="digit-sep">:</span>

                                <div class="digit-box">

                                    <span
                                        class="digit-num"
                                        data-countdown-minutes
                                    >
                                        00
                                    </span>

                                    <span class="digit-unit">
                                        MINUTES
                                    </span>

                                </div>

                                <span class="digit-sep">:</span>

                                <div class="digit-box">

                                    <span
                                        class="digit-num text-accent"
                                        data-countdown-seconds
                                    >
                                        00
                                    </span>

                                    <span class="digit-unit">
                                        SECONDS
                                    </span>

                                </div>

                            </div>

                            <div
                                class="hud-status"
                                data-countdown-status
                            >
                                <span class="dot-red-pulse">●</span>
                                LIVE SERVER
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    @endif


    {{-- =========================================================
         04. JOURNAL
         Cette section reste statique tant que le Journal
         n'est pas branché au backend.
    ========================================================== --}}

    <section class="section-journal-strip">

        <div class="container-home">

            <div class="journal-header-row">

                <div>

                    <span class="section-kicker font-mono">
                        THE JOURNAL
                        <span class="rule-line"></span>
                    </span>

                    <h2 class="journal-title">
                        THE PRINCIPLES BEHIND NOAD
                    </h2>

                    <p class="journal-sub">
                        Notre culture. Notre vision.
                        Pourquoi nous faisons ce que nous faisons.
                    </p>

                </div>

                @if(Route::has('journal.index'))

                    
                        <a href="{{ route('journal.index') }}"
                        class="journal-top-link font-mono"
                    >
                        LIRE L'ARTICLE
                        <span class="arrow">→</span>
                    </a>

                @endif

            </div>

            <div class="journal-articles-grid">

                <article class="journal-card journal-card-featured">

                    <div
                        class="card-thumb"
                        style="background-image: url('{{ asset('images/journal/journal-01.jpg') }}');"
                    ></div>

                    <div class="card-body">

                        <span class="post-date font-mono">
                            07.09.2026
                        </span>

                        <h3 class="post-title">
                            THE PRINCIPLES BEHIND NOAD
                        </h3>

                        <p class="post-snippet">
                            Our story, our culture, our vision.
                            Why we build garments resistant to conformity
                            and fleeting hype cycles.
                        </p>

                        @if(Route::has('journal.show'))

                            
                                <a href="{{ route('journal.show', 1) }}"
                                class="post-link font-mono"
                            >
                                READ MORE
                                <span class="arrow">→</span>
                            </a>

                        @endif

                    </div>

                </article>


                <article class="journal-card">

                    <div
                        class="card-thumb"
                        style="background-image: url('{{ asset('images/journal/journal-02.jpg') }}');"
                    ></div>

                    <div class="card-body">

                        <span class="post-date font-mono">
                            02.09.2026
                        </span>

                        <h3 class="post-title">
                            BRITISH CASUALS:
                            PAST, PRESENT, FUTURE
                        </h3>

                        @if(Route::has('journal.show'))

                            
                                <a href="{{ route('journal.show', 2) }}"
                                class="post-link font-mono"
                            >
                                READ MORE
                                <span class="arrow">→</span>
                            </a>

                        @endif

                    </div>

                </article>


                <article class="journal-card">

                    <div
                        class="card-thumb"
                        style="background-image: url('{{ asset('images/journal/journal-03.jpg') }}');"
                    ></div>

                    <div class="card-body">

                        <span class="post-date font-mono">
                            28.08.2026
                        </span>

                        <h3 class="post-title">
                            QUALITY OVER TRENDS
                        </h3>

                        @if(Route::has('journal.show'))

                            
                                <a href="{{ route('journal.show', 3) }}"
                                class="post-link font-mono"
                            >
                                READ MORE
                                <span class="arrow">→</span>
                            </a>

                        @endif

                    </div>

                </article>

            </div>

        </div>

    </section>


    {{-- =========================================================
         05. ESSENTIELS
    ========================================================== --}}

    @if(isset($newProducts) && $newProducts->isNotEmpty())

        <section class="section-essentials">

            <div class="container-home">

                <div class="essentials-header-row">

                    <div>

                        <span class="section-kicker font-mono">
                            ESSENTIELS
                            <span class="rule-line"></span>
                        </span>

                        <p class="essentials-sub">
                            Les pièces intemporelles de la collection.
                        </p>

                    </div>

                    
                        <a href="{{ route('shop.index') }}"
                        class="link-all font-mono"
                    >
                        VOIR TOUT
                        <span class="arrow">→</span>
                    </a>

                </div>


                <div class="essentials-grid">

                    @foreach($newProducts->take(4) as $product)

                        <article class="home-prod-card">

                            
                                <a href="{{ route('products.show', $product) }}"
                                class="home-prod-card__link"
                            >

                                <div class="card-media">

                                    @if($product->image)

                                        <img
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <div class="card-media__placeholder">
                                            NO IMAGE
                                        </div>

                                    @endif

                                </div>

                                <div class="card-meta font-mono">

                                    <h3 class="prod-name">
                                        {{ strtoupper($product->name) }}
                                    </h3>

                                    @if($product->category)

                                        <span class="prod-color">
                                            {{ strtoupper($product->category->name) }}
                                        </span>

                                    @endif

                                    <div class="prod-price font-bold">
                                        {{ number_format($product->price, 0, ',', ' ') }} DA
                                    </div>

                                </div>

                            </a>

                        </article>

                    @endforeach


                    {{-- Craftsmanship --}}

                    <div
                        class="craftsmanship-card"
                        style="background-image: url('{{ asset('images/brand/craftsmanship-detail.jpg') }}');"
                    >

                        <div class="craft-overlay"></div>

                        <div class="craft-body font-mono">

                            <span class="craft-kicker">
                                CRAFTSMANSHIP
                            </span>

                            <h3 class="craft-title">
                                QUALITY IN EVERY DETAIL.
                            </h3>

                            @if(Route::has('about'))

                                
                                    <a href="{{ route('about') }}"
                                    class="craft-link"
                                >
                                    EN SAVOIR PLUS
                                    <span class="arrow">→</span>
                                </a>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </section>

    @endif


    {{-- =========================================================
         06. FOOTER
         UNIQUEMENT AVEC LES ROUTES RÉELLES
    ========================================================== --}}

    <footer class="noad-home-footer">

        <div class="container-home">

            <div class="footer-top-row">

                <div class="brand-lockup">

                    <div class="noad-logo-monogram font-mono">
                        <span>NO</span>
                        <span>AD</span>
                    </div>

                    <div class="brand-headings">

                        <span class="brand-title">
                            NOAD
                        </span>

                        <span class="brand-sub">
                            NO ADVANTAGE
                        </span>

                        <span class="brand-slogan font-mono">
                            DEFEND YOUR PRINCIPLES
                        </span>

                    </div>

                </div>


                @if(Route::has('newsletter.subscribe'))

                    <div class="newsletter-block">

                        <span class="newsletter-title font-mono">
                            REJOIGNEZ LA COMMUNAUTÉ
                        </span>

                        <p class="newsletter-desc">
                            Recevez les prochaines sorties,
                            drops et actualités NOAD.
                        </p>

                        <form
                            action="{{ route('newsletter.subscribe') }}"
                            method="POST"
                            class="newsletter-form-inline"
                        >

                            @csrf

                            <input
                                type="email"
                                name="email"
                                placeholder="Votre adresse e-mail"
                                required
                                class="input-news font-mono"
                            >

                            <button
                                type="submit"
                                class="btn-news-submit font-mono"
                            >
                                →
                            </button>

                        </form>

                    </div>

                @endif

            </div>


            {{-- Navigation réelle uniquement --}}

            <nav class="footer-main-nav font-mono">

                <a href="{{ route('shop.index') }}">
                    BOUTIQUE
                </a>

                <a href="{{ route('drops.index') }}">
                    DROPS
                </a>

                @if(Route::has('whitelist.index'))

                    <a href="{{ route('whitelist.index') }}">
                        WHITELIST
                    </a>

                @endif

            </nav>


            {{-- Footer inférieur --}}

            <div class="footer-bottom-bar font-mono">

                <div class="legal-links">

                    {{--

                        Les anciennes routes :
                        shipping
                        returns
                        faq
                        cgv
                        legal
                        privacy

                        sont volontairement retirées ici tant qu'elles
                        n'existent pas réellement dans routes/web.php.

                    --}}

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


{{-- =============================================================
     COUNTDOWN — DROP
     SOURCE : data-end
============================================================= --}}

@if($homeDrop && $homeDrop->end_date)

<script>
document.addEventListener('DOMContentLoaded', function () {

    const countdownPanel = document.querySelector(
        '.countdown-hud-panel[data-end]'
    );

    if (!countdownPanel) {
        return;
    }

    const endDate = new Date(
        countdownPanel.dataset.end
    ).getTime();

    const hoursElement = countdownPanel.querySelector(
        '[data-countdown-hours]'
    );

    const minutesElement = countdownPanel.querySelector(
        '[data-countdown-minutes]'
    );

    const secondsElement = countdownPanel.querySelector(
        '[data-countdown-seconds]'
    );

    const statusElement = countdownPanel.querySelector(
        '[data-countdown-status]'
    );


    function updateCountdown() {

        const now = new Date().getTime();
        const distance = endDate - now;


        if (distance <= 0) {

            if (hoursElement) {
                hoursElement.textContent = '00';
            }

            if (minutesElement) {
                minutesElement.textContent = '00';
            }

            if (secondsElement) {
                secondsElement.textContent = '00';
            }

            if (statusElement) {

                statusElement.innerHTML = `
                    <span class="dot-red-pulse">●</span>
                    DROP CLOSED
                `;

            }

            return;
        }


        const totalSeconds = Math.floor(
            distance / 1000
        );

        const hours = Math.floor(
            totalSeconds / 3600
        );

        const minutes = Math.floor(
            (totalSeconds % 3600) / 60
        );

        const seconds = totalSeconds % 60;


        if (hoursElement) {
            hoursElement.textContent =
                String(hours).padStart(2, '0');
        }

        if (minutesElement) {
            minutesElement.textContent =
                String(minutes).padStart(2, '0');
        }

        if (secondsElement) {
            secondsElement.textContent =
                String(seconds).padStart(2, '0');
        }

    }


    updateCountdown();

    const countdownInterval = setInterval(function () {

        updateCountdown();

        if (
            new Date().getTime() >= endDate
        ) {
            clearInterval(countdownInterval);
        }

    }, 1000);

});
</script>

@endif


{{-- =============================================================
     NOAD HOME — STYLES
============================================================= --}}

<style>

    .noad-home-page {
        width: 100%;
        min-height: 100vh;
        background-color: #0c0c0c;
        color: #e5e5e5;
        font-family:
            'Barlow Condensed',
            -apple-system,
            BlinkMacSystemFont,
            sans-serif;
        box-sizing: border-box;
    }

    .noad-home-page a {
        color: inherit;
        text-decoration: none;
    }

    .font-mono {
        font-family:
            'SFMono-Regular',
            Consolas,
            Menlo,
            monospace;
    }

    .font-bold {
        font-weight: 700;
    }

    .text-accent {
        color: #d32f2f !important;
    }

    .container-home {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 40px;
    }

    .home-hero-section {
        position: relative;
        width: 100%;
        height: 92vh;
        min-height: 640px;
        background-size: cover;
        background-position: center top;
        background-color: #121212;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 60px 40px 40px;
        box-sizing: border-box;
        border-bottom: 1px solid #1a1a1a;
    }

    .hero-gradient-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(12, 12, 12, 0.15) 0%,
                rgba(12, 12, 12, 0.45) 50%,
                rgba(12, 12, 12, 0.96) 100%
            );
        z-index: 1;
    }

    .hero-content-wrap {
        position: relative;
        z-index: 2;
        max-width: 1440px;
        width: 100%;
        margin: 0 auto 40px;
    }

    .hero-season-tag {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11px;
        letter-spacing: 0.2em;
        color: #888888;
        margin-bottom: 16px;
    }

    .rule-line {
        display: inline-block;
        width: 40px;
        height: 1px;
        background-color: #333333;
    }

    .hero-main-title {
        font-size: clamp(52px, 7.5vw, 100px);
        font-weight: 900;
        letter-spacing: 0.04em;
        line-height: 0.9;
        margin: 0 0 16px;
        color: #ffffff;
        text-transform: uppercase;
    }

    .hero-lead-text {
        max-width: 520px;
        font-size: 14px;
        color: #aaaaaa;
        line-height: 1.5;
        margin: 0 0 28px;
    }

    .btn-hero-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background-color: transparent;
        color: #ffffff;
        border: 1px solid #ffffff;
        padding: 12px 24px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.15em;
        transition: all 0.2s ease;
    }

    .btn-hero-cta:hover {
        background-color: #ffffff;
        color: #000000;
    }

    .hero-bottom-strip {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1440px;
        width: 100%;
        margin: 0 auto;
        font-size: 10px;
        color: #777777;
        letter-spacing: 0.15em;
    }

    .hero-slider-counter {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hero-slider-counter .curr-slide {
        color: #ffffff;
        font-weight: bold;
    }

    .hero-slider-counter .sep-line {
        width: 32px;
        height: 1px;
        background-color: #444444;
    }

    .hero-scroll-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .section-collection-preview {
        padding: 80px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .collection-split-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 48px;
        align-items: start;
    }

    .section-kicker {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 10px;
        letter-spacing: 0.2em;
        color: #777777;
        margin-bottom: 12px;
    }

    .collection-heading {
        font-size: clamp(38px, 4.5vw, 54px);
        font-weight: 900;
        line-height: 0.95;
        letter-spacing: 0.04em;
        margin: 0 0 16px;
        color: #ffffff;
    }

    .collection-subtext {
        font-size: 13px;
        color: #888888;
        line-height: 1.55;
        margin: 0 0 24px;
    }

    .link-editorial {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        letter-spacing: 0.12em;
        font-weight: 700;
        color: #ffffff;
        transition: color 0.2s;
    }

    .link-editorial:hover {
        color: #d32f2f;
    }

    .collection-products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .home-prod-card {
        background-color: #121212;
        border: 1px solid #202020;
        display: flex;
        flex-direction: column;
    }

    .home-prod-card__link {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card-media {
        position: relative;
        height: 280px;
        background-color: #161616;
        overflow: hidden;
    }

    .card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }

    .home-prod-card:hover .card-media img {
        transform: scale(1.03);
    }

    .card-media__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555555;
        font-family:
            'SFMono-Regular',
            Consolas,
            Menlo,
            monospace;
        font-size: 10px;
        letter-spacing: 0.15em;
    }

    .card-meta {
        padding: 14px 16px;
    }

    .prod-name {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin: 0 0 2px;
        color: #ffffff;
    }

    .prod-color {
        display: block;
        font-size: 10px;
        color: #777777;
        margin-bottom: 8px;
    }

    .prod-price {
        font-size: 13px;
        color: #ffffff;
    }

    .section-next-drop {
        padding: 72px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .drop-banner-box {
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        background-color: #121212;
        border: 1px solid #242424;
    }

    .drop-info-col {
        padding: 44px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .drop-badge {
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #d32f2f;
        margin-bottom: 8px;
    }

    .dot-red-sq {
        color: #d32f2f;
        font-size: 8px;
        margin-right: 4px;
    }

    .drop-banner-title {
        font-size: clamp(34px, 4.5vw, 48px);
        font-weight: 900;
        line-height: 1;
        letter-spacing: 0.04em;
        margin: 0 0 6px;
        color: #ffffff;
    }

    .drop-season {
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #777777;
        margin-bottom: 24px;
    }

    .drop-meta-schedule {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 11px;
        color: #aaaaaa;
        margin-bottom: 32px;
    }

    .drop-actions-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .btn-request-access {
        background-color: #ffffff;
        color: #000000;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.15em;
        padding: 14px 24px;
        border: 1px solid #ffffff;
        transition: all 0.2s;
    }

    .btn-request-access:hover {
        background-color: #d32f2f;
        border-color: #d32f2f;
        color: #ffffff;
    }

    .link-learn-more {
        font-size: 11px;
        letter-spacing: 0.15em;
        color: #777777;
    }

    .link-learn-more:hover {
        color: #ffffff;
    }

    .drop-countdown-col {
        position: relative;
        min-height: 380px;
        background-size: cover;
        background-position: center;
        background-color: #181818;
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
        padding: 32px;
        box-sizing: border-box;
    }

    .col-vignette {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.2) 0%,
                rgba(0, 0, 0, 0.85) 100%
            );
        z-index: 1;
    }

    .countdown-hud-panel {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 320px;
        background: rgba(14, 14, 14, 0.92);
        border: 1px solid #282828;
        padding: 20px 24px;
        backdrop-filter: blur(6px);
    }

    .hud-label {
        display: block;
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #777777;
        margin-bottom: 8px;
    }

    .countdown-digits {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .digit-box {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .digit-num {
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
        color: #ffffff;
    }

    .digit-unit {
        font-size: 8px;
        letter-spacing: 0.1em;
        color: #666666;
        margin-top: 4px;
    }

    .digit-sep {
        font-size: 20px;
        color: #444444;
    }

    .hud-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #888888;
        border-top: 1px solid #222222;
        padding-top: 8px;
    }

    .dot-red-pulse {
        color: #d32f2f;
        font-size: 8px;
    }

    .section-journal-strip {
        padding: 80px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .journal-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 36px;
    }

    .journal-title {
        font-size: clamp(28px, 3.8vw, 42px);
        font-weight: 900;
        letter-spacing: 0.04em;
        margin: 0 0 6px;
        color: #ffffff;
    }

    .journal-sub {
        font-size: 13px;
        color: #777777;
        margin: 0;
    }

    .journal-top-link {
        font-size: 11px;
        letter-spacing: 0.15em;
        color: #ffffff;
        font-weight: 700;
    }

    .journal-top-link:hover {
        color: #d32f2f;
    }

    .journal-articles-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr;
        gap: 24px;
    }

    .journal-card {
        background-color: #121212;
        border: 1px solid #202020;
        display: flex;
        flex-direction: column;
    }

    .journal-card .card-thumb {
        height: 240px;
        background-size: cover;
        background-position: center;
        background-color: #161616;
    }

    .journal-card .card-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .post-date {
        font-size: 9px;
        letter-spacing: 0.15em;
        color: #666666;
        margin-bottom: 8px;
    }

    .post-title {
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin: 0 0 10px;
        color: #ffffff;
    }

    .post-snippet {
        font-size: 12px;
        color: #888888;
        line-height: 1.5;
        margin: 0 0 18px;
    }

    .post-link {
        margin-top: auto;
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #ffffff;
        font-weight: 700;
    }

    .post-link:hover {
        color: #d32f2f;
    }

    .section-essentials {
        padding: 80px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .essentials-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 32px;
    }

    .essentials-sub {
        font-size: 13px;
        color: #777777;
        margin: 0;
    }

    .link-all {
        font-size: 11px;
        letter-spacing: 0.15em;
        color: #ffffff;
        font-weight: 700;
    }

    .link-all:hover {
        color: #d32f2f;
    }

    .essentials-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr) 1.2fr;
        gap: 18px;
    }

    .craftsmanship-card {
        position: relative;
        min-height: 280px;
        background-size: cover;
        background-position: center;
        background-color: #141414;
        border: 1px solid #202020;
        display: flex;
        align-items: flex-end;
        padding: 24px;
        box-sizing: border-box;
    }

    .craft-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.1) 0%,
                rgba(0, 0, 0, 0.85) 100%
            );
        z-index: 1;
    }

    .craft-body {
        position: relative;
        z-index: 2;
    }

    .craft-kicker {
        display: block;
        font-size: 9px;
        letter-spacing: 0.2em;
        color: #888888;
        margin-bottom: 4px;
    }

    .craft-title {
        font-size: 18px;
        font-weight: 900;
        letter-spacing: 0.04em;
        margin: 0 0 12px;
        color: #ffffff;
    }

    .craft-link {
        font-size: 10px;
        letter-spacing: 0.12em;
        color: #ffffff;
        font-weight: 700;
    }

    .craft-link:hover {
        color: #d32f2f;
    }

    .noad-home-footer {
        background-color: #070707;
        padding: 60px 0 36px;
    }

    .footer-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid #181818;
        padding-bottom: 40px;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 32px;
    }

    .brand-lockup {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .noad-logo-monogram {
        display: flex;
        flex-direction: column;
        font-size: 16px;
        font-weight: 900;
        line-height: 1;
        border: 1px solid #282828;
        padding: 6px 8px;
        color: #ffffff;
    }

    .brand-title {
        display: block;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: 0.1em;
        color: #ffffff;
    }

    .brand-sub {
        display: block;
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #666666;
    }

    .brand-slogan {
        display: block;
        font-size: 9px;
        letter-spacing: 0.12em;
        color: #d32f2f;
        margin-top: 4px;
    }

    .newsletter-block {
        max-width: 440px;
        width: 100%;
    }

    .newsletter-title {
        display: block;
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .newsletter-desc {
        font-size: 12px;
        color: #777777;
        margin: 0 0 14px;
    }

    .newsletter-form-inline {
        display: flex;
        border: 1px solid #242424;
    }

    .input-news {
        flex: 1;
        background-color: #0e0e0e;
        border: none;
        outline: none;
        color: #ffffff;
        padding: 12px 14px;
        font-size: 11px;
    }

    .btn-news-submit {
        background-color: #ffffff;
        color: #000000;
        border: none;
        padding: 0 20px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-news-submit:hover {
        background-color: #d32f2f;
        color: #ffffff;
    }

    .footer-main-nav {
        display: flex;
        gap: 28px;
        font-size: 11px;
        letter-spacing: 0.15em;
        color: #888888;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .footer-main-nav a:hover {
        color: #ffffff;
    }

    .footer-bottom-bar {
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        letter-spacing: 0.12em;
        color: #555555;
        flex-wrap: wrap;
        gap: 16px;
    }

    .legal-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .social-links {
        display: flex;
        gap: 16px;
    }

    .legal-links a:hover,
    .social-links a:hover {
        color: #ffffff;
    }

    @media (max-width: 1200px) {

        .essentials-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 1024px) {

        .collection-split-layout {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 960px) {

        .drop-banner-box {
            grid-template-columns: 1fr;
        }

        .journal-articles-grid {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 820px) {

        .collection-products-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 768px) {

        .container-home {
            padding-left: 20px;
            padding-right: 20px;
        }

        .home-hero-section {
            padding: 40px 20px 24px;
        }

        .hero-main-title {
            font-size: clamp(42px, 12vw, 72px);
        }

        .journal-header-row,
        .essentials-header-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .footer-top-row {
            flex-direction: column;
        }

        .footer-bottom-bar {
            flex-direction: column;
        }

    }

    @media (max-width: 600px) {

        .collection-products-grid,
        .essentials-grid {
            grid-template-columns: 1fr;
        }

        .card-media {
            height: 320px;
        }

        .drop-info-col {
            padding: 28px 20px;
        }

        .drop-countdown-col {
            min-height: 320px;
            padding: 20px;
        }

        .drop-actions-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .countdown-hud-panel {
            max-width: none;
        }

        .footer-main-nav {
            gap: 16px;
        }

    }

</style>

@endsection