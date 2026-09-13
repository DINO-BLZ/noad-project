@extends('layouts.app')

@section('content')

<div class="noad-auth-page">

    {{-- =========================================================
         AUTHENTICATION — SPLIT SCREEN
    ========================================================== --}}

    <div class="auth-split-container">

        {{-- =====================================================
             01. COLONNE GAUCHE — VISUEL ÉDITORIAL
        ====================================================== --}}

        <div class="auth-visual-col">

            <div class="visual-bg-overlay"></div>

            {{-- Badge technique supérieur --}}
            <div class="visual-top-strip">

                <span class="sys-auth-tag">
                    <span class="dot-red-sq">■</span>
                    SYS.AUTH // NOAD-09
                </span>

                <span class="archive-tag">
                    TERRACE APPAREL ARCHIVE
                </span>

            </div>

            {{-- Encadré manifeste whitelist --}}
            <div class="visual-bottom-card">

                <div class="card-shield-title">

                    <span class="shield-glyph">
                        🛡
                    </span>

                    <span class="shield-label">
                        ACCÈS RÉSERVÉ WHITELIST
                    </span>

                </div>

                <blockquote class="card-quote">
                    « La discipline précède la suprématie.
                    Votre accès membre garantit l'entrée prioritaire
                    aux drops limités et l'historique complet de vos
                    pièces numérotées. »
                </blockquote>

                <div class="card-meta-foot">

                    <span class="meta-spec">
                        NOAD SPEC. 2026
                    </span>

                    <span class="meta-devise">
                        DEV // DEVISE : DZD (DA)
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
             02. COLONNE DROITE — FORMULAIRE DE CONNEXION
        ====================================================== --}}

        <div class="auth-form-col">

            <div class="auth-form-inner">

                {{-- =================================================
                     FIL D'ARIANE
                ================================================== --}}

                <div class="auth-breadcrumb">

                    <a href="{{ route('home') }}">
                        NOAD
                    </a>

                    <span class="sep">
                        /
                    </span>

                    <span>
                        COMPTE CLIENT
                    </span>

                    <span class="sep">
                        /
                    </span>

                    <span class="breadcrumb-active">
                        SESSION
                    </span>

                </div>


                {{-- =================================================
                     TITRE
                ================================================== --}}

                <h1 class="auth-title">
                    CONNEXION
                </h1>

                <p class="auth-subtitle">
                    Accédez à votre compte pour suivre vos commandes
                    et gérer vos accès whitelist.
                </p>


                {{-- =================================================
                     ERREURS DE VALIDATION
                ================================================== --}}

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


                {{-- Message de statut --}}
                @if (session('status'))

                    <div class="auth-alert-success">
                        {{ session('status') }}
                    </div>

                @endif


                {{-- =================================================
                     FORMULAIRE
                ================================================== --}}

                <form
                    action="{{ route('login') }}"
                    method="POST"
                    class="form-tactical-stack"
                >

                    @csrf


                    {{-- =================================================
                         CHAMP 01 — EMAIL
                    ================================================== --}}

                    <div class="form-field-wrap">

                        <div class="field-label-row">

                            <label for="email">
                                ADRESSE E-MAIL
                            </label>

                            <span class="label-req">
                                REQUIS
                            </span>

                        </div>

                        <div class="input-with-icon">

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
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
                         CHAMP 02 — MOT DE PASSE
                    ================================================== --}}

                    <div class="form-field-wrap">

                        <div class="field-label-row">

                            <label for="password">
                                MOT DE PASSE
                            </label>

                            @if (Route::has('password.request'))

                                <a
                                    href="{{ route('password.request') }}"
                                    class="link-forgot-pwd"
                                >
                                    MOT DE PASSE OUBLIÉ ?
                                </a>

                            @endif

                        </div>

                        <div class="input-with-icon">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="input-auth @error('password') has-error @enderror"
                            >

                            <button
                                type="button"
                                class="btn-toggle-pwd"
                                onclick="togglePasswordVisibility()"
                                title="Afficher/masquer le mot de passe"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    {{-- =================================================
                         OPTIONS
                    ================================================== --}}

                    <div class="form-options-row">

                        <label class="checkbox-container">

                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <span class="checkmark-box"></span>

                            <span class="checkbox-label">
                                MÉMORISER CET APPAREIL
                            </span>

                        </label>

                        <span class="location-tag">
                            DZD • ALGER
                        </span>

                    </div>


                    {{-- =================================================
                         BOUTON CONNEXION
                    ================================================== --}}

                    <button
                        type="submit"
                        class="btn-submit-login"
                    >
                        SE CONNECTER

                        <span class="arrow-glyph">
                            →
                        </span>
                    </button>

                </form>


                {{-- =================================================
                     SÉPARATEUR
                ================================================== --}}

                <div class="auth-divider-wrap">

                    <span class="divider-line"></span>

                    <span class="divider-text">
                        OU
                    </span>

                    <span class="divider-line"></span>

                </div>


                {{-- =================================================
                     INSCRIPTION
                ================================================== --}}

                <div class="register-prompt-row">

                    <span>
                        Pas encore de compte ?
                    </span>

                    <a
                        href="{{ route('register') }}"
                        class="link-create-account"
                    >
                        CRÉER UN COMPTE
                    </a>

                </div>


                {{-- =================================================
                     DROP RÉSERVÉ
                ================================================== --}}

                <div class="drop-teaser-card">

                    <div class="teaser-icon">
                        ⚡
                    </div>

                    <div class="teaser-body">

                        <span class="teaser-title">
                            DROP 01 // THE RESISTANCE
                        </span>

                        <span class="teaser-meta">
                            Accès réservé • Quantités numérotées
                        </span>

                    </div>

                </div>


                {{-- =================================================
                     SÉCURITÉ
                ================================================== --}}

                <div class="auth-security-footer">

                    <span class="security-indicator">

                        <span class="dot-green">
                            ●
                        </span>

                        CONNEXION SÉCURISÉE •
                        CHIFFREMENT 256 BITS

                    </span>

                    <span class="protocol-id">
                        NOAD PROTOCOL
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}

