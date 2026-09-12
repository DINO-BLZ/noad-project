@extends('layouts.app')

@section('content')

<div class="cart">

    <div class="cart__header">
        <div>
            <p class="cart__eyebrow">NOAD // CART</p>
            <h1 class="cart__title">PANIER</h1>
        </div>

        <a href="{{ route('shop.index') }}" class="cart__continue">
            ← CONTINUER LA BOUTIQUE
        </a>
    </div>


    @if(session('success'))
        <div class="cart__notice">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="cart__error">
            {{ session('error') }}
        </div>
    @endif


    @if(count($items) > 0)

        <div class="cart__layout">

            {{-- =====================================================
                 CART ITEMS
            ====================================================== --}}

            <div class="cart__items">

                <div class="cart__items-header">
                    <span>ARTICLE</span>
                    <span>QUANTITÉ</span>
                    <span>TOTAL</span>
                </div>


                @foreach($items as $item)

                    <div
                        class="cart__item"
                        data-variant-id="{{ $item['variant']->id }}"
                        data-unit-price="{{ $item['variant']->product->price }}"
                    >

                        {{-- IMAGE --}}

                        <a
                            href="{{ route('products.show', $item['variant']->product) }}"
                            class="cart__item-image"
                        >
                            @if($item['variant']->product->image)

                                <img
                                    src="{{ asset('storage/' . $item['variant']->product->image) }}"
                                    alt="{{ $item['variant']->product->name }}"
                                >

                            @else

                                <div class="cart__image-placeholder">
                                    NO IMAGE
                                </div>

                            @endif
                        </a>


                        {{-- INFORMATION --}}

                        <div class="cart__item-info">

                            <p class="cart__item-category">
                                {{ strtoupper($item['variant']->product->category->name ?? 'NOAD') }}
                            </p>

                            <h2>
                                {{ $item['variant']->product->name }}
                            </h2>

                            <p class="cart__item-size">
                                TAILLE :
                                <strong>
                                    {{ $item['variant']->size }}
                                </strong>
                            </p>

                            <p class="cart__item-price">
                                {{ number_format($item['variant']->product->price, 0, ',', ' ') }}
                                DA
                            </p>

                            <form
                                action="{{ route('cart.remove', $item['variant']->id) }}"
                                method="POST"
                                class="cart__remove-form"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="cart__remove"
                                >
                                    RETIRER →
                                </button>
                            </form>

                        </div>


                        {{-- QUANTITY --}}

                        <div class="cart__qty">

                            <form
                                action="{{ route('cart.update', $item['variant']->id) }}"
                                method="POST"
                                class="cart__qty-form"
                            >
                                @csrf
                                @method('PATCH')

                                <label
                                    for="quantity-{{ $item['variant']->id }}"
                                    class="sr-only"
                                >
                                    Quantité
                                </label>

                                <button
                                    type="button"
                                    class="cart__qty-button cart__qty-minus"
                                >
                                    −
                                </button>

                                <input
                                    id="quantity-{{ $item['variant']->id }}"
                                    type="number"
                                    name="quantity"
                                    class="cart__qty-input"
                                    value="{{ $item['quantity'] }}"
                                    min="1"
                                    max="{{ $item['variant']->stock }}"
                                >

                                <button
                                    type="button"
                                    class="cart__qty-button cart__qty-plus"
                                >
                                    +
                                </button>
                            </form>

                        </div>


                        {{-- SUBTOTAL --}}

                        <div class="cart__subtotal">

                            <span class="cart__subtotal-label">
                                SOUS-TOTAL
                            </span>

                            <strong>
                                {{ number_format($item['subtotal'], 0, ',', ' ') }}
                                DA
                            </strong>

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- =====================================================
                 ORDER SUMMARY
            ====================================================== --}}

            <aside class="cart__summary">

                <div class="cart__summary-header">
                    <span>NOAD // ORDER</span>
                    <span>{{ count($items) }} ARTICLE(S)</span>
                </div>


                <div class="cart__summary-row">
                    <span>SOUS-TOTAL</span>

                    <strong>
                        {{ number_format($total, 0, ',', ' ') }}
                        DA
                    </strong>
                </div>


                <div class="cart__summary-row">
                    <span>LIVRAISON</span>

                    <strong>
                        CALCULÉE À LA COMMANDE
                    </strong>
                </div>


                <div class="cart__summary-total">
                    <span>TOTAL</span>

                    <strong>
                        {{ number_format($total, 0, ',', ' ') }}
                        DA
                    </strong>
                </div>


                <a
                    href="{{ route('checkout.index') }}"
                    class="cart__checkout"
                >
                    <span>PASSER COMMANDE</span>
                    <span>→</span>
                </a>


                <a
                    href="{{ route('shop.index') }}"
                    class="cart__continue-summary"
                >
                    ← CONTINUER LA BOUTIQUE
                </a>

            </aside>

        </div>

    @else

        {{-- =====================================================
             EMPTY CART
        ====================================================== --}}

        <div class="cart__empty">

            <div class="cart__empty-code">
                CART // 000
            </div>

            <h2>
                TON PANIER EST VIDE
            </h2>

            <p>
                Aucune pièce n'a encore été ajoutée à ton panier.
            </p>

            <a
                href="{{ route('shop.index') }}"
                class="cart__empty-button"
            >
                EXPLORER LA BOUTIQUE →
            </a>

        </div>

    @endif

