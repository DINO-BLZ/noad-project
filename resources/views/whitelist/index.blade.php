@extends('layouts.app')

@section('content')

<div class="noad-whitelist-page">

    {{-- =========================================================
         01. BANDE TECHNIQUE SUPÉRIEURE
    ========================================================== --}}

    <div class="wl-top-strip">
        <div class="container-wl strip-flex">

            <div class="strip-left font-mono">
                <span class="dot-red-sq">■</span>
                SYS.ID: WL-ALGER-2026 // ADMISSION RESTREINTE
            </div>

            <div class="strip-right font-mono">
                <span class="pulse-dot">●</span>
                REGISTRE OPÉRATIONNEL // CANDIDATURE UNIQUE PAR COMPTE
            </div>

        </div>
    </div>


    <div class="container-wl">

        {{-- =====================================================
             02. EN-TÊTE & FIL D'ARIANE
        ====================================================== --}}

        <div class="wl-header-section">

            <div class="breadcrumbs-row font-mono">

                <a href="{{ route('home') }}">
                    ACCUEIL
                </a>

                <span class="sep">/</span>

                <span class="sep-user">
                    COMPTE
                </span>

                <span class="sep">/</span>

                <span class="active">
                    REGISTRE WHITELIST
                </span>

                <span class="meta-tag">
                    PROTOCOLE D'ACCÈS ANTICIPÉ
                </span>

            </div>


            <div class="header-split-row">

                <div class="header-text-block">

                    <h1 class="wl-main-title">
                        REGISTRE WHITELIST
                    </h1>

                    <p class="wl-lead-desc">
                        L'accès aux allocations numérotées et drops restreints NOAD
                        s'effectue exclusivement sous réserve de validation par notre atelier.
                        Les adhérents enregistrés disposent d'un créneau prioritaire sécurisé
                        par clé cryptée.
                    </p>

                </div>


                <div class="wl-stat-widget font-mono">

                    <span class="widget-label">
                        STATUT DU PROFIL
                    </span>

                    <div class="widget-val text-accent">
                        1 CANDIDATURE ACTIVE
                    </div>

                    <span class="widget-sub">
                        VÉRIFICATION D'IDENTITÉ : CONFORME
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
             03. MESSAGES FLASH
        ====================================================== --}}

        @if(session('success'))

            <div class="alert-box alert-success font-mono">

                <span class="dot-green">
                    ✔
                </span>

                {{ session('success') }}

            </div>

        @endif


        @if(session('error'))

            <div class="alert-box alert-error font-mono">

                <span class="dot-red">
                    ✖
                </span>

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             04. GRILLE PRINCIPALE
                 FORMULAIRE + PROTOCOLE
        ====================================================== --}}

        <div class="wl-main-grid">


            {{-- =================================================
                 04.1 COLONNE GAUCHE — FORMULAIRE
            ================================================== --}}

            <div class="wl-form-column">

                <div class="form-tactical-card">


                    {{-- HEADER FORMULAIRE --}}

                    <div class="card-header-bar font-mono">

                        <span class="card-step-num">
                            01 // POSTULATION
                        </span>

                        <h2 class="card-title">
                            SOUMETTRE UNE CANDIDATURE
                        </h2>

                        <span class="card-drop-tag">
                            DROP ACTUEL & À VENIR
                        </span>

                    </div>


                    {{-- FORMULAIRE --}}

                    <form
                        action="{{ route('whitelist.store') }}"
                        method="POST"
                        class="tactical-form"
                    >

                        @csrf


                        {{-- -----------------------------------------
                             DROP
                        ------------------------------------------ --}}

                        <div class="form-field-group">

                            <label
                                for="drop_id"
                                class="field-label font-mono"
                            >
                                CHOIX DE LA SESSION // DROP DISPONIBLE *
                            </label>

                            <select
                                name="drop_id"
                                id="drop_id"
                                class="input-select font-mono"
                                required
                            >

                                @if(isset($drops) && $drops->count() > 0)

                                    @foreach($drops as $availableDrop)

                                        <option
                                            value="{{ $availableDrop->id }}"
                                            {{ old('drop_id') == $availableDrop->id ? 'selected' : '' }}
                                        >
                                            {{ strtoupper($availableDrop->code ?? 'DROP') }}
                                            —
                                            {{ strtoupper($availableDrop->name) }}
                                            [OUVERTURE :
                                            {{ $availableDrop->release_date
                                                ? $availableDrop->release_date->format('d.m.Y')
                                                : 'À VENIR'
                                            }}]
                                        </option>

                                    @endforeach

                                @else

                                    <option value="2" selected>
                                        DROP 02 — URBAN ARMOUR
                                        [DÉCEMBRE 2026 // ESTIMÉ 250 UNITÉS]
                                    </option>

                                    <option value="1">
                                        DROP 01 — THE RESISTANCE
                                        [SESSION LIVE // CLÔTURE IMMINENTE]
                                    </option>

                                    <option value="3">
                                        DROP 03 — CASUAL ARCHIVE
                                        [FÉVRIER 2027 // INSCRIPTIONS PRÉ-TEST]
                                    </option>

                                @endif

                            </select>

                        </div>


                        {{-- -----------------------------------------
                             NOM + EMAIL
                        ------------------------------------------ --}}

                        <div class="form-row-duo">

                            <div class="form-field-group">

                                <label
                                    for="name"
                                    class="field-label font-mono"
                                >
                                    NOM COMPLET DE L'ADHÉRENT *
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', auth()->user()->name ?? '') }}"
                                    placeholder="Ex. Belhocine Mouaad"
                                    class="input-text font-mono"
                                    required
                                >

                            </div>


                            <div class="form-field-group">

                                <label
                                    for="email"
                                    class="field-label font-mono"
                                >
                                    ADRESSE EMAIL SÉCURISÉE *
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', auth()->user()->email ?? '') }}"
                                    placeholder="nom@exemple.com"
                                    class="input-text font-mono"
                                    required
                                >

                            </div>

                        </div>


                        {{-- -----------------------------------------
                             TÉLÉPHONE + WILAYA
                        ------------------------------------------ --}}

                        <div class="form-row-duo">

                            <div class="form-field-group">

                                <label
                                    for="phone"
                                    class="field-label font-mono"
                                >
                                    TÉLÉPHONE MOBILE (RÉCEPTION CLÉ SMS) *
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                    placeholder="05 / 06 / 07 XX XX XX"
                                    class="input-text font-mono"
                                    required
                                >

                            </div>


                            <div class="form-field-group">

                                <label
                                    for="wilaya"
                                    class="field-label font-mono"
                                >
                                    WILAYA DE LIVRAISON *
                                </label>

                                <select
                                    name="wilaya"
                                    id="wilaya"
                                    class="input-select font-mono"
                                    required
                                >

                                    <option value="">
                                        Sélectionnez votre wilaya (58 wilayas)
                                    </option>

                                    <option value="16 - Alger" selected>
                                        16 — Alger
                                    </option>

                                    <option value="31 - Oran">
                                        31 — Oran
                                    </option>

                                    <option value="25 - Constantine">
                                        25 — Constantine
                                    </option>

                                    <option value="06 - Béjaïa">
                                        06 — Béjaïa
                                    </option>

                                    <option value="15 - Tizi Ouzou">
                                        15 — Tizi Ouzou
                                    </option>

                                    <option value="19 - Sétif">
                                        19 — Sétif
                                    </option>

                                    <option value="23 - Annaba">
                                        23 — Annaba
                                    </option>

                                    <option value="09 - Blida">
                                        09 — Blida
                                    </option>

                                    <option value="35 - Boumerdès">
                                        35 — Boumerdès
                                    </option>

                                    <option value="autre">
                                        Autres wilayas (Réseau national)
                                    </option>

                                </select>

                            </div>

                        </div>


                        {{-- -----------------------------------------
                             TAILLE
                        ------------------------------------------ --}}

                        <div class="form-field-group">

                            <label class="field-label font-mono">
                                TAILLE DE PRÉDILECTION (ALLOCATION ESTIMÉE) *
                            </label>

                            <div class="size-selector-strip font-mono">

                                @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)

                                    <label class="size-choice-radio">

                                        <input
                                            type="radio"
                                            name="preferred_size"
                                            value="{{ $size }}"
                                            {{ old('preferred_size', 'M') == $size ? 'checked' : '' }}
                                        >

                                        <span class="radio-box">
                                            {{ $size }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                        </div>


                        {{-- -----------------------------------------
                             MOTIVATION
                        ------------------------------------------ --}}

                        <div class="form-field-group">

                            <label
                                for="motivation"
                                class="field-label font-mono"
                            >
                                NOTE DE MOTIVATION & ENGAGEMENT TERRACE (FACULTATIF)
                            </label>

                            <textarea
                                id="motivation"
                                name="motivation"
                                rows="3"
                                placeholder="Indiquez votre attachement aux pièces intemporelles NOAD ou vos précédentes acquisitions..."
                                class="input-textarea font-mono"
                            >{{ old('motivation') }}</textarea>

                        </div>


                        {{-- -----------------------------------------
                             ACCORD D'ENGAGEMENT
                        ------------------------------------------ --}}

                        <div class="agreement-box font-mono">

                            <label class="checkbox-container">

                                <input
                                    type="checkbox"
                                    name="terms_whitelist"
                                    required
                                    checked
                                >

                                <span class="checkmark"></span>

                                <span class="terms-text">
                                    Je certifie l'authenticité de mes coordonnées.
                                    Je reconnais que toute tentative de revente spéculative
                                    entraînera la résiliation immédiate de ma clé d'accès
                                    et l'exclusion définitive du registre NOAD.
                                </span>

                            </label>

                        </div>


                        {{-- -----------------------------------------
                             SUBMIT
                        ------------------------------------------ --}}

                        <button
                            type="submit"
                            class="btn-submit-candidature font-mono"
                        >
                            TRANSMETTRE LA CANDIDATURE

                            <span class="shield-glyph">
                                🛡
                            </span>

                        </button>

                    </form>

                </div>

            </div>


            {{-- =================================================
                 04.2 COLONNE DROITE — PROTOCOLE
            ================================================== --}}

            <div class="wl-info-column">


                {{-- PROTOCOLE D'ADMISSION --}}

                <div class="protocol-card">

                    <div class="proto-top font-mono">

                        <span class="proto-kicker text-accent">
                            ■ PROTOCOLE D'ADMISSION
                        </span>

                        <span class="proto-version">
                            REV. 2026.04
                        </span>

                    </div>


                    <h3 class="proto-title">
                        COMMENT FONCTIONNE LA WHITELIST ?
                    </h3>


                    <div class="proto-steps-list">


                        {{-- ÉTAPE 01 --}}

                        <div class="proto-step-item">

                            <div class="step-num font-bold text-accent">
                                01
                            </div>

                            <div class="step-body">

                                <h4 class="step-head font-mono">
                                    SOUMISSION DU DOSSIER
                                </h4>

                                <p class="step-txt">
                                    Votre profil est examiné par l'équipe d'attribution
                                    afin d'éliminer les scripts automatisés et faux comptes.
                                </p>

                            </div>

                        </div>


                        {{-- ÉTAPE 02 --}}

                        <div class="proto-step-item">

                            <div class="step-num font-bold text-accent">
                                02
                            </div>

                            <div class="step-body">

                                <h4 class="step-head font-mono">
                                    RÉCEPTION DE LA CLÉ UNIQUE
                                </h4>

                                <p class="step-txt">
                                    Si votre demande est retenue, vous recevez un token chiffré
                                    par SMS et email 2 heures avant le drop public.
                                </p>

                            </div>

                        </div>


                        {{-- ÉTAPE 03 --}}

                        <div class="proto-step-item">

                            <div class="step-num font-bold text-accent">
                                03
                            </div>

                            <div class="step-body">

                                <h4 class="step-head font-mono">
                                    SESSION PRIVÉE DE COMMANDE
                                </h4>

                                <p class="step-txt">
                                    La clé vous ouvre l'accès direct aux pièces numérotées,
                                    avec paiement en DA à la livraison sur 58 Wilayas.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- ENGAGEMENT NOAD --}}

                    <div class="anti-bot-pledge font-mono">

                        <div class="pledge-icon">
                            ⚙
                        </div>

                        <div class="pledge-text">

                            <strong>
                                ENGAGEMENT STRICT NOAD :
                            </strong>

                            <br>

                            « DEFEND YOUR PRINCIPLE » —
                            Zéro passe-droit, zéro réassortiment sauvage.

                        </div>

                    </div>

                </div>


                {{-- SUPPORT --}}

                <div class="support-card font-mono">

                    <span class="support-tag">
                        BESOIN D'ASSISTANCE SUR VOTRE CLÉ ?
                    </span>

                    <p class="support-p">
                        Un problème avec votre validation ou votre numéro de téléphone ?
                        Contactez le bureau d'admission NOAD.
                    </p>

                    <a
                        href="{{ route('contact') }}"
                        class="support-link"
                    >
                        CONTACTER LE SUPPORT WHITELIST →
                    </a>

                </div>

            </div>

        </div>


        {{-- =====================================================
             05. HISTORIQUE DES DEMANDES
        ====================================================== --}}

        <div class="wl-history-section">

            <div class="history-header-bar font-mono">

                <div class="hist-left">

                    <span class="hist-sec-num text-accent">
                        02 // HISTORIQUE
                    </span>

                    <h2 class="hist-title">
                        MES CANDIDATURES & ACCÈS ATTRIBUÉS
                    </h2>

                </div>

                <div class="hist-right">
                    <span>
                        REGISTRE EN TEMPS RÉEL
                    </span>
                </div>

            </div>


            <div class="history-table-holder">

                <table class="wl-table font-mono">

                    <thead>

                        <tr>
                            <th>RÉFÉRENCE DROP</th>
                            <th>DATE SOUMISSION</th>
                            <th>STATUT ADMISSION</th>
                            <th>CLÉ CRYPTÉE ATTRIBUÉE</th>
                            <th>FENÊTRE PRIORITAIRE</th>
                            <th class="text-right">ACTION</th>
                        </tr>

                    </thead>


                    <tbody>

                        @if(isset($applications) && count($applications) > 0)

                            @foreach($applications as $app)

                                <tr>

                                    <td>

                                        <div class="drop-cell-title font-bold">
                                            {{ strtoupper($app->drop->name ?? 'DROP') }}
                                        </div>

                                        <div class="drop-cell-sub">
                                            {{ $app->drop->code ?? 'SERIES NOAD' }}
                                        </div>

                                    </td>


                                    <td>
                                        {{ $app->created_at
                                            ? $app->created_at->format('d.m.Y — H:i')
                                            : '14.09.2026'
                                        }}
                                    </td>


                                    <td>

                                        @if($app->status === 'approved')

                                            <span class="status-badge badge-approved">
                                                ● APPROUVÉE
                                            </span>

                                        @elseif($app->status === 'pending')

                                            <span class="status-badge badge-pending">
                                                ● EN REVUE
                                            </span>

                                        @else

                                            <span class="status-badge badge-closed">
                                                ✕ CLÔTURÉE
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if($app->access_key)

                                            <span class="key-code font-bold">
                                                {{ $app->access_key }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                EN COURS D'ATTRIBUTION
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if($app->access_window)

                                            <span class="time-window">
                                                {{ $app->access_window }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                COMMUNIQUÉ PAR SMS
                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-right">

                                        @if($app->status === 'approved')

                                            <a
                                                href="{{ route('drops.show', $app->drop_id) }}"
                                                class="btn-table-action"
                                            >
                                                ACCÉDER →
                                            </a>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        @else


                            {{-- =====================================
                                 FALLBACK / DONNÉES DE DÉMONSTRATION
                            ====================================== --}}


                            {{-- DROP 01 --}}

                            <tr>

                                <td>

                                    <div class="drop-cell-title font-bold">
                                        DROP 01 — THE RESISTANCE
                                    </div>

                                    <div class="drop-cell-sub">
                                        SERIES // 01 • AUTUMN 26
                                    </div>

                                </td>

                                <td>
                                    14.09.2026 — 11:24
                                </td>

                                <td>

                                    <span class="status-badge badge-approved">
                                        ● APPROUVÉE
                                    </span>

                                </td>

                                <td>

                                    <span class="key-code font-bold">
                                        WL-01-8842-ALG
                                    </span>

                                </td>

                                <td>

                                    <span class="time-window">
                                        18.09.2026 @ 18:00 (H-2)
                                    </span>

                                </td>

                                <td class="text-right">

                                    <a
                                        href="{{ route('drops.show', 1) }}"
                                        class="btn-table-action"
                                    >
                                        ACCÉDER →
                                    </a>

                                </td>

                            </tr>


                            {{-- DROP 02 --}}

                            <tr>

                                <td>

                                    <div class="drop-cell-title font-bold">
                                        DROP 02 — URBAN ARMOUR
                                    </div>

                                    <div class="drop-cell-sub">
                                        SERIES // 02 • WINTER 26
                                    </div>

                                </td>

                                <td>
                                    Hier — 19:40
                                </td>

                                <td>

                                    <span class="status-badge badge-pending">
                                        ● EN COURS DE REVUE
                                    </span>

                                </td>

                                <td>

                                    <span class="text-muted">
                                        GÉNÉRATION SOUS 24H
                                    </span>

                                </td>

                                <td>

                                    <span class="text-muted">
                                        OUVERTURE DÉCEMBRE
                                    </span>

                                </td>

                                <td class="text-right">

                                    <span class="badge-waiting font-mono">
                                        FILE #182
                                    </span>

                                </td>

                            </tr>


                            {{-- CAPSULE 0.5 --}}

                            <tr>

                                <td>

                                    <div class="drop-cell-title font-bold">
                                        CAPSULE 0.5 — DERBY NOIR
                                    </div>

                                    <div class="drop-cell-sub">
                                        ARCHIVE // PRINTEMPS 26
                                    </div>

                                </td>

                                <td>
                                    10.05.2026
                                </td>

                                <td>

                                    <span class="status-badge badge-closed">
                                        ✕ CLÔTURÉE
                                    </span>

                                </td>

                                <td>

                                    <span class="text-muted">
                                        SESSION TERMINÉE
                                    </span>

                                </td>

                                <td>

                                    <span class="text-muted">
                                        SOLD OUT
                                    </span>

                                </td>

                                <td class="text-right">

                                    <span class="text-muted">
                                        ARCHIVÉ
                                    </span>

                                </td>

                            </tr>

                        @endif

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- =========================================================
         06. FOOTER GLOBAL NOAD
    ========================================================== --}}

    <footer class="noad-wl-footer">

        <div class="container-wl">

            {{-- FOOTER TOP --}}

            <div class="footer-top-split">


                {{-- IDENTITÉ NOAD --}}

                <div class="footer-brand font-mono">

                    <div class="brand-monogram">

                        <span>NO</span>
                        <span>AD</span>

                    </div>

                    <div class="brand-copy">

                        <span class="b-name">
                            NOAD
                        </span>

                        <span class="b-sub">
                            NO ADVANTAGE
                        </span>

                        <span class="b-slogan text-accent">
                            DEFEND YOUR PRINCIPLES
                        </span>

                    </div>

                </div>


                {{-- NEWSLETTER --}}

                <div class="footer-news">

                    <span class="news-title font-mono">
                        REJOIGNEZ LA COMMUNAUTÉ
                    </span>

                    <p class="news-desc">
                        Recevez les notifications officielles d'ouverture
                        de registre Whitelist.
                    </p>

                    <form
                        action="{{ route('newsletter.subscribe') }}"
                        method="POST"
                        class="news-form-bar"
                    >

                        @csrf

                        <input
                            type="email"
                            name="email"
                            placeholder="Votre adresse e-mail"
                            required
                            class="input-news-text font-mono"
                        >

                        <button
                            type="submit"
                            class="btn-news-submit font-mono"
                        >
                            →
                        </button>

                    </form>

                </div>

            </div>


            {{-- NAVIGATION FOOTER --}}

            <nav class="footer-nav-row font-mono">

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


            {{-- LEGAL + SOCIAL --}}

            <div class="footer-bottom-legal font-mono">

                <div class="legal-links">

                    <a href="{{ route('shipping') }}">
                        LIVRAISON 58 WILAYAS
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('returns') }}">
                        RETOURS
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('faq') }}">
                        FAQ WHITELIST
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('cgv') }}">
                        CGV
                    </a>

                    <span class="sep">/</span>

                    <a href="{{ route('legal') }}">
                        MENTIONS LÉGALES
                    </a>

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
     07. STYLESHEET
     NOAD WHITELIST REGISTRATION — TERRACE BRUTALISM
