@extends('layouts.app')

@section('content')

<div class="checkout-confirmation-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <header class="checkout-header">

        <div class="checkout-header-top">

            <div class="checkout-status">
                <span>NOAD // CHECKOUT</span>
                <span class="checkout-status-divider">—</span>
                <span>COMMANDE EN COURS</span>
            </div>

            <a href="{{ route('cart.index') }}" class="checkout-back-top">
                ← RETOUR AU PANIER
            </a>

        </div>

        <h1 class="checkout-page-title">
            FINALISER LA COMMANDE
        </h1>

        <p class="checkout-page-subtitle">
            Vérifiez vos informations de livraison puis confirmez votre commande.
        </p>

    </header>


    {{-- =========================================================
         MESSAGES D'ERREUR
    ========================================================== --}}

    @if($errors->any())

        <div class="checkout-alert checkout-alert--error">

            <strong>Impossible de confirmer la commande.</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- =========================================================
         CONTENU PRINCIPAL
    ========================================================== --}}

    <div class="checkout-main-grid">


        {{-- =====================================================
             COLONNE GAUCHE : FORMULAIRE
        ====================================================== --}}

        <div class="checkout-form-column">

            <form
                action="{{ route('checkout.store') }}"
                method="POST"
                id="checkout-form"
            >

                @csrf

                {{-- Token anti double commande --}}
                <input
                    type="hidden"
                    name="checkout_token"
                    value="{{ old('checkout_token', (string) \Illuminate\Support\Str::uuid()) }}"
                >


                {{-- =================================================
                     01 — INFORMATIONS CLIENT
                ================================================== --}}

                <section class="checkout-block">

                    <div class="checkout-block-header">

                        <div>
                            <span class="checkout-block-number">01</span>

                            <h2>
                                INFORMATIONS CLIENT
                            </h2>
                        </div>

                        <span class="checkout-block-meta">
                            REQUIS
                        </span>

                    </div>


                    <div class="checkout-field">

                        <label for="full_name">
                            NOM COMPLET *
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', auth()->user()->name ?? '') }}"
                            required
                            autocomplete="name"
                            placeholder="Ex : Belhocine Mouaad"
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="phone">
                            NUMÉRO DE TÉLÉPHONE *
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            required
                            autocomplete="tel"
                            placeholder="0550 12 34 56"
                        >

                    </div>

                </section>


                {{-- =================================================
                     02 — LIVRAISON
                ================================================== --}}

                <section class="checkout-block">

                    <div class="checkout-block-header">

                        <div>
                            <span class="checkout-block-number">02</span>

                            <h2>
                                ADRESSE DE LIVRAISON
                            </h2>
                        </div>

                        <span class="checkout-block-meta">
                            ALGÉRIE
                        </span>

                    </div>


                    <div class="checkout-field">

                        <label for="address">
                            ADRESSE *
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address') }}"
                            required
                            autocomplete="street-address"
                            placeholder="Ex : Cité 2000 Logts, bâtiment 12"
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="wilaya">
                            WILAYA *
                        </label>

                        <input
                            type="text"
                            id="wilaya"
                            name="wilaya"
                            value="{{ old('wilaya') }}"
                            required
                            placeholder="Ex : Alger"
                        >

                    </div>

                </section>


                {{-- =================================================
                     03 — PAIEMENT
                ================================================== --}}

                <section class="checkout-block">

                    <div class="checkout-block-header">

                        <div>
                            <span class="checkout-block-number">03</span>

                            <h2>
                                MODE DE PAIEMENT
                            </h2>
                        </div>

                        <span class="checkout-block-meta">
                            SÉCURISÉ
                        </span>

                    </div>


                    <label class="checkout-payment-option">

                        <div class="checkout-payment-radio">

                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                required
                            >

                        </div>

                        <div class="checkout-payment-content">

                            <strong>
                                PAIEMENT À LA LIVRAISON
                            </strong>

                            <span>
                                Payez en espèces lors de la réception de votre commande.
                            </span>

                        </div>

                        <div class="checkout-payment-badge">
                            RECOMMANDÉ
                        </div>

                    </label>

                </section>


                {{-- =================================================
                     ACTIONS
                ================================================== --}}

                <div class="checkout-actions">

                    <a
                        href="{{ route('cart.index') }}"
                        class="checkout-back-button"
                    >
                        ← RETOUR AU PANIER
                    </a>

                    <button
                        type="submit"
                        class="checkout-submit-button"
                    >
                        <span>
                            CONFIRMER LA COMMANDE
                        </span>

                        <span>
                            →
                        </span>
                    </button>

                </div>

            </form>

        </div>


        {{-- =====================================================
             COLONNE DROITE : RÉCAPITULATIF
        ====================================================== --}}

        <aside class="checkout-summary-column">

            <div class="checkout-summary-card">

                <div class="checkout-summary-header">

                    <div>
                        <span class="checkout-summary-kicker">
                            NOAD // ORDER
                        </span>

                        <h2>
                            VOTRE COMMANDE
                        </h2>
                    </div>

                    <span class="checkout-summary-count">
                        {{ count($items) }}
                        {{ count($items) > 1 ? 'ARTICLES' : 'ARTICLE' }}
                    </span>

                </div>


                {{-- =================================================
                     ARTICLES
                ================================================== --}}

                <div class="checkout-summary-items">

                    @forelse($items as $item)

                        <div class="checkout-summary-item">

                            <div class="checkout-summary-item-info">

                                <h3>
                                    {{ $item['variant']->product->name }}
                                </h3>

                                <p>
                                    TAILLE :
                                    {{ $item['variant']->size }}
                                </p>

                                @if(!empty($item['variant']->color))
                                    <p>
                                        COLORIS :
                                        {{ $item['variant']->color }}
                                    </p>
                                @endif

                                <p>
                                    QUANTITÉ :
                                    {{ $item['quantity'] }}
                                </p>

                            </div>


                            <div class="checkout-summary-item-price">

                                {{ number_format($item['subtotal'], 0, ',', ' ') }}
                                DA

                            </div>

                        </div>

                    @empty

                        <div class="checkout-summary-empty">
                            Votre panier est vide.
                        </div>

                    @endforelse

                </div>


                {{-- =================================================
                     TOTAL
                ================================================== --}}

                <div class="checkout-summary-breakdown">

                    <div class="checkout-summary-row">

                        <span>
                            SOUS-TOTAL
                        </span>

                        <strong>
                            {{ number_format($total, 0, ',', ' ') }} DA
                        </strong>

                    </div>


                    <div class="checkout-summary-row">

                        <span>
                            LIVRAISON
                        </span>

                        <span>
                            CALCULÉE À LA COMMANDE
                        </span>

                    </div>

                </div>


                <div class="checkout-summary-total">

                    <span>
                        TOTAL
                    </span>

                    <strong>
                        {{ number_format($total, 0, ',', ' ') }} DA
                    </strong>

                </div>


                {{-- =================================================
                     RÉASSURANCE
                ================================================== --}}

                <div class="checkout-security">

                    <span class="checkout-security-icon">
                        ✓
                    </span>

                    <div>

                        <strong>
                            PAIEMENT À LA RÉCEPTION
                        </strong>

                        <p>
                            Aucune avance nécessaire pour le paiement à la livraison.
                        </p>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>