</div>


<style>

.cart {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 24px 90px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
}


/* =========================================================
   HEADER
========================================================= */

.cart__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;

    padding-bottom: 28px;
    margin-bottom: 30px;

    border-bottom: 1px solid var(--border, #242424);
}

.cart__eyebrow {
    margin: 0 0 8px;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: .14em;

    color: var(--accent, #d32f2f);
}

.cart__title {
    margin: 0;

    font-size: clamp(42px, 6vw, 78px);
    line-height: .9;
    font-weight: 900;
    letter-spacing: -.04em;
}

.cart__continue {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .1em;
    color: #777;
    text-decoration: none;

    transition: color .2s ease;
}

.cart__continue:hover {
    color: var(--text, #fff);
}


/* =========================================================
   NOTICES
========================================================= */

.cart__notice,
.cart__error {
    padding: 14px 16px;
    margin-bottom: 20px;

    font-family: monospace;
    font-size: 11px;
    letter-spacing: .05em;
}

.cart__notice {
    border: 1px solid #2ecc71;
    color: #2ecc71;
    background: rgba(46, 204, 113, .04);
}

.cart__error {
    border: 1px solid var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);
    background: rgba(211, 47, 47, .04);
}


/* =========================================================
   LAYOUT
========================================================= */

.cart__layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 50px;
    align-items: start;
}


/* =========================================================
   ITEMS
========================================================= */

.cart__items {
    min-width: 0;
}