============================================================= --}}

<style>

/* ================================================================
   01. BASE
================================================================ */

.noad-whitelist-page {
    width: 100%;
    min-height: 100vh;
    background-color: #0c0c0c;
    color: #e5e5e5;
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    box-sizing: border-box;
}

.noad-whitelist-page a {
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
    color: #666666 !important;
}

.text-right {
    text-align: right;
}

.container-wl {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 40px;
}


/* ================================================================
   02. BARRE TECHNIQUE
================================================================ */

.wl-top-strip {
    background-color: #070707;
    border-bottom: 1px solid #1a1a1a;
    padding: 10px 0;
    font-size: 10px;
    letter-spacing: 0.15em;
    color: #777777;
}

.strip-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dot-red-sq {
    color: #d32f2f;
    font-size: 8px;
    margin-right: 6px;
}

.pulse-dot {
    color: #2ecc71;
    animation: blinkAnim 1.5s infinite;
}

@keyframes blinkAnim {
    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.3;
    }
}


/* ================================================================
   03. HEADER
================================================================ */

.wl-header-section {
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

.breadcrumbs-row .meta-tag {
    margin-left: 10px;
    color: #d32f2f;
    background-color: #140d0d;
    padding: 2px 6px;
    border: 1px solid #331515;
}

.header-split-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
    flex-wrap: wrap;
}