<script>

    function togglePasswordVisibility() {

        const pwd = document.getElementById('password');

        if (pwd.type === 'password') {

            pwd.type = 'text';

        } else {

            pwd.type = 'password';

        }
    }

</script>


{{-- =============================================================
     STYLES
============================================================= --}}

<style>

/* =============================================================
   01. BASE
============================================================= */

.noad-auth-page {
    width: 100%;
    min-height: calc(100vh - 80px);

    background-color: #0c0c0c;
    color: var(--text, #e5e5e5);

    font-family:
        'Barlow Condensed',
        -apple-system,
        BlinkMacSystemFont,
        sans-serif;

    display: flex;
}

.noad-auth-page a {
    color: inherit;
    text-decoration: none;
}


/* =============================================================
   02. LAYOUT — SPLIT SCREEN
============================================================= */

.auth-split-container {
    display: grid;
    grid-template-columns: 1fr 1fr;

    width: 100%;
    min-height: 100%;
}


/* =============================================================
   03. COLONNE GAUCHE — VISUEL
============================================================= */

.auth-visual-col {
    position: relative;

    background-color: #121212;

    background-image:
        url('{{ asset('images/auth/login-hero.jpg') }}');

    background-size: cover;
    background-position: center center;

    border-right:
        1px solid var(--border, #242424);

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    padding: 36px 44px;

    box-sizing: border-box;
}


/* =============================================================
   04. OVERLAY VISUEL
============================================================= */

.visual-bg-overlay {
    position: absolute;

    top: 0;
    left: 0;
    right: 0;
    bottom: 0;

    background:
        linear-gradient(
            180deg,
            rgba(12, 12, 12, 0.45) 0%,
            rgba(12, 12, 12, 0.25) 40%,
            rgba(12, 12, 12, 0.9) 100%
        );

    z-index: 1;
}

.visual-top-strip,
.visual-bottom-card {
    position: relative;
    z-index: 2;
}


/* =============================================================
   05. BARRE TECHNIQUE
============================================================= */

.visual-top-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.12em;
}

.sys-auth-tag {
    color: #ffffff;
    font-weight: bold;

    display: flex;
    align-items: center;

    gap: 6px;
}

.dot-red-sq {
    color: var(--accent, #d32f2f);
    font-size: 8px;
}

.archive-tag {
    color: #888888;
}


/* =============================================================
   06. CARTE MANIFESTE
============================================================= */

.visual-bottom-card {
    background-color: rgba(16, 16, 16, 0.88);

    border:
        1px solid var(--border, #242424);

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

    margin: 0 0 16px;

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


/* =============================================================
   07. COLONNE DROITE — FORMULAIRE
============================================================= */

.auth-form-col {
    background-color: #0c0c0c;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 48px 36px;

    box-sizing: border-box;
}

.auth-form-inner {
    width: 100%;
    max-width: 440px;
}


/* =============================================================
   08. FIL D'ARIANE
============================================================= */

.auth-breadcrumb {
    font-family: monospace;

    font-size: 10px;

    letter-spacing: 0.15em;

    color: #777777;

    margin-bottom: 16px;

    display: flex;
    align-items: center;

    gap: 6px;
}

.auth-breadcrumb a:hover {
    color: #ffffff;
}

.auth-breadcrumb .sep {
    color: #444444;
}

.breadcrumb-active {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}


/* =============================================================
   09. TITRE
============================================================= */

.auth-title {
    font-size: clamp(38px, 4.8vw, 56px);

    font-weight: 900;

    letter-spacing: 0.04em;

    line-height: 0.95;

    margin: 0 0 12px;

    color: #ffffff;

    text-transform: uppercase;
}

.auth-subtitle {
    font-size: 13px;

    color: #888888;

    line-height: 1.45;

    margin: 0 0 28px;
}


/* =============================================================
   10. ALERTES
============================================================= */

.auth-alert-errors {
    background-color:
        rgba(211, 47, 47, 0.1);

    border:
        1px solid var(--accent, #d32f2f);

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

.auth-alert-success {
    background-color:
        rgba(46, 204, 113, 0.1);

    border:
        1px solid #2ecc71;

    color: #2ecc71;

    padding: 12px 14px;

    margin-bottom: 20px;

    font-size: 12px;
}


/* =============================================================
   11. FORMULAIRE
============================================================= */

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


/* =============================================================
   12. MOT DE PASSE OUBLIÉ
============================================================= */

.link-forgot-pwd {
    color: #888888;

    transition: color 0.2s;
}

.link-forgot-pwd:hover {
    color: #ffffff;
}


/* =============================================================
   13. INPUTS
============================================================= */

.input-with-icon {
    display: flex;
    align-items: center;

    background-color: #121212;

    border:
        1px solid var(--border, #242424);

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

    font-family:
        'Barlow Condensed',
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


/* =============================================================
   14. TOGGLE PASSWORD
============================================================= */

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


/* =============================================================
   15. OPTIONS & CHECKBOX
============================================================= */

.form-options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin: 4px 0 6px;

    font-family: monospace;

    font-size: 9px;

    letter-spacing: 0.1em;
}

.checkbox-container {
    display: flex;
    align-items: center;

    gap: 8px;

    cursor: pointer;

    user-select: none;

    color: #888888;
}

.checkbox-container input {
    display: none;
}

.checkmark-box {
    width: 14px;
    height: 14px;

    background-color: #121212;

    border:
        1px solid var(--border, #242424);

    display: inline-block;

    position: relative;
}

.checkbox-container input:checked ~ .checkmark-box {
    background-color: var(--accent, #d32f2f);

    border-color:
        var(--accent, #d32f2f);
}

.checkbox-container input:checked ~ .checkmark-box::after {
    content: "✓";

    position: absolute;

    color: #ffffff;

    font-size: 10px;

    top: -1px;
    left: 2px;
}

.location-tag {
    color: #666666;
}


/* =============================================================
   16. BOUTON CONNEXION
============================================================= */

.btn-submit-login {
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
}

.btn-submit-login:hover {
    background-color: var(--accent, #d32f2f);

    border-color: var(--accent, #d32f2f);

    color: #ffffff;
}

.arrow-glyph {
    font-size: 14px;
}


/* =============================================================
   17. SÉPARATEUR "OU"
============================================================= */

.auth-divider-wrap {
    display: flex;
    align-items: center;

    margin: 22px 0 18px;

    gap: 12px;
}

.divider-line {
    flex: 1;

    height: 1px;

    background-color:
        var(--border, #242424);
}

.divider-text {
    font-family: monospace;

    font-size: 9px;

    color: #555555;

    letter-spacing: 0.15em;
}


/* =============================================================
   18. CRÉATION DE COMPTE
============================================================= */

.register-prompt-row {
    text-align: center;

    font-size: 12px;

    color: #888888;

    margin-bottom: 24px;
}

.link-create-account {
    color: var(--accent, #d32f2f);

    font-weight: 800;

    margin-left: 6px;

    letter-spacing: 0.06em;

    transition: color 0.2s;
}

.link-create-account:hover {
    color: #ffffff;
}


/* =============================================================
   19. DROP TEASER
============================================================= */

.drop-teaser-card {
    background-color: #101010;

    border:
        1px solid var(--border, #242424);

    padding: 12px 14px;

    display: flex;
    align-items: center;

    gap: 12px;

    margin-bottom: 24px;
}

.teaser-icon {
    width: 28px;
    height: 28px;

    background-color: #161616;

    border:
        1px solid #222222;

    display: flex;
    align-items: center;
    justify-content: center;

    color: var(--accent, #d32f2f);

    font-size: 12px;

    flex-shrink: 0;
}

.teaser-body {
    display: flex;
    flex-direction: column;

    gap: 2px;
}

.teaser-title {
    font-family: monospace;

    font-size: 9px;

    font-weight: bold;

    color: #ffffff;

    letter-spacing: 0.08em;
}

.teaser-meta {
    font-family: monospace;

    font-size: 9px;

    color: #777777;
}


/* =============================================================
   20. FOOTER SÉCURITÉ
============================================================= */

.auth-security-footer {
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


/* =============================================================
   21. RESPONSIVE
============================================================= */

@media (max-width: 960px) {

    .auth-split-container {
        grid-template-columns: 1fr;
    }

    .auth-visual-col {
        display: none !important;
    }

}


@media (max-width: 560px) {

    .auth-form-col {
        padding: 36px 20px;
    }

    .auth-title {
        font-size: 42px;
    }

    .form-options-row {
        align-items: flex-start;

        gap: 12px;
    }

    .location-tag {
        white-space: nowrap;
    }

    .auth-security-footer {
        flex-direction: column;

        align-items: flex-start;

        gap: 8px;
    }

}

</style>

@endsection