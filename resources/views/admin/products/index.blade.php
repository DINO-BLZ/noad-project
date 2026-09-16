@extends('layouts.admin')

@section('content')

<div class="admin-products">

```
{{-- =========================================================
     HEADER
========================================================== --}}

<header class="admin-products__header">

    <div class="admin-products__heading">

        <span class="admin-products__eyebrow">
            NOAD // INVENTAIRE
        </span>

        <h1>
            Produits
        </h1>

        <p class="admin-products__subtitle">
            Gestion du catalogue et des stocks.
        </p>

    </div>

    <a
        href="{{ route('admin.products.create') }}"
        class="admin-btn"
    >
        + Ajouter un produit
    </a>

</header>


{{-- =========================================================
     MESSAGE DE SUCCÈS
========================================================== --}}

@if(session('success'))

    <div class="admin-notice">

        <span class="admin-notice__indicator"></span>

        <span>
            {{ session('success') }}
        </span>

    </div>

@endif


{{-- =========================================================
     KPIs
     DONNÉES RÉELLES ENVOYÉES PAR LE CONTROLLER
========================================================== --}}

<section class="admin-products__stats">

    {{-- RÉFÉRENCES --}}

    <div class="admin-stat">

        <span class="admin-stat__label">
            RÉFÉRENCES
        </span>

        <strong class="admin-stat__value">
            {{ $activeReferences }}
        </strong>

        <span class="admin-stat__meta">
            produit{{ $activeReferences > 1 ? 's' : '' }} au catalogue
        </span>

    </div>


    {{-- RUPTURES --}}

    <div class="admin-stat">

        <span class="admin-stat__label">
            RUPTURES
        </span>

        <strong class="admin-stat__value">
            {{ $outOfStock }}
        </strong>

        <span class="admin-stat__meta">
            produit{{ $outOfStock > 1 ? 's' : '' }} sans stock
        </span>

    </div>


    {{-- VALEUR DU STOCK --}}

    <div class="admin-stat">

        <span class="admin-stat__label">
            VALEUR STOCK
        </span>

        <strong class="admin-stat__value admin-stat__value--stock">
            {{ number_format($stockValue, 0, ',', ' ') }} DA
        </strong>

        <span class="admin-stat__meta">
            valeur théorique actuelle
        </span>

    </div>

</section>


{{-- =========================================================
     TOOLBAR
========================================================== --}}

<section class="admin-products__toolbar">

    <div class="admin-products__toolbar-left">

        <span class="admin-products__count">
            {{ $products->count() }}
            référence{{ $products->count() > 1 ? 's' : '' }}
        </span>

    </div>


    <div class="admin-products__toolbar-right">

        {{-- Ces filtres restent visuels pour le moment.
             Ils ne sont pas encore connectés au backend. --}}

        <select
            class="admin-filter"
            aria-label="Filtrer par statut"
        >

            <option value="">
                Tous les statuts
            </option>

            <option value="available">
                Disponible
            </option>

            <option value="out_of_stock">
                Rupture
            </option>

        </select>


        <select
            class="admin-filter"
            aria-label="Filtrer par catégorie"
        >

            <option value="">
                Toutes les catégories
            </option>

            @php
                $categories = $products
                    ->pluck('category')
                    ->filter()
                    ->unique('id')
                    ->sortBy('name');
            @endphp

            @foreach($categories as $category)

                <option value="{{ $category->id }}">
                    {{ $category->name }}
                </option>

            @endforeach

        </select>

    </div>

</section>


{{-- =========================================================
     LISTE DES PRODUITS
========================================================== --}}

<section class="admin-products__list">

    @forelse($products as $product)

        @php

            /*
            |--------------------------------------------------------------------------
            | STOCK RÉEL
            |--------------------------------------------------------------------------
            |
            | Le stock n'est PAS récupéré depuis un champ inventé
            | comme total_stock.
            |
            | Il est calculé à partir des vraies variantes.
            |
            */

            $totalStock = $product->variants->sum('stock');


            /*
            |--------------------------------------------------------------------------
            | RUPTURE RÉELLE
            |--------------------------------------------------------------------------
            */

            $isOutOfStock = $totalStock <= 0;


            /*
            |--------------------------------------------------------------------------
            | VARIANTES GROUPÉES PAR TAILLE
            |--------------------------------------------------------------------------
            |
            | Si plusieurs variantes possèdent la même taille,
            | leurs stocks sont additionnés.
            |
            */

            $sizes = $product->variants
                ->filter(function ($variant) {
                    return filled($variant->size);
                })
                ->groupBy('size')
                ->sortKeys();


            /*
            |--------------------------------------------------------------------------
            | IMAGE
            |--------------------------------------------------------------------------
            */

            $imageUrl = $product->image
                ? asset('storage/' . $product->image)
                : null;

        @endphp


        {{-- =================================================
             PRODUIT
        ================================================== --}}

        <article class="admin-product">


            {{-- =================================================
                 IMAGE
            ================================================== --}}

            <div class="admin-product__image">

                @if($imageUrl)

                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $product->name }}"
                        loading="lazy"
                    >

                @else

                    <div class="admin-product__image-placeholder">
                        NO IMAGE
                    </div>

                @endif

            </div>


            {{-- =================================================
                 INFORMATIONS + TAILLES
            ================================================== --}}

            <div class="admin-product__main">


                {{-- IDENTITÉ PRODUIT --}}

                <div class="admin-product__identity">

                    <span class="admin-product__category">
                        {{ $product->category->name ?? 'Sans catégorie' }}
                    </span>

                    <h2>
                        {{ $product->name }}
                    </h2>

                    <span class="admin-product__price">
                        {{ number_format($product->price, 0, ',', ' ') }} DA
                    </span>

                </div>


                {{-- =================================================
                     GRILLE DES TAILLES RÉELLES
                ================================================== --}}

                <div class="admin-product__sizes">

                    <div class="admin-product__sizes-header">

                        <span>
                            STOCK PAR TAILLE
                        </span>

                        <strong>
                            {{ $totalStock }}
                            unité{{ $totalStock > 1 ? 's' : '' }}
                        </strong>

                    </div>


                    @if($sizes->isNotEmpty())

                        <div class="admin-size-grid">

                            @foreach($sizes as $size => $variants)

                                @php
                                    $sizeStock = $variants->sum('stock');
                                @endphp

                                <div
                                    class="admin-size {{ $sizeStock <= 0 ? 'admin-size--empty' : '' }}"
                                >

                                    <span class="admin-size__name">
                                        {{ $size }}
                                    </span>

                                    <strong class="admin-size__stock">
                                        {{ $sizeStock }}
                                    </strong>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="admin-product__no-variants">
                            Aucune taille configurée.
                        </div>

                    @endif

                </div>

            </div>


            {{-- =================================================
                 STOCK GLOBAL
            ================================================== --}}

            <div
                class="admin-product__stock {{ $isOutOfStock ? 'admin-product__stock--empty' : '' }}"
            >

                <span class="admin-product__stock-label">
                    STOCK
                </span>

                <strong class="admin-product__stock-value">
                    {{ $totalStock }}
                </strong>

                <span class="admin-product__stock-status">

                    @if($isOutOfStock)
                        RUPTURE
                    @else
                        DISPONIBLE
                    @endif

                </span>

            </div>


            {{-- =================================================
                 ACTIONS
            ================================================== --}}

            <div class="admin-product__actions">

                <a
                    href="{{ route('admin.products.edit', $product) }}"
                    class="admin-product__edit"
                >
                    Modifier
                </a>


                <form
                    action="{{ route('admin.products.destroy', $product) }}"
                    method="POST"
                    onsubmit="return confirm('Supprimer ce produit ? Cette action est irréversible.');"
                >

                    @csrf

                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-product__delete"
                    >
                        Supprimer
                    </button>

                </form>

            </div>

        </article>

    @empty


        {{-- =================================================
             EMPTY STATE
        ================================================== --}}

        <div class="admin-products__empty">

            <span class="admin-products__empty-code">
                NOAD // 000
            </span>

            <h2>
                Aucun produit
            </h2>

            <p>
                Aucun produit n'est actuellement présent dans le catalogue.
            </p>

            <a
                href="{{ route('admin.products.create') }}"
                class="admin-btn"
            >
                + Ajouter le premier produit
            </a>

        </div>

    @endforelse

</section>


{{-- =========================================================
     FOOTER
========================================================== --}}

@if($products->isNotEmpty())

    <div class="admin-products__footer">

        <span>
            AFFICHAGE COMPLET DU CATALOGUE
        </span>

        <span>
            {{ $products->count() }}
            référence{{ $products->count() > 1 ? 's' : '' }}
        </span>

    </div>

@endif
```