.wl-main-title {
    font-size: clamp(38px, 5vw, 64px);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.95;
    margin: 0 0 12px;
    color: #ffffff;
    text-transform: uppercase;
}

.wl-lead-desc {
    font-size: 14px;
    color: #888888;
    max-width: 720px;
    margin: 0;
    line-height: 1.55;
}

.wl-stat-widget {
    background-color: #121212;
    border: 1px solid #242424;
    padding: 16px 22px;
    text-align: right;
}

.widget-label {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #666666;
    display: block;
    margin-bottom: 4px;
}

.widget-val {
    font-size: 15px;
    font-weight: 900;
    letter-spacing: 0.08em;
    margin-bottom: 2px;
}

.widget-sub {
    font-size: 8px;
    color: #777777;
    letter-spacing: 0.12em;
}


/* ================================================================
   04. ALERTES
================================================================ */

.alert-box {
    padding: 14px 18px;
    margin-bottom: 28px;
    font-size: 11px;
    letter-spacing: 0.12em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #0b180e;
    border: 1px solid #1e4624;
    color: #4ade80;
}

.alert-error {
    background-color: #1c0e0e;
    border: 1px solid #5a1d1d;
    color: #f87171;
}


/* ================================================================
   05. GRILLE PRINCIPALE
================================================================ */