{{-- =============================================================
     STYLE LOCAL DU CHECKOUT
============================================================= --}}

<style>

.checkout-confirmation-page {
    width: min(1400px, calc(100% - 60px));
    margin: 0 auto;
    padding: 70px 0 100px;
}

.checkout-header {
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 45px;
    margin-bottom: 40px;
}

.checkout-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.checkout-status {
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: monospace;
    font-size: 11px;
    letter-spacing: .12em;
    color: #777;
}

.checkout-status-divider {
    color: var(--accent, #b02e26);
}

.checkout-back-top {
    color: #777;
    text-decoration: none;
    font-family: monospace;
    font-size: 11px;
    letter-spacing: .1em;
    transition: color .2s ease;
}

.checkout-back-top:hover {
    color: var(--text, #fff);
}

.checkout-page-title {
    margin: 0;
    font-size: clamp(48px, 7vw, 100px);
    line-height: .9;
    font-weight: 900;
    letter-spacing: -.04em;
    text-transform: uppercase;
}

.checkout-page-subtitle {
    margin: 25px 0 0;
    max-width: 650px;
    color: #777;
    font-size: 14px;
    line-height: 1.7;
}

.checkout-alert {
    border: 1px solid;
    padding: 18px 22px;
    margin-bottom: 30px;
    font-size: 14px;
}

.checkout-alert--error {
    border-color: #b02e26;
    color: #fff;
    background: rgba(176, 46, 38, .08);
}

.checkout-alert ul {
    margin: 10px 0 0 20px;
    padding: 0;
}

.checkout-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(360px, .8fr);
    gap: 45px;
    align-items: start;
}

.checkout-form-column {
    min-width: 0;
}

.checkout-block {
    border: 1px solid var(--border, #242424);
    background: #0d0d0d;
    padding: 30px;
    margin-bottom: 22px;
}

.checkout-block-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 18px;
    margin-bottom: 28px;
}

.checkout-block-header > div {
    display: flex;
    align-items: center;
    gap: 14px;
}

.checkout-block-number {
    color: var(--accent, #b02e26);
    font-family: monospace;
    font-size: 13px;
    font-weight: 700;
}

.checkout-block h2 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: .1em;
}

.checkout-block-meta {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;
    color: #666;
}

.checkout-field {
    display: flex;
    flex-direction: column;
    gap: 9px;
    margin-bottom: 20px;
}

.checkout-field:last-child {
    margin-bottom: 0;
}

.checkout-field label {
    color: #888;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
}

.checkout-field input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--border, #242424);
    background: #080808;
    color: #fff;
    padding: 16px;
    outline: none;
    font-size: 14px;
    transition: border-color .2s ease;
}