</div>

<style>

/* =============================================================
   BASE
============================================================= */

.admin-products {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 3rem;
    color: var(--text);
}


/* =============================================================
   HEADER
============================================================= */

.admin-products__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 2rem;
    margin-bottom: 2.5rem;
}

.admin-products__eyebrow {
    display: block;
    margin-bottom: .7rem;
    color: var(--muted, #8a8a8a);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
}

.admin-products__header h1 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3.5rem);
    line-height: .95;
    font-weight: 900;
    letter-spacing: -.04em;
    text-transform: uppercase;
}

.admin-products__subtitle {
    margin: .8rem 0 0;
    color: var(--muted, #8a8a8a);
    font-size: .85rem;
}


/* =============================================================
   BOUTON
============================================================= */

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: .75rem 1.25rem;
    border: 1px solid var(--accent);
    background: var(--accent);
    color: #fff;
    text-decoration: none;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    transition:
        opacity .2s ease,
        transform .2s ease;
}

.admin-btn:hover {
    opacity: .88;
    transform: translateY(-1px);
}


/* =============================================================
   NOTICE
============================================================= */

.admin-notice {
    display: flex;
    align-items: center;
    gap: .7rem;
    margin-bottom: 2rem;
    padding: .9rem 1rem;
    border: 1px solid rgba(46, 204, 113, .45);
    background: rgba(46, 204, 113, .08);
    color: #2ecc71;
    font-size: .78rem;
}