.wl-main-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 32px;
    margin-bottom: 64px;
}


/* ================================================================
   06. FORMULAIRE
================================================================ */

.form-tactical-card {
    background-color: #121212;
    border: 1px solid #242424;
    padding: 36px;
}

.card-header-bar {
    border-bottom: 1px solid #1f1f1f;
    padding-bottom: 16px;
    margin-bottom: 24px;
}

.card-step-num {
    font-size: 9px;
    letter-spacing: 0.2em;
    color: #d32f2f;
    display: block;
    margin-bottom: 4px;
}

.card-title {
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0 0 4px;
    color: #ffffff;
    text-transform: uppercase;
}

.card-drop-tag {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #666666;
}

.tactical-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-row-duo {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-label {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #888888;
}

.input-text,
.input-select,
.input-textarea {
    background-color: #0e0e0e;
    border: 1px solid #262626;
    color: #ffffff;
    padding: 12px 14px;
    font-size: 11px;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
    width: 100%;
}

.input-text:focus,
.input-select:focus,
.input-textarea:focus {
    border-color: #666666;
}

.input-textarea {
    resize: vertical;
    line-height: 1.5;
}


/* ================================================================
   07. SÉLECTEUR DE TAILLE
================================================================ */

.size-selector-strip {
    display: flex;
    gap: 8px;
}

.size-choice-radio input {
    display: none;
}

.radio-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 40px;
    background-color: #0e0e0e;
    border: 1px solid #262626;
    color: #888888;
    font-size: 11px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
}

