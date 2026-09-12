@extends('layouts.app')

@section('content')

@php
    $activeDrop = $product->activeDrop();

    $sizeOrder = [
        'XS' => 0,
        'S' => 1,
        'M' => 2,
        'L' => 3,
        'XL' => 4,
        'XXL' => 5,
    ];

    $sortedVariants = $product->variants
        ->sortBy(function ($variant) use ($sizeOrder) {
            if (isset($sizeOrder[$variant->size])) {
                return $sizeOrder[$variant->size];
            }

            if (is_numeric($variant->size)) {
                return 100 + (float) $variant->size;
            }

            return 1000;
        })
        ->values();

    $availableVariants = $sortedVariants->where('stock', '>', 0);

    $totalStock = $sortedVariants->sum('stock');

    $hasGallery = $product->images->count() > 0;

    $isWhitelisted = false;

    if ($activeDrop && auth()->check()) {
        $isWhitelisted = auth()->user()->isWhitelistedForDrop($activeDrop);
    }
@endphp

<div class="product-page">

    {{-- =========================================================
         PRODUCT HEADER
    ========================================================== --}}
    <div class="product-topbar">

        <div class="product-breadcrumb">
           <a href="{{ route('shop.index') }}">
    BOUTIQUE
</a>

            <span>/</span>

            <span>
                {{ strtoupper($product->category->name) }}
            </span>

            <span>/</span>

            <span>
                {{ strtoupper($product->name) }}
            </span>
        </div>

        <span class="product-system-code">
            NOAD // PRODUCT ARCHIVE
        </span>

    </div>


    {{-- =========================================================
         MAIN PRODUCT GRID
    ========================================================== --}}
    <div class="product-layout">

        {{-- =====================================================
             GALLERY
        ====================================================== --}}
        <section class="product-gallery">

            <div class="product-main-image">

                @if($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        id="product-main-image"
                    >
                @else
                    <div class="product-image-placeholder">
                        <span>NOAD // IMAGE UNAVAILABLE</span>
                    </div>
                @endif

                <div class="product-image-index">
                    <span>01</span>
                    <span>/</span>
                    <span>
                        {{ $hasGallery ? $product->images->count() + 1 : 1 }}
                    </span>
                </div>

            </div>


            {{-- THUMBNAILS --}}
            <div class="product-thumbnails">

                @if($product->image)
                    <button
                        type="button"
                        class="product-thumbnail is-active"
                        data-full="{{ asset('storage/' . $product->image) }}"
                        aria-label="Image principale"
                    >
                        <img
                            src="{{ asset('storage/' . $product->image) }}"
                            alt="{{ $product->name }}"
                        >
                    </button>
                @endif


                @foreach($product->images->sortBy([
                    fn ($image) => $image->is_primary ? 0 : 1,
                    fn ($image) => $image->position,
                ]) as $image)

                    <button
                        type="button"
                        class="product-thumbnail"
                        data-full="{{ asset('storage/' . $image->path) }}"
                        aria-label="Vue {{ $loop->iteration + 1 }}"
                    >
                        <img
                            src="{{ asset('storage/' . $image->path) }}"
                            alt="{{ $product->name }} - vue {{ $loop->iteration + 1 }}"
                        >
                    </button>

                @endforeach

            </div>

        </section>


        {{-- =====================================================
             PRODUCT INFORMATION
        ====================================================== --}}
        <section class="product-info">

            {{-- PRODUCT META --}}
            <div class="product-meta">

                <span class="product-category">
                    {{ strtoupper($product->category->name) }}
                </span>

                <span class="product-reference">
                    REF. NOAD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}
                </span>

            </div>


            {{-- TITLE --}}
            <h1 class="product-title">
                {{ $product->name }}
            </h1>


            {{-- PRICE --}}
            <div class="product-price">
                {{ number_format($product->price, 0, ',', ' ') }} DA
            </div>


            {{-- DESCRIPTION --}}
            @if($product->description)
                <div class="product-description">
                    {{ $product->description }}
                </div>
            @endif


            {{-- =================================================
                 DROP INFORMATION
            ================================================== --}}
            @if($activeDrop)

                <div class="product-drop-panel">

                    <div class="drop-panel-header">

                        <div>
                            <span class="drop-label">
                                DROP // ACTIF
                            </span>

                            <strong>
                                {{ strtoupper($activeDrop->name) }}
                            </strong>
                        </div>

                        <span class="drop-status-dot"></span>

                    </div>


                    @if($isWhitelisted)

                        <div class="drop-access approved">
                            <span class="drop-access-icon">✓</span>

                            <div>
                                <strong>ACCÈS WHITELIST CONFIRMÉ</strong>

                                <p>
                                    Votre compte dispose de l'accès prioritaire
                                    à ce drop.
                                </p>
                            </div>
                        </div>

                    @elseif(auth()->check())

                        <div class="drop-access restricted">
                            <span class="drop-access-icon">!</span>

                            <div>
                                <strong>ACCÈS RESTREINT</strong>

                                <p>
                                    Ce produit appartient à un drop privé.
                                    Une whitelist est nécessaire pour l'acquérir.
                                </p>
                            </div>
                        </div>

                    @else

                        <div class="drop-access restricted">
                            <span class="drop-access-icon">!</span>

                            <div>
                                <strong>ACCÈS MEMBRE REQUIS</strong>

                                <p>
                                    Connectez-vous pour vérifier votre accès
                                    à ce drop.
                                </p>
                            </div>
                        </div>

                    @endif

                </div>

            @endif


            {{-- =================================================
                 ADD TO CART
            ================================================== --}}
            <form
                action="{{ route('cart.add', $product) }}"
                method="POST"
                class="product-form"
                id="add-to-cart-form"
            >

                @csrf


                {{-- SIZE SELECTOR --}}
                <div class="product-option-section">

                    <div class="option-header">

                        <label class="option-label">
                            SÉLECTIONNER LA TAILLE
                        </label>

                        <span class="option-count">
                            {{ $availableVariants->count() }} DISPONIBLES
                        </span>

                    </div>


                    <div class="product-sizes">

                        @forelse($sortedVariants as $variant)

                            <label
                                class="size-option {{ $variant->stock <= 0 ? 'is-disabled' : '' }}"
                            >

                                <input
                                    type="radio"
                                    name="variant_id"
                                    value="{{ $variant->id }}"
                                    {{ $variant->stock <= 0 ? 'disabled' : '' }}
                                    required
                                >

                                <span class="size-value">
                                    {{ $variant->size }}
                                </span>

                                @if($variant->stock <= 0)

                                    <span class="size-stock">
                                        ÉPUISÉ
                                    </span>

                                @elseif($variant->stock <= 3)

                                    <span class="size-stock low">
                                        {{ $variant->stock }} REST.
                                    </span>

                                @endif

                            </label>

                        @empty

                            <div class="no-variants">
                                AUCUNE TAILLE DISPONIBLE.
                            </div>

                        @endforelse

                    </div>

                </div>


                {{-- ADD BUTTON --}}
                <button
                    type="submit"
                    class="product-add-button"
                    {{ $availableVariants->count() === 0 ? 'disabled' : '' }}
                >

                    <span>
                        {{ $availableVariants->count() > 0
                            ? 'AJOUTER AU PANIER'
                            : 'PRODUIT ÉPUISÉ'
                        }}
                    </span>

                    <span class="button-arrow">
                        →
                    </span>

                </button>

            </form>


            {{-- WHITELIST --}}
            @if($activeDrop && auth()->check() && !$isWhitelisted)

                <div class="product-whitelist">

                    <div class="whitelist-header">
                        <span class="whitelist-code">
                            ACCESS // 01
                        </span>

                        <span>
                            WHITELIST
                        </span>
                    </div>

                    <p>
                        Vous n'avez pas encore accès à ce drop.
                        Demandez votre whitelist pour pouvoir acquérir
                        cette pièce.
                    </p>

                    <form
                        action="{{ route('drops.request-whitelist', $activeDrop) }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="whitelist-button"
                        >
                            DEMANDER LA WHITELIST →
                        </button>
                    </form>

                </div>

            @elseif($activeDrop && auth()->guest())

                <div class="product-login-note">

                    <span>ACCÈS MEMBRE</span>

                    <p>
                        Connectez-vous à votre compte pour vérifier
                        votre éligibilité à ce drop.
                    </p>

                    <a href="{{ route('login') }}">
                        SE CONNECTER →
                    </a>

                </div>

            @endif


            {{-- =================================================
                 PRODUCT INFORMATION BLOCKS
            ================================================== --}}
            <div class="product-details">

                <div class="detail-row">
                    <span>CATÉGORIE</span>
                    <strong>
                        {{ strtoupper($product->category->name) }}
                    </strong>
                </div>

                <div class="detail-row">
                    <span>RÉFÉRENCE</span>
                    <strong>
                        NOAD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}
                    </strong>
                </div>

                <div class="detail-row">
                    <span>STOCK TOTAL</span>
                    <strong>
                        {{ $totalStock }} UNITÉS
                    </strong>
                </div>

                <div class="detail-row">
                    <span>LIVRAISON</span>
                    <strong>
                        58 WILAYAS // COD
                    </strong>
                </div>

            </div>


            {{-- BRAND MANIFESTO --}}
            <div class="product-manifesto">

                <span>
                    NOAD // PRINCIPLE 001
                </span>

                <p>
                    DEFEND YOUR PRINCIPLE
                </p>

            </div>

        </section>

    </div>