.admin-notice__indicator {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 50%;
    background: #2ecc71;
}


/* =============================================================
   KPIs
============================================================= */

.admin-products__stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-bottom: 2rem;
}

.admin-stat {
    min-height: 145px;
    padding: 1.5rem;
    border-right: 1px solid var(--border);
}

.admin-stat:last-child {
    border-right: 0;
}

.admin-stat__label {
    display: block;
    margin-bottom: .9rem;
    color: var(--muted, #8a8a8a);
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.admin-stat__value {
    display: block;
    font-size: 2rem;
    line-height: 1;
    font-weight: 900;
}

.admin-stat__value--stock {
    font-size: 1.55rem;
}

.admin-stat__meta {
    display: block;
    margin-top: .65rem;
    color: var(--muted, #8a8a8a);
    font-size: .72rem;
}


/* =============================================================
   TOOLBAR
============================================================= */

.admin-products__toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.admin-products__count {
    color: var(--muted, #8a8a8a);
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.admin-products__toolbar-right {
    display: flex;
    gap: .5rem;
}

.admin-filter {
    min-width: 160px;
    padding: .65rem .8rem;
    border: 1px solid var(--border);
    border-radius: 0;
    outline: none;
    background: transparent;
    color: var(--text);
    font-family: inherit;
    font-size: .7rem;
    text-transform: uppercase;
    cursor: pointer;
}


/* =============================================================
   LISTE PRODUITS
============================================================= */

.admin-products__list {
    width: 100%;
}


/* =============================================================
   PRODUIT
============================================================= */

.admin-product {
    display: grid;
    grid-template-columns: 100px minmax(260px, 1fr) 100px auto;
    align-items: center;
    gap: 1.5rem;
    min-height: 145px;
    padding: 1.25rem 0;
    border-bottom: 1px solid var(--border);
}

.admin-product:hover {
    background: rgba(255, 255, 255, .015);
}


/* =============================================================
   IMAGE
============================================================= */

.admin-product__image {
    width: 100px;
    height: 120px;
    overflow: hidden;
    background: var(--border);
}

.admin-product__image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.admin-product__image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: var(--muted, #8a8a8a);
    font-size: .55rem;
    font-weight: 800;
    letter-spacing: .1em;
}


/* =============================================================
   INFORMATIONS
============================================================= */

.admin-product__main {
    min-width: 0;
}

.admin-product__category {
    display: block;
    margin-bottom: .35rem;
    color: var(--muted, #8a8a8a);
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.admin-product__identity h2 {
    margin: 0;
    font-size: 1rem;
    line-height: 1.1;
    font-weight: 900;
    text-transform: uppercase;
}

.admin-product__price {
    display: block;
    margin-top: .45rem;
    font-size: .78rem;
    font-weight: 700;
}


/* =============================================================
   TAILLES
============================================================= */

.admin-product__sizes {
    margin-top: 1rem;
}

.admin-product__sizes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: .5rem;
    color: var(--muted, #8a8a8a);
    font-size: .58rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.admin-product__sizes-header strong {
    color: var(--text);
    font-size: .62rem;
}

.admin-size-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
}

.admin-size {
    display: grid;
    grid-template-columns: auto auto;
    align-items: center;
    gap: .5rem;
    min-width: 64px;
    padding: .4rem .5rem;
    border: 1px solid var(--border);
}

.admin-size__name {
    font-size: .62rem;
    font-weight: 800;
    text-transform: uppercase;
}

.admin-size__stock {
    font-size: .68rem;
    font-weight: 900;
}

.admin-size--empty {
    opacity: .45;
}

.admin-size--empty .admin-size__stock {
    text-decoration: line-through;
}

.admin-product__no-variants {
    padding: .6rem;
    border: 1px dashed var(--border);
    color: var(--muted, #8a8a8a);
    font-size: .65rem;
}


/* =============================================================
   STOCK GLOBAL
============================================================= */

.admin-product__stock {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 90px;
    padding: .8rem;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    text-align: center;
}

.admin-product__stock-label {
    color: var(--muted, #8a8a8a);
    font-size: .58rem;
    font-weight: 800;
    letter-spacing: .1em;
}

.admin-product__stock-value {
    margin-top: .35rem;
    font-size: 1.7rem;
    line-height: 1;
    font-weight: 900;
}

.admin-product__stock-status {
    margin-top: .45rem;
    font-size: .55rem;
    font-weight: 800;
    letter-spacing: .08em;
}

.admin-product__stock--empty {
    color: var(--accent);
}


/* =============================================================
   ACTIONS
============================================================= */

.admin-product__actions {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    min-width: 100px;
}

.admin-product__edit,
.admin-product__delete {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: .45rem .7rem;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text);
    font-family: inherit;
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-decoration: none;
    text-transform: uppercase;
    cursor: pointer;
}

.admin-product__edit:hover {
    border-color: var(--text);
}

.admin-product__delete {
    color: var(--muted, #8a8a8a);
}

.admin-product__delete:hover {
    border-color: var(--accent);
    background: var(--accent);
    color: #fff;
}


/* =============================================================
   EMPTY STATE
============================================================= */

.admin-products__empty {
    padding: 5rem 2rem;
    border-bottom: 1px solid var(--border);
    text-align: center;
}

.admin-products__empty-code {
    display: block;
    margin-bottom: 1rem;
    color: var(--muted, #8a8a8a);
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .15em;
}

.admin-products__empty h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 900;
    text-transform: uppercase;
}

.admin-products__empty p {
    margin: .7rem 0 1.5rem;
    color: var(--muted, #8a8a8a);
    font-size: .8rem;
}


/* =============================================================
   FOOTER
============================================================= */

.admin-products__footer {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    color: var(--muted, #8a8a8a);
    font-size: .6rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1100px) {

    .admin-product {
        grid-template-columns: 90px minmax(220px, 1fr) 90px;
    }

    .admin-product__actions {
        grid-column: 2 / -1;
        flex-direction: row;
        justify-content: flex-end;
    }

}


@media (max-width: 800px) {

    .admin-products {
        padding: 2rem 1.25rem;
    }

    .admin-products__header {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-products__stats {
        grid-template-columns: 1fr;
    }

    .admin-stat {
        min-height: auto;
        border-right: 0;
        border-bottom: 1px solid var(--border);
    }

    .admin-stat:last-child {
        border-bottom: 0;
    }

    .admin-products__toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .admin-products__toolbar-right {
        width: 100%;
    }

    .admin-filter {
        width: 100%;
        min-width: 0;
    }

    .admin-product {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
        padding: 1.25rem 0;
    }

    .admin-product__image {
        width: 80px;
        height: 100px;
    }

    .admin-product__stock {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: flex-start;
        gap: .7rem;
        min-height: auto;
        border: 0;
        border-top: 1px solid var(--border);
        padding: .8rem 0 0;
        text-align: left;
    }

    .admin-product__stock-value {
        margin-top: 0;
        font-size: 1rem;
    }

    .admin-product__stock-status {
        margin-top: 0;
    }

    .admin-product__actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }

}


@media (max-width: 500px) {

    .admin-products {
        padding: 1.5rem 1rem;
    }

    .admin-product {
        grid-template-columns: 65px 1fr;
    }

    .admin-product__image {
        width: 65px;
        height: 85px;
    }

    .admin-product__identity h2 {
        font-size: .85rem;
    }

    .admin-product__sizes-header {
        align-items: flex-start;
        flex-direction: column;
        gap: .25rem;
    }

    .admin-size {
        min-width: 58px;
    }

}

</style>

@endsection