.size-choice-radio input:checked + .radio-box {
    background-color: #ffffff;
    color: #000000;
    border-color: #ffffff;
}

.radio-box:hover {
    border-color: #444444;
    color: #ffffff;
}


/* ================================================================
   08. ACCORD D'ENGAGEMENT
================================================================ */

.agreement-box {
    background-color: #0d0d0d;
    border: 1px solid #1f1f1f;
    padding: 14px;
    font-size: 10px;
    color: #777777;
    line-height: 1.5;
}

.checkbox-container {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
}

.checkbox-container input {
    margin-top: 2px;
    accent-color: #d32f2f;
}


/* ================================================================
   09. BOUTON DE SOUMISSION
================================================================ */

.btn-submit-candidature {
    background-color: #ffffff;
    color: #000000;
    border: 1px solid #ffffff;
    padding: 16px 24px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
}

.btn-submit-candidature:hover {
    background-color: #d32f2f;
    border-color: #d32f2f;
    color: #ffffff;
}


/* ================================================================
   10. COLONNE PROTOCOLE
================================================================ */

.wl-info-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.protocol-card {
    background-color: #121212;
    border: 1px solid #242424;
    padding: 32px;
}

.proto-top {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    letter-spacing: 0.15em;
    margin-bottom: 12px;
}