.cart__items-header {
    display: grid;
    grid-template-columns: 1fr 140px 120px;

    padding-bottom: 12px;
    border-bottom: 1px solid var(--border, #242424);

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;
    color: #555;
}

.cart__items-header span:nth-child(2),
.cart__items-header span:nth-child(3) {
    text-align: right;
}


.cart__item {
    display: grid;
    grid-template-columns: 140px minmax(180px, 1fr) 140px 120px;
    gap: 20px;

    padding: 20px 0;

    border-bottom: 1px solid #1c1c1c;
}


/* =========================================================
   IMAGE
========================================================= */

.cart__item-image {
    display: block;

    width: 140px;
    height: 175px;

    background: #111;
    border: 1px solid var(--border, #242424);

    overflow: hidden;
}

.cart__item-image img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;

    transition: transform .3s ease;
}

.cart__item-image:hover img {
    transform: scale(1.03);
}

.cart__image-placeholder {
    width: 100%;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-family: monospace;
    font-size: 9px;
    color: #444;
}


/* =========================================================
   INFO
========================================================= */

.cart__item-info {
    min-width: 0;
    padding-top: 4px;
}

.cart__item-category {
    margin: 0 0 8px;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;

    color: var(--accent, #d32f2f);
}

.cart__item-info h2 {
    margin: 0 0 14px;

    font-size: 22px;
    line-height: 1.05;
    font-weight: 800;

    text-transform: uppercase;
}

.cart__item-size {
    margin: 0 0 7px;

    font-family: monospace;
    font-size: 10px;

    color: #666;
}

.cart__item-size strong {
    color: #aaa;
}

.cart__item-price {
    margin: 0;

    font-family: monospace;
    font-size: 12px;
    color: #aaa;
}

.cart__remove-form {
    margin-top: 25px;
}

.cart__remove {
    padding: 0;

    border: 0;
    background: transparent;

    color: #555;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .1em;

    cursor: pointer;

    transition: color .2s ease;
}

.cart__remove:hover {
    color: var(--accent, #d32f2f);
}


/* =========================================================
   QUANTITY
========================================================= */

.cart__qty {
    display: flex;
    justify-content: flex-end;
    align-items: flex-start;

    padding-top: 4px;
}

.cart__qty-form {
    display: flex;
    align-items: center;

    border: 1px solid var(--border, #242424);
}

.cart__qty-button {
    width: 32px;
    height: 36px;

    border: 0;
    background: #111;

    color: #aaa;

    font-size: 16px;

    cursor: pointer;
}

.cart__qty-button:hover {
    background: #1a1a1a;
    color: #fff;
}

.cart__qty-input {
    width: 42px;
    height: 36px;

    padding: 0;

    border: 0;
    border-left: 1px solid #242424;
    border-right: 1px solid #242424;

    background: #0c0c0c;
    color: #fff;

    text-align: center;

    font-family: monospace;
    font-size: 11px;

    outline: none;
}

.cart__qty-input::-webkit-inner-spin-button,
.cart__qty-input::-webkit-outer-spin-button {
    appearance: none;
}


/* =========================================================
   SUBTOTAL
========================================================= */

.cart__subtotal {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 7px;

    padding-top: 7px;
}

.cart__subtotal-label {
    font-family: monospace;
    font-size: 8px;
    letter-spacing: .1em;
    color: #555;
}

.cart__subtotal strong {
    font-family: monospace;
    font-size: 12px;
    color: #ddd;
}


/* =========================================================
   SUMMARY
========================================================= */

.cart__summary {
    position: sticky;
    top: 30px;

    border: 1px solid var(--border, #242424);
    background: #0e0e0e;
}

.cart__summary-header {
    display: flex;
    justify-content: space-between;

    padding: 16px;

    border-bottom: 1px solid #242424;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .1em;

    color: #666;
}

.cart__summary-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;

    padding: 15px 16px;

    font-family: monospace;
    font-size: 10px;

    border-bottom: 1px solid #1c1c1c;
}

.cart__summary-row span {
    color: #666;
}

.cart__summary-row strong {
    color: #aaa;
    font-weight: normal;
    text-align: right;
}

.cart__summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 20px 16px;

    border-bottom: 1px solid #242424;
}

.cart__summary-total span {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .1em;
    color: #777;
}

.cart__summary-total strong {
    font-family: monospace;
    font-size: 18px;
    color: #fff;
}


/* =========================================================
   CHECKOUT
========================================================= */

.cart__checkout {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin: 16px;
    padding: 16px;

    background: var(--text, #fff);
    border: 1px solid var(--text, #fff);

    color: #000;
    text-decoration: none;

    font-size: 11px;
    font-weight: 900;
    letter-spacing: .12em;

    transition:
        background-color .2s ease,
        color .2s ease,
        border-color .2s ease;
}

.cart__checkout:hover {
    background: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
    color: #fff;
}

.cart__continue-summary {
    display: block;

    margin: 0 16px 18px;

    text-align: center;

    color: #555;
    text-decoration: none;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .08em;
}

.cart__continue-summary:hover {
    color: #aaa;
}


/* =========================================================
   EMPTY CART
========================================================= */

.cart__empty {
    min-height: 420px;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    padding: 50px 20px;

    border: 1px solid #1d1d1d;

    text-align: center;
}

.cart__empty-code {
    margin-bottom: 20px;

    font-family: monospace;
    font-size: 9px;
    letter-spacing: .15em;

    color: var(--accent, #d32f2f);
}

.cart__empty h2 {
    margin: 0;

    font-size: clamp(26px, 4vw, 42px);
    font-weight: 900;
    letter-spacing: -.02em;
}

.cart__empty p {
    margin: 14px 0 25px;

    color: #666;

    font-family: monospace;
    font-size: 10px;
}

.cart__empty-button {
    padding: 14px 18px;

    background: transparent;

    border: 1px solid #444;

    color: #fff;
    text-decoration: none;

    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: .1em;

    transition: all .2s ease;
}

.cart__empty-button:hover {
    background: #fff;
    border-color: #fff;
    color: #000;
}


/* =========================================================
   ACCESSIBILITY
========================================================= */

.sr-only {
    position: absolute;

    width: 1px;
    height: 1px;

    padding: 0;
    margin: -1px;

    overflow: hidden;

    clip: rect(0, 0, 0, 0);

    white-space: nowrap;

    border: 0;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .cart__layout {
        grid-template-columns: 1fr;
    }

    .cart__summary {
        position: static;
    }

}


@media (max-width: 750px) {

    .cart {
        padding: 25px 16px 60px;
    }

    .cart__header {
        align-items: flex-start;
        flex-direction: column;
    }

    .cart__items-header {
        display: none;
    }

    .cart__item {
        grid-template-columns: 100px minmax(0, 1fr);
        gap: 15px;
    }

    .cart__item-image {
        width: 100px;
        height: 125px;
    }

    .cart__qty {
        justify-content: flex-start;
        grid-column: 2;
    }

    .cart__subtotal {
        align-items: flex-start;
        grid-column: 2;
        padding-top: 0;
    }

}


@media (max-width: 450px) {

    .cart__item {
        grid-template-columns: 80px minmax(0, 1fr);
    }

    .cart__item-image {
        width: 80px;
        height: 105px;
    }

    .cart__item-info h2 {
        font-size: 17px;
    }

}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.cart__qty-form').forEach(function (form) {

        const input = form.querySelector('.cart__qty-input');
        const minus = form.querySelector('.cart__qty-minus');
        const plus = form.querySelector('.cart__qty-plus');

        if (!input || !minus || !plus) {
            return;
        }


        minus.addEventListener('click', function () {

            let value = parseInt(input.value, 10) || 1;

            if (value > 1) {
                input.value = value - 1;
                form.submit();
            }

        });


        plus.addEventListener('click', function () {

            let value = parseInt(input.value, 10) || 1;
            const max = parseInt(input.max, 10);

            if (!max || value < max) {
                input.value = value + 1;
                form.submit();
            }

        });


        input.addEventListener('change', function () {

            let value = parseInt(input.value, 10) || 1;
            const max = parseInt(input.max, 10);

            if (value < 1) {
                value = 1;
            }

            if (max && value > max) {
                value = max;
            }

            input.value = value;

            form.submit();

        });

    });

});

</script>

@endsection