.checkout-field input:focus {
    border-color: #777;
}

.checkout-field input::placeholder {
    color: #444;
}

.checkout-payment-option {
    display: flex;
    align-items: center;
    gap: 18px;
    border: 1px solid var(--border, #242424);
    padding: 22px;
    cursor: pointer;
    transition: border-color .2s ease, background .2s ease;
}

.checkout-payment-option:hover {
    border-color: #555;
    background: #111;
}

.checkout-payment-radio input {
    width: 18px;
    height: 18px;
    accent-color: var(--accent, #b02e26);
}

.checkout-payment-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.checkout-payment-content strong {
    font-size: 13px;
    letter-spacing: .06em;
}

.checkout-payment-content span {
    color: #777;
    font-size: 12px;
    line-height: 1.5;
}

.checkout-payment-badge {
    font-family: monospace;
    font-size: 9px;
    color: var(--accent, #b02e26);
    letter-spacing: .1em;
}

.checkout-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-top: 30px;
}

.checkout-back-button {
    color: #777;
    text-decoration: none;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .1em;
}

.checkout-back-button:hover {
    color: #fff;
}

.checkout-submit-button {
    flex: 1;
    max-width: 420px;
    border: 0;
    background: #fff;
    color: #080808;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .08em;
    transition: transform .2s ease, background .2s ease;
}

.checkout-submit-button:hover {
    transform: translateY(-2px);
    background: #e8e8e8;
}

.checkout-summary-column {
    position: sticky;
    top: 30px;
}

.checkout-summary-card {
    border: 1px solid var(--border, #242424);
    background: #0d0d0d;
}

.checkout-summary-header {
    padding: 25px;
    border-bottom: 1px solid var(--border, #242424);
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.checkout-summary-kicker {
    display: block;
    color: #777;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;
    margin-bottom: 8px;
}

.checkout-summary-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
}

.checkout-summary-count {
    color: #777;
    font-family: monospace;
    font-size: 10px;
    white-space: nowrap;
}

.checkout-summary-items {
    padding: 0 25px;
}

.checkout-summary-item {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 24px 0;
    border-bottom: 1px solid var(--border, #242424);
}

.checkout-summary-item-info {
    min-width: 0;
}

.checkout-summary-item-info h3 {
    margin: 0 0 10px;
    font-size: 16px;
    font-weight: 800;
    text-transform: uppercase;
}

.checkout-summary-item-info p {
    margin: 4px 0;
    color: #777;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .08em;
}

.checkout-summary-item-price {
    white-space: nowrap;
    font-family: monospace;
    font-size: 12px;
    font-weight: 700;
}

.checkout-summary-empty {
    padding: 30px 0;
    color: #777;
    font-size: 13px;
}

.checkout-summary-breakdown {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border, #242424);
}

.checkout-summary-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 9px 0;
    color: #777;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .08em;
}

.checkout-summary-row strong {
    color: #fff;
}

.checkout-summary-total {
    padding: 28px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.checkout-summary-total span {
    font-family: monospace;
    color: #777;
    font-size: 11px;
    letter-spacing: .1em;
}

.checkout-summary-total strong {
    font-size: 24px;
    font-weight: 900;
}

.checkout-security {
    margin: 0 25px 25px;
    border: 1px solid var(--border, #242424);
    padding: 18px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.checkout-security-icon {
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #555;
    border-radius: 50%;
    font-size: 12px;
}

.checkout-security strong {
    display: block;
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .08em;
    margin-bottom: 5px;
}

.checkout-security p {
    margin: 0;
    color: #666;
    font-size: 10px;
    line-height: 1.5;
}

@media (max-width: 900px) {

    .checkout-confirmation-page {
        width: min(100% - 30px, 700px);
        padding-top: 40px;
    }

    .checkout-main-grid {
        grid-template-columns: 1fr;
    }

    .checkout-summary-column {
        position: static;
    }

}

@media (max-width: 600px) {

    .checkout-header-top {
        align-items: flex-start;
        flex-direction: column;
        gap: 20px;
    }

    .checkout-block {
        padding: 20px;
    }

    .checkout-block-header {
        align-items: flex-start;
    }

    .checkout-block-meta {
        display: none;
    }

    .checkout-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .checkout-submit-button {
        max-width: none;
        width: 100%;
    }

    .checkout-back-button {
        text-align: center;
    }

    .checkout-payment-option {
        align-items: flex-start;
    }

    .checkout-payment-badge {
        display: none;
    }

}

</style>

@endsection