.proto-version {
    color: #666666;
}

.proto-title {
    font-size: 20px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0 0 24px;
    color: #ffffff;
    text-transform: uppercase;
}

.proto-steps-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    border-bottom: 1px solid #1f1f1f;
    padding-bottom: 24px;
    margin-bottom: 24px;
}

.proto-step-item {
    display: flex;
    gap: 16px;
}

.step-num {
    font-size: 24px;
    line-height: 1;
}

.step-head {
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 0.12em;
    margin: 0 0 4px;
    color: #ffffff;
}

.step-txt {
    font-size: 12px;
    color: #888888;
    line-height: 1.5;
    margin: 0;
}

.anti-bot-pledge {
    background-color: #0e0e0e;
    border: 1px solid #1f1f1f;
    padding: 14px 16px;
    display: flex;
    gap: 12px;
    align-items: center;
    font-size: 10px;
    color: #aaaaaa;
    line-height: 1.45;
}

.pledge-icon {
    color: #d32f2f;
    font-size: 18px;
}


/* ================================================================
   11. SUPPORT
================================================================ */

.support-card {
    background-color: #101010;
    border: 1px solid #202020;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.support-tag {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #888888;
}

.support-p {
    font-size: 11px;
    color: #666666;
    margin: 0 0 8px;
}

