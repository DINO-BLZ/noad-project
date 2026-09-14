```blade
@extends('layouts.app')

@section('content')

<div class="noad-reset-page">

    <div class="reset-split-container">

        {{-- =========================================================
             COLONNE GAUCHE : VISUEL ÉDITORIAL & PROTOCOLE
        ========================================================== --}}

        <div class="reset-visual-col">

            <div class="visual-bg-overlay"></div>

            {{-- Header métadonnées de sécurité --}}
            <div class="visual-top-strip">

                <span class="sys-protocol-tag">
                    <span class="dot-red-sq">■</span>
                    SYS.AUTH // NOAD-SEC-KEY
                </span>

                <span class="token-status-tag">
                    STATUS: RESET-ACTIVE
                </span>

            </div>

            {{-- Encadré manifeste de sécurité inférieur --}}
            <div class="visual-bottom-card">

                <div class="card-shield-title">
                    <span class="shield-glyph">🛡</span>

                    <span class="shield-label">
                        PROTOCOLE DE SÉCURISATION COMPTE
                    </span>
                </div>

                <blockquote class="card-quote">
                    « LA SÉCURITÉ DE VOTRE ACCÈS GARANTIT L'INTÉGRITÉ
                    DE VOS RÉSERVATIONS DROPS ET DE VOTRE HISTORIQUE WHITELIST. »
                </blockquote>

                <div class="card-meta-foot">
                    <span class="meta-spec">
                        DIVISION SÉCURITÉ NOAD
                    </span>

                    <span class="meta-devise">
                        CHIFFREMENT 256 BITS
                    </span>
                </div>

            </div>

        </div>


        {{-- =========================================================
             COLONNE DROITE : FORMULAIRE DE RÉINITIALISATION
        ========================================================== --}}

        <div class="reset-form-col">

            <div class="reset-form-inner">

                {{-- Fil d'Ariane & Monogramme NOAD --}}
                <div class="form-header-row">

                    <div class="auth-breadcrumbs">

                        <a href="{{ route('home') }}">
                            NOAD
                        </a>

                        <span class="sep">/</span>

                        <span>
                            AUTH
                        </span>

                        <span class="sep">/</span>

                        <span class="breadcrumb-active">
                            NOUVEAU MOT DE PASSE
                        </span>

                    </div>

                    <div class="noad-monogram">
                        <span>NO</span>
                        <span>AD</span>
                    </div>

                </div>


                {{-- Titre --}}
                <h1 class="auth-title">
                    NOUVEAU MOT DE PASSE
                </h1>

                <p class="auth-subtitle">
                    Définissez une clé d'accès robuste pour restaurer
                    l'accès à votre compte et à vos privilèges de drop.
                </p>


                {{-- =====================================================
                     ERREURS DE VALIDATION
                ====================================================== --}}

                @if ($errors->any())

                    <div class="auth-alert-errors">

                        <span class="alert-icon">
                            ⚠
                        </span>

                        <div class="alert-text">

                            @foreach ($errors->all() as $error)

                                <div>
                                    {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif


                {{-- =====================================================
                     FORMULAIRE
                ====================================================== --}}

                <form
                    action="{{ route('password.update') }}"
                    method="POST"
                    class="form-tactical-stack"
                >

                    @csrf

                    {{-- Token Laravel --}}
                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token ?? request()->route('token') }}"
                    >


                    {{-- =================================================
                         CHAMP 01 : EMAIL
                    ================================================== --}}

                    <div class="form-field-wrap">

                        <div class="field-label-row">

                            <label for="email">
                                ADRESSE E-MAIL
                            </label>

                            <span class="label-req">
                                VÉRIFIÉE
                            </span>

                        </div>

                        <div class="input-with-icon">

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ $email ?? old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="nom@exemple.dz"
                                class="input-auth @error('email') has-error @enderror"
                            >

                            <span class="input-icon">
                                ✉
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                         CHAMP 02 : NOUVEAU MOT DE PASSE
                    ================================================== --}}

                    <div class="form-field-wrap">

                        <div class="field-label-row">

                            <label for="password">
                                NOUVEAU MOT DE PASSE
                            </label>

                            <span class="label-req">
                                MIN. 8 CARACTÈRES
                            </span>

                        </div>

                        <div class="input-with-icon">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••••••"
                                class="input-auth @error('password') has-error @enderror"
                                oninput="checkPasswordStrength(this.value)"
                            >

                            <button
                                type="button"
                                class="btn-toggle-pwd"
                                onclick="toggleFieldVisibility('password')"
                                title="Afficher/masquer"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    {{-- =================================================
                         JAUGE DE SÉCURITÉ DU MOT DE PASSE
                    ================================================== --}}

                    <div class="password-meter-wrap">

                        <div class="meter-bar-track">

                            <div
                                class="meter-bar-fill"
                                id="meterFill"
                            ></div>

                        </div>

                        <div class="meter-requirements-row">

                            <span
                                class="req-item"
                                id="reqLen"
                            >
                                ■ 8+ CARACTÈRES
                            </span>

                            <span
                                class="req-item"
                                id="reqUpper"
                            >
                                ■ MAJUSCULE
                            </span>

                            <span
                                class="req-item"
                                id="reqNum"
                            >
                                ■ CHIFFRE / SYMBOLE
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                         CHAMP 03 : CONFIRMATION
                    ================================================== --}}

                    <div class="form-field-wrap">

                        <div class="field-label-row">

                            <label for="password_confirmation">
                                CONFIRMER LE NOUVEAU MOT DE PASSE
                            </label>

                            <span class="label-req">
                                REQUIS
                            </span>

                        </div>

                        <div class="input-with-icon">

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••••••"
                                class="input-auth"
                            >

                            <button
                                type="button"
                                class="btn-toggle-pwd"
                                onclick="toggleFieldVisibility('password_confirmation')"
                                title="Afficher/masquer"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    {{-- =================================================
                         BOUTON SUBMIT
                    ================================================== --}}

                    <button
                        type="submit"
                        class="btn-submit-reset"
                    >
                        RÉINITIALISER ET ACCÉDER

                        <span class="arrow-glyph">
                            →
                        </span>
                    </button>


                    {{-- Retour connexion --}}
                    <div class="reset-back-row">

                        <a
                            href="{{ route('login') }}"
                            class="back-link"
                        >
                            <span class="arrow-back">
                                ←
                            </span>

                            ANNULER ET RETOURNER À LA CONNEXION
                        </a>

                    </div>

                </form>


                {{-- =====================================================
                     FOOTER SÉCURITÉ
                ====================================================== --}}

                <div class="reset-security-footer">

                    <div class="security-indicator">

                        <span class="dot-green">
                            ●
                        </span>

                        PROTOCOLE CHIFFRÉ 256 BITS • SHA-256

                    </div>

                    <span class="protocol-id">
                        NOAD SEC-AUTH // ALGER
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================== --}}

<script>

    function toggleFieldVisibility(id) {

        const field = document.getElementById(id);

        if (!field) {
            return;
        }

        field.type =
            field.type === 'password'
                ? 'text'
                : 'password';
    }


    function checkPasswordStrength(val) {

        let score = 0;

        const lenCheck = val.length >= 8;
        const upperCheck = /[A-Z]/.test(val);
        const numCheck = /[0-9!@#$%^&*(),.?":{}|<>]/.test(val);

        const elLen = document.getElementById('reqLen');
        const elUpper = document.getElementById('reqUpper');
        const elNum = document.getElementById('reqNum');
        const fill = document.getElementById('meterFill');


        if (lenCheck) {

            score += 33;

            elLen.classList.add('valid');

        } else {

            elLen.classList.remove('valid');

        }


        if (upperCheck) {

            score += 33;

            elUpper.classList.add('valid');

        } else {

            elUpper.classList.remove('valid');

        }


        if (numCheck) {

            score += 34;

            elNum.classList.add('valid');

        } else {

            elNum.classList.remove('valid');

        }


        fill.style.width = score + '%';


        if (score < 66) {

            fill.style.backgroundColor =
                'var(--accent, #d32f2f)';

        } else if (score < 100) {

            fill.style.backgroundColor =
                '#e67e22';

        } else {

            fill.style.backgroundColor =
                '#2ecc71';

        }

    }

</script>


{{-- =============================================================
     CSS
============================================================== --}}

<style>

    /* =========================================================
       BASE
    ========================================================== */

    .noad-reset-page {
        width: 100%;
        min-height: calc(100vh - 80px);
        background-color: #0c0c0c;
        color: var(--text, #e5e5e5);
        font-family: 'Barlow Condensed',
            -apple-system,
            BlinkMacSystemFont,
            sans-serif;
        display: flex;
    }


    .noad-reset-page a {
        color: inherit;
        text-decoration: none;
    }


    /* =========================================================
       LAYOUT
    ========================================================== */

    .reset-split-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        min-height: 100%;
    }


    @media (max-width: 960px) {

        .reset-split-container {
            grid-template-columns: 1fr;
        }

        .reset-visual-col {
            display: none !important;
        }

    }


    /* =========================================================
       COLONNE GAUCHE
    ========================================================== */

    .reset-visual-col {
        position: relative;
        background-color: #121212;
        background-image:
            url('{{ asset('images/auth/security-hero.jpg') }}');
        background-size: cover;
        background-position: center;
        border-right: 1px solid var(--border, #242424);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 36px 44px;
        box-sizing: border-box;
    }


    .visual-bg-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(12, 12, 12, 0.45) 0%,
                rgba(12, 12, 12, 0.25) 40%,
                rgba(12, 12, 12, 0.92) 100%
            );
        z-index: 1;
    }


    .visual-top-strip,
    .visual-bottom-card {
        position: relative;
        z-index: 2;
    }


    .visual-top-strip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: monospace;
        font-size: 10px;
        letter-spacing: 0.12em;
    }


    .sys-protocol-tag {
        color: #ffffff;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: #141414;
        border: 1px solid #222222;
        padding: 5px 10px;
    }


    .dot-red-sq {
        color: var(--accent, #d32f2f);
        font-size: 8px;
    }


    .token-status-tag {
        font-family: monospace;
        color: #888888;
    }


    .visual-bottom-card {
        background-color: rgba(16, 16, 16, 0.88);
        border: 1px solid var(--border, #242424);
        padding: 24px 28px;
        backdrop-filter: blur(6px);
        max-width: 520px;
    }


    .card-shield-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }


    .shield-glyph {
        font-size: 12px;
        color: #aaaaaa;
    }


    .shield-label {
        font-family: monospace;
        font-size: 9px;
        color: #cccccc;
        letter-spacing: 0.15em;
        font-weight: bold;
        text-transform: uppercase;
    }


    .card-quote {
        font-size: 13px;
        line-height: 1.5;
        color: #dddddd;
        margin: 0 0 16px 0;
        font-style: italic;
    }


    .card-meta-foot {
        display: flex;
        justify-content: space-between;
        font-family: monospace;
        font-size: 9px;
        color: #777777;
        border-top: 1px solid #222222;
        padding-top: 12px;
    }


    /* =========================================================
       COLONNE DROITE
    ========================================================== */

    .reset-form-col {
        background-color: #0c0c0c;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 36px;
        box-sizing: border-box;
    }


    .reset-form-inner {
        width: 100%;
        max-width: 440px;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .form-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }


    .auth-breadcrumbs {
        font-family: monospace;
        font-size: 10px;
        letter-spacing: 0.15em;
        color: #777777;
        display: flex;
        align-items: center;
        gap: 6px;
    }


    .auth-breadcrumbs a:hover {
        color: #ffffff;
    }


    .auth-breadcrumbs .sep {
        color: #444444;
    }


    .breadcrumb-active {
        color: var(--accent, #d32f2f);
        font-weight: bold;
    }


    .noad-monogram {
        display: flex;
        flex-direction: column;
        font-family: monospace;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 0.15em;
        line-height: 1;
        color: #666666;
        border: 1px solid var(--border, #242424);
        padding: 4px 6px;
    }


    /* =========================================================
       TITRE
    ========================================================== */

    .auth-title {
        font-size: clamp(34px, 4.5vw, 50px);
        font-weight: 900;
        letter-spacing: 0.04em;
        line-height: 0.95;
        margin: 0 0 12px 0;
        color: #ffffff;
        text-transform: uppercase;
    }


    .auth-subtitle {
        font-size: 13px;
        color: #888888;
        line-height: 1.45;
        margin: 0 0 28px 0;
    }


    /* =========================================================
       ALERTES
    ========================================================== */

    .auth-alert-errors {
        background-color: rgba(211, 47, 47, 0.1);
        border: 1px solid var(--accent, #d32f2f);
        color: #ffffff;
        padding: 12px 14px;
        margin-bottom: 20px;
        font-size: 12px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }


    .auth-alert-errors .alert-icon {
        color: var(--accent, #d32f2f);
        font-weight: bold;
    }


    /* =========================================================
       FORMULAIRE
    ========================================================== */

    .form-tactical-stack {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }


    .form-field-wrap {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }


    .field-label-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-family: monospace;
        font-size: 9px;
        letter-spacing: 0.12em;
    }


    .field-label-row label {
        color: #999999;
        font-weight: bold;
        text-transform: uppercase;
    }


    .label-req {
        color: #666666;
    }


    .input-with-icon {
        display: flex;
        align-items: center;
        background-color: #121212;
        border: 1px solid var(--border, #242424);
        padding: 0 14px;
        transition: border-color 0.2s;
    }


    .input-with-icon:focus-within {
        border-color: #666666;
    }


    .input-auth {
        width: 100%;
        background: transparent;
        border: none;
        outline: none;
        color: #ffffff;
        font-family: 'Barlow Condensed',
            -apple-system,
            sans-serif;
        font-size: 15px;
        padding: 12px 0;
        letter-spacing: 0.05em;
    }


    .input-auth.has-error {
        color: var(--accent, #d32f2f);
    }


    .input-icon {
        color: #555555;
        font-size: 13px;
        margin-left: 8px;
    }


    .btn-toggle-pwd {
        background: transparent;
        border: none;
        color: #555555;
        font-size: 13px;
        cursor: pointer;
        padding: 0;
        margin-left: 8px;
        transition: color 0.2s;
    }


    .btn-toggle-pwd:hover {
        color: #ffffff;
    }


    /* =========================================================
       JAUGE MOT DE PASSE
    ========================================================== */

    .password-meter-wrap {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: -6px;
        margin-bottom: 4px;
    }


    .meter-bar-track {
        width: 100%;
        height: 3px;
        background-color: #1a1a1a;
        overflow: hidden;
    }


    .meter-bar-fill {
        height: 100%;
        width: 0%;
        background-color: var(--accent, #d32f2f);
        transition:
            width 0.3s ease,
            background-color 0.3s ease;
    }


    .meter-requirements-row {
        display: flex;
        justify-content: space-between;
        font-family: monospace;
        font-size: 8px;
        color: #555555;
        letter-spacing: 0.08em;
    }


    .meter-requirements-row .req-item.valid {
        color: #2ecc71;
        font-weight: bold;
    }


    /* =========================================================
       BOUTON SUBMIT
    ========================================================== */

    .btn-submit-reset {
        width: 100%;
        background-color: #ffffff;
        color: #000000;
        border: 1px solid #ffffff;
        padding: 14px 20px;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s ease;
        margin-top: 6px;
    }


    .btn-submit-reset:hover {
        background-color: var(--accent, #d32f2f);
        border-color: var(--accent, #d32f2f);
        color: #ffffff;
    }


    .arrow-glyph {
        font-size: 14px;
    }


    /* =========================================================
       RETOUR CONNEXION
    ========================================================== */

    .reset-back-row {
        text-align: center;
        margin-top: 6px;
        margin-bottom: 24px;
    }


    .back-link {
        font-family: monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        color: #777777;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }


    .back-link:hover {
        color: #ffffff;
    }


    .arrow-back {
        font-size: 12px;
    }


    /* =========================================================
       FOOTER SÉCURITÉ
    ========================================================== */

    .reset-security-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #161616;
        padding-top: 14px;
        font-family: monospace;
        font-size: 9px;
        color: #666666;
        letter-spacing: 0.08em;
    }


    .security-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
    }


    .dot-green {
        color: #2ecc71;
        font-size: 8px;
    }


    .protocol-id {
        color: #555555;
    }

</style>

@endsection
```