</div>


<style>

.product-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 32px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
}

.product-page a {
    color: inherit;
    text-decoration: none;
}


/* =========================================================
   TOP BAR
========================================================= */

.product-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding-bottom: 18px;
    margin-bottom: 32px;
    border-bottom: 1px solid var(--border, #242424);
}

.product-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: .1em;
    color: #666;
}

.product-breadcrumb a:hover {
    color: var(--text, #fff);
}

.product-system-code {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
    color: var(--accent, #d32f2f);
    white-space: nowrap;
}


/* =========================================================
   MAIN LAYOUT
========================================================= */

.product-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(400px, .85fr);
    gap: 64px;
    align-items: start;
}


/* =========================================================
   GALLERY
========================================================= */

.product-gallery {
    min-width: 0;
}

.product-main-image {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 5;
    background: #151515;
    border: 1px solid var(--border, #242424);
    overflow: hidden;
}

.product-main-image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;

    font-family: monospace;
    font-size: 11px;
    color: #555;
}

.product-image-index {
    position: absolute;
    right: 16px;
    bottom: 16px;

    display: flex;
    gap: 5px;

    padding: 7px 10px;

    background: rgba(0, 0, 0, .75);
    border: 1px solid #333;

    font-family: monospace;
    font-size: 10px;
    color: #aaa;
}


/* =========================================================
   THUMBNAILS
========================================================= */

.product-thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(82px, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.product-thumbnail {
    position: relative;

    width: 100%;
    aspect-ratio: 1 / 1;

    padding: 0;

    background: #151515;
    border: 1px solid var(--border, #242424);

    cursor: pointer;
    overflow: hidden;

    transition:
        border-color .2s ease,
        opacity .2s ease;
}

.product-thumbnail:hover {
    border-color: #666;
}

.product-thumbnail.is-active {
    border-color: var(--accent, #d32f2f);
}

.product-thumbnail img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}


/* =========================================================
   PRODUCT INFO
========================================================= */

.product-info {
    min-width: 0;
    padding-top: 4px;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;

    margin-bottom: 12px;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
}

.product-category {
    color: var(--accent, #d32f2f);
    font-weight: bold;
}

.product-reference {
    color: #555;
}

.product-title {
    margin: 0;

    font-size: clamp(32px, 4vw, 54px);
    line-height: 1;
    font-weight: 900;

    letter-spacing: -.02em;
    text-transform: uppercase;
}

.product-price {
    margin-top: 20px;

    font-family: monospace;
    font-size: 19px;
    font-weight: 800;
    letter-spacing: .04em;
}

.product-description {
    margin-top: 28px;
    padding-top: 24px;

    border-top: 1px solid var(--border, #242424);

    color: #888;
    font-size: 13px;
    line-height: 1.7;
}


/* =========================================================
   DROP PANEL
========================================================= */

.product-drop-panel {
    margin-top: 28px;

    border: 1px solid var(--accent, #d32f2f);
    background: #101010;
}

.drop-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 15px 16px;

    border-bottom: 1px solid #292929;
}

.drop-panel-header > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.drop-label {
    font-family: monospace;
    font-size: 9px;
    color: var(--accent, #d32f2f);
    letter-spacing: .15em;
}

.drop-panel-header strong {
    font-size: 13px;
    letter-spacing: .04em;
}

.drop-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--accent, #d32f2f);
    box-shadow: 0 0 10px rgba(211, 47, 47, .6);
}

.drop-access {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
}

.drop-access-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 20px;
    height: 20px;

    flex: 0 0 20px;

    border: 1px solid currentColor;

    font-family: monospace;
    font-size: 11px;
    font-weight: bold;
}

.drop-access.approved {
    color: #2ecc71;
}

.drop-access.restricted {
    color: var(--accent, #d32f2f);
}

.drop-access strong {
    display: block;

    color: #fff;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: .1em;
}

.drop-access p {
    margin: 5px 0 0;

    color: #777;

    font-family: monospace;
    font-size: 10px;
    line-height: 1.5;
}


/* =========================================================
   FORM
========================================================= */

.product-form {
    margin-top: 32px;
}

.product-option-section {
    margin-bottom: 20px;
}

.option-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 12px;
}

.option-label,
.option-count {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
}

.option-label {
    color: #888;
}

.option-count {
    color: #555;
}


/* =========================================================
   SIZE SELECTOR
========================================================= */

.product-sizes {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.size-option {
    position: relative;

    min-height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--border, #242424);
    background: #121212;

    cursor: pointer;

    transition:
        background-color .2s ease,
        border-color .2s ease,
        color .2s ease;
}

.size-option:hover:not(.is-disabled) {
    border-color: #666;
}

.size-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.size-option:has(input:checked) {
    background: var(--text, #fff);
    border-color: var(--text, #fff);
    color: #000;
}

.size-value {
    font-family: monospace;
    font-size: 12px;
    font-weight: bold;
}

.size-stock {
    position: absolute;
    right: 5px;
    bottom: 4px;

    font-family: monospace;
    font-size: 7px;
    color: #555;
}

.size-stock.low {
    color: var(--accent, #d32f2f);
}

.size-option.is-disabled {
    opacity: .35;
    cursor: not-allowed;
}

.size-option.is-disabled .size-value {
    text-decoration: line-through;
}

.no-variants {
    grid-column: 1 / -1;

    padding: 20px;

    border: 1px solid var(--border, #242424);

    font-family: monospace;
    font-size: 10px;
    text-align: center;
    color: #666;
}


/* =========================================================
   ADD TO CART
========================================================= */

.product-add-button {
    width: 100%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 17px 18px;

    background: var(--text, #fff);
    color: #000;

    border: 1px solid var(--text, #fff);

    font-size: 12px;
    font-weight: 900;
    letter-spacing: .15em;

    cursor: pointer;

    transition:
        background-color .2s ease,
        border-color .2s ease,
        color .2s ease;
}

.product-add-button:hover:not(:disabled) {
    background: var(--accent, #d32f2f);
    border-color: var(--accent, #d32f2f);
    color: #fff;
}

.product-add-button:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.button-arrow {
    font-size: 18px;
}


/* =========================================================
   WHITELIST
========================================================= */

.product-whitelist {
    margin-top: 18px;

    padding: 18px;

    background: #0e0e0e;
    border: 1px solid #242424;
}

.whitelist-header {
    display: flex;
    justify-content: space-between;

    padding-bottom: 10px;
    margin-bottom: 12px;

    border-bottom: 1px solid #242424;

    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;

    color: #888;
}

.whitelist-code {
    color: var(--accent, #d32f2f);
}

.product-whitelist p,
.product-login-note p {
    margin: 0 0 15px;

    color: #777;

    font-family: monospace;
    font-size: 10px;
    line-height: 1.6;
}

.whitelist-button {
    width: 100%;

    padding: 12px;

    background: transparent;

    border: 1px solid var(--accent, #d32f2f);
    color: var(--accent, #d32f2f);

    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: .1em;

    cursor: pointer;

    transition: all .2s ease;
}

.whitelist-button:hover {
    background: var(--accent, #d32f2f);
    color: #fff;
}

.product-login-note {
    margin-top: 18px;

    padding: 18px;

    border: 1px solid #242424;
    background: #0e0e0e;
}

.product-login-note > span {
    display: block;
    margin-bottom: 8px;

    color: var(--accent, #d32f2f);

    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: .12em;
}

.product-login-note a {
    color: var(--text, #fff);
    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
}

.product-login-note a:hover {
    color: var(--accent, #d32f2f);
}


/* =========================================================
   DETAILS
========================================================= */

.product-details {
    margin-top: 32px;

    border-top: 1px solid var(--border, #242424);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;

    padding: 13px 0;

    border-bottom: 1px solid #1c1c1c;

    font-family: monospace;
    font-size: 10px;
}

.detail-row span {
    color: #555;
    letter-spacing: .1em;
}

.detail-row strong {
    color: #aaa;
    font-weight: normal;
    text-align: right;
}


/* =========================================================
   MANIFESTO
========================================================= */

.product-manifesto {
    margin-top: 28px;
    padding-top: 18px;

    border-top: 1px solid var(--border, #242424);
}

.product-manifesto span {
    font-family: monospace;
    font-size: 9px;
    color: #555;
    letter-spacing: .12em;
}

.product-manifesto p {
    margin: 8px 0 0;

    color: var(--accent, #d32f2f);

    font-family: monospace;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: .15em;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .product-layout {
        grid-template-columns: 1fr;
        gap: 42px;
    }

    .product-info {
        max-width: 800px;
    }

}


@media (max-width: 600px) {

    .product-page {
        padding: 20px 16px 60px;
    }

    .product-topbar {
        align-items: flex-start;
        flex-direction: column;
        margin-bottom: 22px;
    }

    .product-system-code {
        display: none;
    }

    .product-layout {
        gap: 30px;
    }

    .product-title {
        font-size: 34px;
    }

    .product-sizes {
        grid-template-columns: repeat(3, 1fr);
    }

    .product-thumbnails {
        grid-template-columns: repeat(5, 1fr);
    }

    .product-thumbnail {
        min-width: 0;
    }

}


@media (max-width: 400px) {

    .product-sizes {
        grid-template-columns: repeat(2, 1fr);
    }

}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const mainImage = document.getElementById('product-main-image');
    const thumbnails = document.querySelectorAll('.product-thumbnail');

    if (!mainImage || !thumbnails.length) {
        return;
    }

    thumbnails.forEach(function (thumbnail) {

        thumbnail.addEventListener('click', function () {

            const fullImage = this.dataset.full;

            if (!fullImage) {
                return;
            }

            mainImage.src = fullImage;

            thumbnails.forEach(function (item) {
                item.classList.remove('is-active');
            });

            this.classList.add('is-active');

        });

    });

});
</script>

@endsection