.support-link {
    font-size: 10px;
    font-weight: bold;
    letter-spacing: 0.12em;
    color: #ffffff;
    transition: color 0.2s;
}

.support-link:hover {
    color: #d32f2f;
}


/* ================================================================
   12. HISTORIQUE
================================================================ */

.wl-history-section {
    margin-bottom: 64px;
}

.history-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-bottom: 1px solid #242424;
    padding-bottom: 12px;
    margin-bottom: 24px;
}

.hist-sec-num {
    font-size: 9px;
    letter-spacing: 0.2em;
    display: block;
    margin-bottom: 2px;
}

.hist-title {
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.04em;
    margin: 0;
    color: #ffffff;
    text-transform: uppercase;
}

.hist-right {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #666666;
}

.history-table-holder {
    background-color: #121212;
    border: 1px solid #242424;
    overflow-x: auto;
}

.wl-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    text-align: left;
}

.wl-table th {
    background-color: #0e0e0e;
    color: #777777;
    font-weight: normal;
    letter-spacing: 0.12em;
    padding: 14px 18px;
    border-bottom: 1px solid #1f1f1f;
    font-size: 9px;
}

.wl-table td {
    padding: 18px;
    border-bottom: 1px solid #1a1a1a;
    vertical-align: middle;
}

.wl-table tr:last-child td {
    border-bottom: none;
}

.drop-cell-title {
    color: #ffffff;
    font-size: 12px;
    letter-spacing: 0.04em;
}

.drop-cell-sub {
    font-size: 9px;
    color: #666666;
}


/* ================================================================
   13. STATUS
================================================================ */

.status-badge {
    font-size: 9px;
    letter-spacing: 0.12em;
    padding: 4px 8px;
    display: inline-block;
    font-weight: bold;
}

.badge-approved {
    background-color: rgba(46, 204, 113, 0.12);
    border: 1px solid #2ecc71;
    color: #2ecc71;
}

.badge-pending {
    background-color: rgba(243, 156, 18, 0.12);
    border: 1px solid #f39c12;
    color: #f39c12;
}

