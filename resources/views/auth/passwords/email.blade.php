@extends('layouts.app')

@section('content')

<div class="noad-forgot-password-page">

    <div class="auth-split-layout">

        {{-- =========================================================
             COLONNE GAUCHE : VISUEL ÉDITORIAL & PROTOCOLE
        ========================================================== --}}

        <div class="editorial-col">

            <div class="editorial-overlay"></div>

            {{-- Header métadonnées de sécurité --}}
            <div class="editorial-top-strip">

                <span class="security-protocol-tag">
                    <span class="dot-red-sq">■</span>
                    NOAD SECURITY PROTOCOL // 2026
                </span>

                <span class="sys-id-tag">
                    SYS.ID: 884-REC
                </span>

            </div>

            {{-- Footer manifeste et division tactique --}}
            <div class="editorial-bottom-wrap">

                <div class="manifesto-line"></div>

                <span class="manifesto-subtag">
                    WHITELIST PROTOCOL
                </span>

                <blockquote class="manifesto-quote">
                    « LA DISCIPLINE ET LA CONFIDENTIALITÉ D'ABORD.
                    RÉCUPÉREZ VOTRE ACCÈS WHITELIST SÉCURISÉ. »
                </blockquote>

                <div class="division-meta-row">

                    <span>
                        DIVISION AUTHENTIFICATION
                    </span>

                    <span class="meta-sep">
                        •
                    </span>

                    <span>
                        ACCÈS RÉSILIATION V.04
                    </span>

                </div>

            </div>

        </div>


        {{-- =========================================================
             COLONNE DROITE : FORMULAIRE DE RÉCUPÉRATION
        ========================================================== --}}

        <div class="form-col">

            <div class="form-col-inner">

                {{-- Fil d'Ariane & Monogramme NOAD --}}
                <div class="form-top-row">

                    <div class="auth-breadcrumbs">

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

                        <span class="current">
                            SÉCURITÉ
                        </span>

                    </div>

                    <div class="noad-monogram">
                        <span>NO</span>
                        <span>AD</span>
                    </div>

                </div>


                {{-- Titre Brutaliste et Explication --}}
                <h1 class="auth-headline">
                    RÉINITIALISATION DU MOT DE PASSE
                </h1>

                <p class="auth-lead">
                    Entrez l'adresse e-mail associée à votre compte NOAD.
                    Nous vous enverrons un code de réinitialisation sécurisé
                    à usage unique pour restaurer vos accès aux drops et à la whitelist.
                </p>


                {{-- =====================================================
                     ALERTES
                ====================================================== --}}

                {{-- Statut d'envoi --}}
                @if (session('status'))

                    <div class="status-alert-box">

                        <span class="alert-glyph">
                            ✓
                        </span>

                        <span>
                            {{ session('status') }}
                        </span>

                    </div>

                @endif


                {{-- Erreurs de validation --}}
                @if ($errors->any())

                    <div class="error-alert-box">

                        <span class="alert-glyph">
                            ⚠
                        </span>

                        <div class="error-lines">

                            @foreach ($errors->all() as $error)

                                <div>
                                    {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif


                {{-- =====================================================
                     FORMULAIRE DE RÉCUPÉRATION
                ====================================================== --}}

                <form
                    action="{{ route('password.email') }}"
                    method="POST"
                    class="tactical-form"
                >

                    @csrf


                    {{-- Champ Adresse E-mail --}}
                    <div class="field-container">

                        <div class="field-header">

                            <label for="email">
                                ADRESSE E-MAIL
                            </label>

                            <span class="req-label">
                                REQUIS
                            </span>

                        </div>

                        <div class="input-wrap">

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="nom@exemple.dz"
                                class="input-text @error('email') has-error @enderror"
                            >

                            <span class="input-icon">
                                ✉
                            </span>

                        </div>

                        <span class="field-helper">
                            Assurez-vous de vérifier vos courriers indésirables
                            ou notifications d'accès.
                        </span>

                    </div>


                    {{-- Bouton Submit --}}
                    <button
                        type="submit"
                        class="btn-submit-action"
                    >
                        ENVOYER LE LIEN DE RÉCUPÉRATION

                        <span class="arrow-glyph">
                            →
                        </span>
                    </button>


                    {{-- Liens de navigation & assistance --}}
                    <div class="auth-links-row">

                        <a
                            href="{{ route('login') }}"
                            class="back-link"
                        >
                            <span class="arrow-back">
                                ←
                            </span>

                            RETOURNER À LA CONNEXION
                        </a>

                        <span class="assistance-link">
                            ASSISTANCE PROTOCOLE
                        </span>

                    </div>

                </form>


                {{-- =====================================================
                     WIDGET MÉTRIQUES DE VALIDITÉ
                ====================================================== --}}

                <div class="security-metrics-card">

                    <div class="metric-block">

                        <span class="metric-label">
                            DÉLAI DE VALIDITÉ
                        </span>

                        <span class="metric-value">
                            15 MINUTES
                        </span>

                    </div>

                    <div class="metric-separator"></div>

                    <div class="metric-block">

                        <span class="metric-label">
                            TENTATIVES MAX
                        </span>

                        <span class="metric-value">
                            03 / 24 HEURES
                        </span>

                    </div>

                </div>


                {{-- =====================================================
                     FOOTER SÉCURITÉ
                ====================================================== --}}

                <div class="security-footer-row">

                    <div class="security-lock-info">

                        <span class="lock-icon">
                            🔒
                        </span>

                        <span>
                            CHIFFREMENT SSL 256 BITS • PROTOCOLE NOAD AUTH •
                            ALGER // LONDRES
                        </span>

                    </div>

                    <span class="sec-id-code">
                        SEC-ID: 0092-2026
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/* =========================================================
   NOAD FORGOT PASSWORD
   Scoped Stylesheet
========================================================= */

.noad-forgot-password-page {
    width: 100%;
    min-height: calc(100vh - 80px);
    background-color: #0c0c0c;
    color: var(--text, #e5e5e5);
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    display: flex;
}

.noad-forgot-password-page a {
    color: inherit;
    text-decoration: none;
}


/* =========================================================
   01. LAYOUT SPLIT
========================================================= */

.auth-split-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    min-height: 100%;
}


/* =========================================================
   02. RESPONSIVE LAYOUT
========================================================= */

@media (max-width: 960px) {

    .auth-split-layout {
        grid-template-columns: 1fr;
    }

    .editorial-col {
        display: none !important;
    }

}


/* =========================================================
   03. COLONNE GAUCHE — ÉDITORIAL
========================================================= */

.editorial-col {
    position: relative;
    background-color: #121212;
    background-image: url('{{ asset('images/auth/security-hero.jpg') }}');
    background-size: cover;
    background-position: center;
    border-right: 1px solid var(--border, #242424);

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    padding: 36px 44px;
    box-sizing: border-box;
}


/* =========================================================
   04. OVERLAY ÉDITORIAL
========================================================= */

.editorial-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;

    background:
        linear-gradient(
            180deg,
            rgba(12, 12, 12, 0.45) 0%,
            rgba(12, 12, 12, 0.3) 40%,
            rgba(12, 12, 12, 0.95) 100%
        );

    z-index: 1;
}

.editorial-top-strip,
.editorial-bottom-wrap {
    position: relative;
    z-index: 2;
}


/* =========================================================
   05. TOP STRIP
========================================================= */

.editorial-top-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.12em;
}

.security-protocol-tag {
    background-color: #141414;
    border: 1px solid #222222;

    padding: 6px 12px;

    color: #ffffff;
    font-weight: bold;

    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.dot-red-sq {
    color: var(--accent, #d32f2f);
    font-size: 8px;
}

.sys-id-tag {
    font-family: monospace;
    color: #888888;
}


/* =========================================================
   06. MANIFESTE ÉDITORIAL
========================================================= */

.editorial-bottom-wrap {
    max-width: 520px;
}

.manifesto-line {
    width: 32px;
    height: 2px;

    background-color: var(--accent, #d32f2f);

    margin-bottom: 12px;
}

.manifesto-subtag {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.15em;

    color: #aaaaaa;

    text-transform: uppercase;

    display: block;

    margin-bottom: 8px;
}

.manifesto-quote {
    font-size: 20px;
    font-weight: 900;

    letter-spacing: 0.04em;
    line-height: 1.25;

    color: #ffffff;

    margin: 0 0 16px 0;

    text-transform: uppercase;
}

.division-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.12em;

    color: #777777;
}

.meta-sep {
    color: #444444;
}


/* =========================================================
   07. COLONNE DROITE — FORMULAIRE
========================================================= */

.form-col {
    background-color: #0c0c0c;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 56px 48px;

    box-sizing: border-box;
}

.form-col-inner {
    width: 100%;
    max-width: 480px;
}


/* =========================================================
   08. HEADER FORMULAIRE
========================================================= */

.form-top-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;

    margin-bottom: 24px;
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

.auth-breadcrumbs .current {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}

.noad-monogram {
    display: flex;
    flex-direction: column;

    font-family: monospace;
    font-size: 10px;
    font-weight: 900;

    letter-spacing: 0.15em;
    line-height: 1;

    color: #666666;

    border: 1px solid var(--border, #242424);

    padding: 4px 6px;
}


/* =========================================================
   09. TITRE & DESCRIPTION
========================================================= */

.auth-headline {
    font-size: clamp(34px, 4.2vw, 48px);
    font-weight: 900;

    letter-spacing: 0.04em;
    line-height: 1;

    margin: 0 0 16px 0;

    color: #ffffff;

    text-transform: uppercase;
}

.auth-lead {
    font-size: 13px;
    color: #888888;

    line-height: 1.5;

    margin: 0 0 32px 0;
}


/* =========================================================
   10. ALERTES
========================================================= */

.status-alert-box {
    background-color: rgba(46, 204, 113, 0.08);

    border: 1px solid #2ecc71;

    color: #2ecc71;

    padding: 12px 16px;

    margin-bottom: 24px;

    font-size: 12px;

    display: flex;
    align-items: center;
    gap: 10px;
}

.error-alert-box {
    background-color: rgba(211, 47, 47, 0.08);

    border: 1px solid var(--accent, #d32f2f);

    color: #ffffff;

    padding: 12px 16px;

    margin-bottom: 24px;

    font-size: 12px;

    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.alert-glyph {
    font-weight: bold;
}

.error-lines {
    display: flex;
    flex-direction: column;
    gap: 4px;
}


/* =========================================================
   11. FORMULAIRE
========================================================= */

.tactical-form {
    display: flex;
    flex-direction: column;
    gap: 22px;

    margin-bottom: 36px;
}


/* =========================================================
   12. CHAMP
========================================================= */

.field-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.12em;
}

.field-header label {
    color: #999999;
    font-weight: bold;
    text-transform: uppercase;
}

.req-label {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}


/* =========================================================
   13. INPUT
========================================================= */

.input-wrap {
    display: flex;
    align-items: center;

    background-color: #121212;

    border: 1px solid var(--border, #242424);

    padding: 0 14px;

    transition: border-color 0.2s;
}

.input-wrap:focus-within {
    border-color: #666666;
}

.input-text {
    width: 100%;

    background: transparent;
    border: none;
    outline: none;

    color: #ffffff;

    font-family: monospace;
    font-size: 13px;

    padding: 14px 0;

    letter-spacing: 0.05em;
}

.input-text.has-error {
    color: var(--accent, #d32f2f);
}

.input-icon {
    color: #555555;
    font-size: 13px;

    margin-left: 8px;
}

.field-helper {
    font-size: 11px;
    color: #666666;

    margin-top: 2px;
}


/* =========================================================
   14. BOUTON ACTION
========================================================= */

.btn-submit-action {
    width: 100%;

    background-color: #e5e5e5;
    color: #000000;

    border: 1px solid #e5e5e5;

    padding: 15px 20px;

    font-size: 12px;
    font-weight: 900;

    letter-spacing: 0.12em;

    text-transform: uppercase;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 10px;

    transition: all 0.2s ease;
}

.btn-submit-action:hover {
    background-color: var(--accent, #d32f2f);

    border-color: var(--accent, #d32f2f);

    color: #ffffff;
}

.arrow-glyph {
    font-size: 14px;
}


/* =========================================================
   15. LIENS
========================================================= */

.auth-links-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: 0.1em;

    color: #888888;

    margin-top: 4px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    color: #aaaaaa;

    transition: color 0.2s;
}

.back-link:hover {
    color: #ffffff;
}

.arrow-back {
    font-size: 12px;
}

.assistance-link {
    color: #666666;
}


/* =========================================================
   16. MÉTRIQUES DE SÉCURITÉ
========================================================= */

.security-metrics-card {
    background-color: #101010;

    border: 1px solid var(--border, #242424);

    display: flex;
    align-items: center;

    padding: 16px 20px;

    margin-bottom: 36px;
}

.metric-block {
    flex: 1;

    display: flex;
    flex-direction: column;

    gap: 4px;
}

.metric-label {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: 0.12em;

    color: #777777;
}

.metric-value {
    font-family: monospace;
    font-size: 15px;
    font-weight: 900;

    letter-spacing: 0.08em;

    color: #ffffff;
}

.metric-separator {
    width: 1px;
    height: 32px;

    background-color: var(--border, #242424);

    margin: 0 18px;
}


/* =========================================================
   17. FOOTER SÉCURITÉ
========================================================= */

.security-footer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    border-top: 1px solid #161616;

    padding-top: 16px;

    font-family: monospace;
    font-size: 9px;

    color: #666666;

    letter-spacing: 0.08em;

    flex-wrap: wrap;
    gap: 10px;
}

.security-lock-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lock-icon {
    color: var(--accent, #d32f2f);
    font-size: 10px;
}

.sec-id-code {
    color: #555555;
}

</style>

@endsection