.badge-closed {
    background-color: #1a1a1a;
    border: 1px solid #333333;
    color: #777777;
}

.key-code {
    background-color: #0a0a0a;
    border: 1px solid #2a2a2a;
    padding: 4px 8px;
    color: #ffffff;
    letter-spacing: 0.1em;
}

.time-window {
    color: #cccccc;
}

.btn-table-action {
    background-color: #ffffff;
    color: #000000;
    font-weight: bold;
    font-size: 9px;
    letter-spacing: 0.12em;
    padding: 6px 12px;
    transition: all 0.2s;
    display: inline-block;
}

.btn-table-action:hover {
    background-color: #d32f2f;
    color: #ffffff;
}

.badge-waiting {
    background-color: #181818;
    border: 1px solid #282828;
    color: #888888;
    font-size: 9px;
    padding: 4px 8px;
}


/* ================================================================
   14. FOOTER
================================================================ */

.noad-wl-footer {
    background-color: #080808;
    border-top: 1px solid #1a1a1a;
    padding: 54px 0 36px;
}

.footer-top-split {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 36px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 28px;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.brand-monogram {
    display: flex;
    flex-direction: column;
    font-size: 16px;
    font-weight: 900;
    line-height: 1;
    border: 1px solid #282828;
    padding: 6px 8px;
    color: #ffffff;
}

.brand-copy {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.b-name {
    font-size: 16px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 0.1em;
}

.b-sub {
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #666666;
}

.b-slogan {
    font-size: 9px;
    letter-spacing: 0.12em;
}


/* ================================================================
   15. NEWSLETTER
================================================================ */

.footer-news {
    max-width: 440px;
    width: 100%;
}

.news-title {
    font-size: 10px;
    letter-spacing: 0.15em;
    color: #ffffff;
    display: block;
    margin-bottom: 4px;
}

.news-desc {
    font-size: 12px;
    color: #777777;
    margin: 0 0 14px;
}

.news-form-bar {
    display: flex;
    border: 1px solid #222222;
}

.input-news-text {
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


/* ================================================================
   16. NAVIGATION FOOTER
================================================================ */

.footer-nav-row {
    display: flex;
    gap: 24px;
    font-size: 11px;
    letter-spacing: 0.15em;
    color: #888888;
    margin-bottom: 28px;
    flex-wrap: wrap;
}

.footer-nav-row a:hover {
    color: #ffffff;
}


/* ================================================================
   17. LEGAL + RÉSEAUX SOCIAUX
================================================================ */

.footer-bottom-legal {
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

.legal-links a:hover,
.social-links a:hover {
    color: #ffffff;
}

.social-links {
    display: flex;
    gap: 16px;
}


/* ================================================================
   18. RESPONSIVE — TABLET
================================================================ */

@media (max-width: 1024px) {

    .wl-main-grid {
        grid-template-columns: 1fr;
    }

}


/* ================================================================
   19. RESPONSIVE — MOBILE
================================================================ */

@media (max-width: 680px) {

    .container-wl {
        padding: 0 20px;
    }

    .strip-flex {
        align-items: flex-start;
        flex-direction: column;
        gap: 6px;
    }

    .form-row-duo {
        grid-template-columns: 1fr;
    }

    .form-tactical-card,
    .protocol-card {
        padding: 24px;
    }

    .history-header-bar {
        align-items: flex-start;
        flex-direction: column;
        gap: 10px;
    }

    .hist-title {
        font-size: 18px;
    }

    .footer-top-split {
        flex-direction: column;
    }

    .footer-bottom-legal {
        flex-direction: column;
    }

}


/* ================================================================
   20. RESPONSIVE — PETIT MOBILE
================================================================ */

@media (max-width: 480px) {

    .container-wl {
        padding: 0 16px;
    }

    .wl-main-title {
        font-size: 38px;
    }

    .wl-stat-widget {
        width: 100%;
        text-align: left;
        box-sizing: border-box;
    }

    .size-selector-strip {
        flex-wrap: wrap;
    }

    .radio-box {
        width: 42px;
    }

    .form-tactical-card,
    .protocol-card,
    .support-card {
        padding: 20px;
    }

    .footer-nav-row {
        gap: 14px;
    }

}

</style>

@endsection