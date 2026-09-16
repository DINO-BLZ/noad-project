@extends('layouts.admin')

@section('title', 'Modifier le Produit #' . $product->id . ' — NOAD Admin')

@section('content')

<div class="noad-admin-edit-product-scope">

```
<!-- ================= BARRE SUPÉRIEURE / FIL D'ARIANE ================= -->

<div class="top-nav-bar font-mono">

    <div class="breadcrumbs">

        <a href="{{ route('admin.dashboard') }}">
            PORTAL
        </a>

        <span class="sep">/</span>

        <a href="{{ route('admin.products.index') }}">
            CATALOGUE
        </a>

        <span class="sep">/</span>

        <span class="active">
            MODIFIER LE PRODUIT // ID #{{ $product->id }}
        </span>

    </div>

    <div class="system-status">

        <span class="status-tag">
            <span class="dot-active">■</span>
            MODE ÉDITION : ACTIF
        </span>

        <span class="user-badge">
            AD
        </span>

    </div>

</div>


<div class="edit-form-container">

    <!-- ================= EN-TÊTE DE LA PAGE ================= -->

    <div class="form-header-row">

        <div>

            <div class="header-pretitle font-mono text-accent">
                <span class="glyph">≡</span>
                REGISTRE CATALOGUE
            </div>

            <h1 class="page-title">
                MODIFIER LE PRODUIT
            </h1>

            <p class="page-subtitle">
                Mettez à jour les informations, la tarification, le visuel et les variantes de stock.
            </p>

        </div>


        @if(Route::has('products.show'))

            <a
                href="{{ route('products.show', $product->slug ?? $product->id) }}"
                target="_blank"
                class="btn-preview-link font-mono"
            >
                <span class="glyph">◉</span>
                APERÇU EN LIGNE
            </a>

        @endif

    </div>


    <!-- ================= MESSAGE DE SUCCÈS ================= -->

    @if(session('success'))

        <div class="alert-success-box font-mono">

            <span class="alert-icon">
                ✓
            </span>

            <div>

                <strong>
                    MODIFICATIONS SYNCHRONISÉES
                </strong>

                <div class="alert-sub">
                    {{ session('success') }}
                </div>

            </div>

        </div>

    @endif


    <!-- ================= FORMULAIRE ================= -->

    <form
        action="{{ route('admin.products.update', $product->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="product-form"
    >

        @csrf

        @method('PUT')


        <!-- =========================================================
             01. INFORMATIONS GÉNÉRALES
        ========================================================== -->

        <div class="form-section">

            <div class="section-header-wrap">

                <div class="section-badge-group">

                    <span class="section-badge font-mono">
                        01
                    </span>

                    <h2 class="section-title">
                        INFORMATIONS GÉNÉRALES
                    </h2>

                </div>

                <span class="section-tag font-mono text-muted">
                    OBLIGATOIRE
                </span>

            </div>


            <!-- NOM DU PRODUIT -->

            <div class="form-group">

                <div class="label-with-meta">

                    <label
                        for="name"
                        class="form-label font-mono"
                    >
                        NOM DU PRODUIT
                    </label>

                    <span class="meta-tag font-mono">
                        FR-QC
                    </span>

                </div>

                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $product->name) }}"
                    placeholder="Ex : Logo Hoodie"
                    class="form-control @error('name') is-invalid @enderror"
                    required
                >

                @error('name')

                    <span class="error-feedback font-mono">
                        {{ $message }}
                    </span>

                @enderror

            </div>


            <!-- CATÉGORIE + PRIX -->

            <div class="form-row-2">

                <!-- CATÉGORIE -->

                <div class="form-group">

                    <label
                        for="category_id"
                        class="form-label font-mono"
                    >
                        CATÉGORIE
                    </label>

                    <div class="select-wrapper">

                        <select
                            name="category_id"
                            id="category_id"
                            class="form-control select-custom @error('category_id') is-invalid @enderror"
                            required
                        >

                            <option
                                value=""
                                disabled
                                {{ old('category_id', $product->category_id) ? '' : 'selected' }}
                            >
                                Sélectionner une catégorie
                            </option>

                            @if(isset($categories) && count($categories) > 0)

                                @foreach($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}
                                    >
                                        {{ strtoupper($category->name) }}
                                    </option>

                                @endforeach

                            @else

                                <option
                                    value="1"
                                    {{ old('category_id', $product->category_id) == 1 ? 'selected' : '' }}
                                >
                                    VESTES & HOODIES
                                </option>

                                <option
                                    value="2"
                                    {{ old('category_id', $product->category_id) == 2 ? 'selected' : '' }}
                                >
                                    SWEATS
                                </option>

                                <option
                                    value="3"
                                    {{ old('category_id', $product->category_id) == 3 ? 'selected' : '' }}
                                >
                                    T-SHIRTS
                                </option>

                                <option
                                    value="4"
                                    {{ old('category_id', $product->category_id) == 4 ? 'selected' : '' }}
                                >
                                    PANTALONS & CARGOS
                                </option>

                                <option
                                    value="5"
                                    {{ old('category_id', $product->category_id) == 5 ? 'selected' : '' }}
                                >
                                    ACCESSOIRES
                                </option>

                            @endif

                        </select>

                        <span class="select-arrow">
                            ▼
                        </span>

                    </div>

                    @error('category_id')

                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>

                    @enderror

                </div>


                <!-- PRIX -->

                <div class="form-group">

                    <div class="label-with-meta">

                        <label
                            for="price"
                            class="form-label font-mono"
                        >
                            PRIX UNITAIRE
                        </label>

                        <span class="meta-tag font-mono text-accent">
                            DA (DZD)
                        </span>

                    </div>

                    <div class="input-currency-wrapper">

                        <input
                            type="number"
                            name="price"
                            id="price"
                            step="100"
                            min="0"
                            value="{{ old('price', $product->price) }}"
                            placeholder="0.00"
                            class="form-control @error('price') is-invalid @enderror"
                            required
                        >

                        <span class="currency-tag font-mono">
                            DA
                        </span>

                    </div>

                    @error('price')

                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>

                    @enderror

                </div>

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <div class="label-with-meta">

                    <label
                        for="description"
                        class="form-label font-mono"
                    >
                        DESCRIPTION TECHNIQUE & COUPE
                    </label>

                    <span class="meta-tag font-mono text-muted">
                        MARKDOWN ACCEPTÉ
                    </span>

                </div>

                <textarea
                    name="description"
                    id="description"
                    rows="5"
                    placeholder="Sweat à capuche coupe oversize en molleton lourd 480 GSM..."
                    class="form-control @error('description') is-invalid @enderror"
                >{{ old('description', $product->description) }}</textarea>

                <span class="field-hint font-mono">
                    Indiquez la coupe, le grammage du tissu et les finitions de la pièce.
                </span>

                @error('description')

                    <span class="error-feedback font-mono">
                        {{ $message }}
                    </span>

                @enderror

            </div>

        </div>


        <!-- =========================================================
             02. PHOTO DE COUVERTURE & MÉDIAS
        ========================================================== -->

        <div class="form-section">

            <div class="section-header-wrap">

                <div class="section-badge-group">

                    <span class="section-badge font-mono">
                        02
                    </span>

                    <h2 class="section-title">
                        PHOTO DE COUVERTURE & MÉDIAS
                    </h2>

                </div>

                <span class="section-tag font-mono text-muted">
                    FORMAT 4:5 RECOMMANDÉ
                </span>

            </div>


            <div class="media-management-grid">

                <!-- PHOTO ACTUELLE -->

                <div class="media-current-card">

                    <span class="media-card-title font-mono">
                        PHOTO DE COUVERTURE ACTUELLE
                    </span>

                    @if($product->image)

                        <div class="current-thumbnail-wrap">

                            <img
                                src="{{ asset($product->image) }}"
                                alt="{{ $product->name }}"
                                class="current-img-preview"
                            >

                            <div class="media-file-info font-mono">

                                <span class="file-status-pill">
                                    ● ACTIF / PRINCIPAL
                                </span>

                                <span class="file-name text-truncate">
                                    {{ basename($product->image) }}
                                </span>

                                <span class="file-meta text-muted">
                                    HAUTE DÉFINITION
                                </span>

                            </div>

                        </div>

                    @else

                        <div class="current-thumbnail-empty font-mono text-muted">

                            <span class="glyph">
                                ▨
                            </span>

                            AUCUNE IMAGE ASSOCIÉE

                        </div>

                    @endif

                </div>


                <!-- REMPLACER PHOTO -->

                <div class="media-replace-card">

                    <span class="media-card-title font-mono">
                        REMPLACER LA PHOTO DE COUVERTURE (OPTIONNEL)
                    </span>

                    <div class="file-drop-zone">

                        <span class="upload-glyph">
                            ☁
                        </span>

                        <label class="custom-file-upload">

                            <input
                                type="file"
                                name="image"
                                id="product-image"
                                accept="image/png, image/jpeg, image/webp"
                                onchange="updateFileName(this)"
                                class="@error('image') is-invalid @enderror"
                            >

                            <span class="btn-browse font-mono">
                                CHOISIR UN FICHIER
                            </span>

                        </label>

                        <span
                            id="file-name-display"
                            class="file-chosen-text font-mono text-muted"
                        >
                            Aucun fichier choisi
                        </span>

                        <span class="upload-specs font-mono text-muted">
                            PNG, JPG OU WEBP. JUSQU'À 12MB. MINIMUM 1600PX LARGEUR.
                        </span>

                    </div>

                    @error('image')

                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>

                    @enderror

                </div>

            </div>

        </div>


        <!-- =========================================================
             03. VARIANTES & STOCKS
        ========================================================== -->

        <div class="form-section">

            @php

                /*
                |--------------------------------------------------------------------------
                | VARIANTES RÉELLES DU PRODUIT
                |--------------------------------------------------------------------------
                */

                $variants = $product->variants ?? collect([]);

                $totalCurrentStock = $variants->sum('stock');

                $standardSizes = [
                    'XS',
                    'S',
                    'M',
                    'L',
                    'XL',
                    'XXL'
                ];

            @endphp


            <div class="section-header-wrap">

                <div class="section-badge-group">

                    <span class="section-badge font-mono">
                        03
                    </span>

                    <h2 class="section-title">
                        DÉCLINAISONS & GESTION DES STOCKS PAR TAILLE
                    </h2>

                </div>

                <span
                    class="stock-total-badge font-mono"
                    id="stock-total-counter"
                >
                    <span class="glyph">
                        ▤
                    </span>

                    {{ $totalCurrentStock }} PCS EN STOCK
                </span>

            </div>


            <p class="section-sub">
                Ajustez le niveau d'inventaire disponible pour chaque variante de taille.
            </p>


            <!-- =====================================================
                 TABLEAU DES VARIANTES
            ====================================================== -->

            <div class="variants-table-wrapper">

                <table class="variants-table font-mono">

                    <thead>

                        <tr>

                            <th>
                                TAILLE
                            </th>

                            <th>
                                QUANTITÉ ACTUELLE
                            </th>

                            <th>
                                RÉASSORT RAPIDE
                            </th>

                            <th class="text-right">
                                DISPONIBILITÉ
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($standardSizes as $size)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | VARIANTE EXISTANTE
                                |--------------------------------------------------------------------------
                                */

                                $variant = $variants->firstWhere('size', $size);

                                /*
                                |--------------------------------------------------------------------------
                                | VALEUR AFFICHÉE
                                |--------------------------------------------------------------------------
                                |
                                | Priorité :
                                | 1. old() après erreur de validation
                                | 2. stock réel de la variante
                                | 3. 0 si aucune variante
                                |
                                */

                                $currentQty = old(
                                    'sizes.' . $size . '.stock',
                                    $variant ? $variant->stock : 0
                                );

                                $currentQty = max(
                                    0,
                                    (int) $currentQty
                                );

                            @endphp


                            <tr class="variant-row">

                                <!-- TAILLE -->

                                <td class="td-size font-bold">

                                    <span class="size-tag">
                                        {{ $size }}
                                    </span>

                                    @if($size === 'M')

                                        <span class="best-seller-tag">
                                            (BESTSELLER)
                                        </span>

                                    @endif

                                </td>


                                <!-- QUANTITÉ -->

                                <td class="td-qty">

                                    <div class="qty-stepper">

                                        <button
                                            type="button"
                                            class="btn-step"
                                            onclick="adjustQty('qty-{{ $size }}', -1)"
                                            aria-label="Diminuer le stock {{ $size }}"
                                        >
                                            -
                                        </button>


                                        <!--
                                            IMPORTANT :
                                            Le backend attend :

                                            sizes[XS][stock]
                                            sizes[S][stock]
                                            sizes[M][stock]
                                            etc.

                                            PAS :

                                            variants[XS]
                                            variants[S]
                                            variants[M]
                                        -->

                                        <input
                                            type="number"
                                            id="qty-{{ $size }}"
                                            name="sizes[{{ $size }}][stock]"
                                            min="0"
                                            value="{{ $currentQty }}"
                                            class="input-qty size-qty-field"
                                            oninput="updateSizeState('{{ $size }}', this.value)"
                                        >


                                        <button
                                            type="button"
                                            class="btn-step"
                                            onclick="adjustQty('qty-{{ $size }}', 1)"
                                            aria-label="Augmenter le stock {{ $size }}"
                                        >
                                            +
                                        </button>

                                    </div>

                                </td>


                                <!-- RÉASSORT RAPIDE -->

                                <td class="td-quick-add">

                                    <button
                                        type="button"
                                        class="btn-quick-add"
                                        onclick="adjustQty('qty-{{ $size }}', 5)"
                                    >
                                        +5 PCS
                                    </button>

                                </td>


                                <!-- DISPONIBILITÉ -->

                                <td class="td-status text-right">

                                    <span
                                        id="badge-{{ $size }}"
                                        class="badge-stock
                                        {{ $currentQty == 0
                                            ? 'badge-stock-out'
                                            : (
                                                $currentQty <= 3
                                                    ? 'badge-stock-low'
                                                    : (
                                                        $currentQty >= 15
                                                            ? 'badge-stock-optimal'
                                                            : 'badge-stock-ok'
                                                    )
                                            )
                                        }}"
                                    >

                                        @if($currentQty == 0)

                                            RUPTURE

                                        @elseif($currentQty <= 3)

                                            STOCK BAS

                                        @elseif($currentQty >= 15)

                                            OPTIMAL

                                        @else

                                            DISPONIBLE

                                        @endif

                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <!-- ERREUR GLOBALE SIZES -->

            @error('sizes')

                <span class="error-feedback font-mono">
                    {{ $message }}
                </span>

            @enderror


            <!-- ERREURS INDIVIDUELLES DES STOCKS -->

            @foreach($standardSizes as $size)

                @error('sizes.' . $size . '.stock')

                    <span class="error-feedback font-mono">
                        Taille {{ $size }} : {{ $message }}
                    </span>

                @enderror

            @endforeach

        </div>


        <!-- =========================================================
             BARRE D'ACTIONS
        ========================================================== -->

        <div class="form-actions-bar font-mono">

            <div class="actions-left">

                <a
                    href="{{ route('admin.products.index') }}"
                    class="btn-cancel"
                >
                    ANNULER
                </a>

            </div>


            <div class="actions-right">

                <button
                    type="submit"
                    class="btn-submit"
                >
                    <span class="glyph">
                        💾
                    </span>

                    ENREGISTRER LES MODIFICATIONS
                </button>

            </div>

        </div>

    </form>

</div>
```

</div>

<style>

/* ==========================================================================
   NOAD ADMIN : MODIFIER LE PRODUIT
   TERRACE BRUTALISM
========================================================================== */

.noad-admin-edit-product-scope {
    width: 100%;
    min-height: 100vh;
    background-color: #0c0c0c;
    color: #e5e5e5;
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    box-sizing: border-box;
    padding-bottom: 80px;
}

.noad-admin-edit-product-scope *,
.noad-admin-edit-product-scope *::before,
.noad-admin-edit-product-scope *::after {
    box-sizing: border-box;
}

.noad-admin-edit-product-scope a {
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
    color: #777777 !important;
}

.text-right {
    text-align: right;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}


/* ==========================================================================
   TOP NAV BAR
========================================================================== */

.top-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 48px;
    border-bottom: 1px solid #1a1a1a;
    background-color: #070707;
    font-size: 11px;
    letter-spacing: 0.12em;
}

.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666666;
}

.breadcrumbs .sep {
    color: #333333;
}

.breadcrumbs .active {
    color: #ffffff;
    font-weight: 700;
}

.system-status {
    display: flex;
    align-items: center;
    gap: 16px;
}

.dot-active {
    color: #d32f2f;
    margin-right: 4px;
    font-size: 9px;
}

.status-tag {
    background-color: #121212;
    border: 1px solid #222222;
    padding: 4px 10px;
    color: #aaaaaa;
    font-size: 10px;
}

.user-badge {
    background-color: #1a1a1a;
    border: 1px solid #2e2e2e;
    color: #ffffff;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 8px;
}


/* ==========================================================================
   CONTAINER
========================================================================== */

.edit-form-container {
    max-width: 980px;
    margin: 0 auto;
    padding: 44px 24px 0 24px;
}


/* ==========================================================================
   EN-TÊTE
========================================================================== */

.form-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 28px;
    gap: 20px;
    flex-wrap: wrap;
}

.header-pretitle {
    font-size: 10px;
    letter-spacing: 0.18em;
    margin-bottom: 6px;
}

.page-title {
    font-size: clamp(34px, 4.5vw, 50px);
    font-weight: 900;
    letter-spacing: 0.02em;
    line-height: 1;
    margin: 0 0 8px 0;
    color: #ffffff;
    text-transform: uppercase;
}

.page-subtitle {
    font-size: 13px;
    color: #888888;
    margin: 0;
}

.btn-preview-link {
    background-color: #111111;
    border: 1px solid #262626;
    color: #cccccc;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 10px 18px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-preview-link:hover {
    color: #ffffff;
    border-color: #666666;
    background-color: #1a1a1a;
}


/* ==========================================================================
   NOTIFICATION
========================================================================== */

.alert-success-box {
    background-color: #0b140e;
    border: 1px solid #1a3823;
    color: #2ecc71;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 30px;
    font-size: 11px;
    letter-spacing: 0.08em;
}

.alert-icon {
    font-size: 16px;
    font-weight: bold;
}

.alert-sub {
    font-size: 10px;
    color: #7fba95;
    margin-top: 2px;
}


/* ==========================================================================
   SECTIONS
========================================================================== */

.form-section {
    border-top: 1px solid #1a1a1a;
    padding-top: 36px;
    margin-bottom: 44px;
}

.section-header-wrap {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 24px;
}

.section-badge-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-badge {
    color: #d32f2f;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    background-color: #1a1010;
    border: 1px solid #3d1b1b;
    padding: 3px 8px;
}

.section-title {
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.04em;
    color: #ffffff;
    text-transform: uppercase;
    margin: 0;
}

.section-tag {
    font-size: 9px;
    letter-spacing: 0.14em;
}

.stock-total-badge {
    background-color: #141414;
    border: 1px solid #282828;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    padding: 6px 12px;
}

.section-sub {
    font-size: 12px;
    color: #777777;
    margin: -14px 0 20px 0;
}


/* ==========================================================================
   FORMULAIRE
========================================================================== */

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 22px;
}

.label-with-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.form-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    color: #cccccc;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.meta-tag {
    font-size: 9px;
    letter-spacing: 0.1em;
}

.form-control {
    width: 100%;
    background-color: #101010;
    border: 1px solid #202020;
    color: #ffffff;
    padding: 13px 16px;
    font-size: 14px;
    font-family: inherit;
    border-radius: 0;
    box-sizing: border-box;
    outline: none;
    transition:
        border-color 0.2s,
        background-color 0.2s;
}

.form-control:focus {
    border-color: #555555;
    background-color: #141414;
}

.form-control::placeholder {
    color: #444444;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
    line-height: 1.5;
}

.field-hint {
    display: block;
    font-size: 10px;
    color: #555555;
    margin-top: 6px;
    letter-spacing: 0.05em;
}

.error-feedback {
    display: block;
    color: #d32f2f;
    font-size: 11px;
    margin-top: 6px;
}


/* ==========================================================================
   SELECT
========================================================================== */

.select-wrapper {
    position: relative;
}

.select-custom {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    padding-right: 40px;
}

.select-arrow {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    color: #666666;
    pointer-events: none;
}


/* ==========================================================================
   PRIX
========================================================================== */

.input-currency-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-currency-wrapper input {
    padding-right: 50px;
}

.currency-tag {
    position: absolute;
    right: 16px;
    font-size: 11px;
    color: #666666;
    font-weight: 700;
    pointer-events: none;
}


/* ==========================================================================
   MÉDIAS
========================================================================== */

.media-management-grid {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 20px;
}

.media-current-card,
.media-replace-card {
    background-color: #0f0f0f;
    border: 1px solid #1c1c1c;
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.media-card-title {
    font-size: 9px;
    letter-spacing: 0.14em;
    color: #777777;
    margin-bottom: 14px;
    display: block;
}

.current-thumbnail-wrap {
    display: flex;
    gap: 16px;
    align-items: center;
}

.current-img-preview {
    width: 80px;
    height: 96px;
    object-fit: cover;
    background-color: #181818;
    border: 1px solid #292929;
    flex-shrink: 0;
}

.media-file-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 10px;
}

.file-status-pill {
    color: #2ecc71;
    font-size: 9px;
    letter-spacing: 0.1em;
    font-weight: bold;
}

.file-name {
    color: #ffffff;
    font-size: 12px;
}

.current-thumbnail-empty {
    padding: 30px 16px;
    text-align: center;
    border: 1px dashed #242424;
    font-size: 11px;
}


/* ==========================================================================
   UPLOAD
========================================================================== */

.file-drop-zone {
    border: 1px dashed #2a2a2a;
    background-color: #0a0a0a;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
}

.upload-glyph {
    font-size: 20px;
    color: #666666;
}

.custom-file-upload input[type="file"] {
    display: none;
}

.btn-browse {
    display: inline-block;
    background-color: #ffffff;
    color: #000000;
    padding: 8px 16px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition:
        background-color 0.2s,
        color 0.2s;
}

.btn-browse:hover {
    background-color: #d32f2f;
    color: #ffffff;
}

.file-chosen-text {
    font-size: 11px;
}

.upload-specs {
    font-size: 8px;
    letter-spacing: 0.08em;
}


/* ==========================================================================
   TABLEAU VARIANTES
========================================================================== */

.variants-table-wrapper {
    background-color: #0f0f0f;
    border: 1px solid #1c1c1c;
    overflow-x: auto;
}

.variants-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}

.variants-table thead tr {
    background-color: #080808;
    border-bottom: 1px solid #202020;
}

.variants-table th {
    padding: 12px 18px;
    font-size: 9px;
    letter-spacing: 0.15em;
    color: #666666;
    text-align: left;
    font-weight: 700;
}

.variants-table tbody tr {
    border-bottom: 1px solid #181818;
    transition: background-color 0.15s;
}

.variants-table tbody tr:hover {
    background-color: #141414;
}

.variants-table td {
    padding: 12px 18px;
    vertical-align: middle;
}

.td-size {
    font-size: 14px;
    color: #ffffff;
}

.best-seller-tag {
    font-size: 9px;
    color: #d32f2f;
    font-weight: normal;
    margin-left: 6px;
}


/* ==========================================================================
   STEPPER
========================================================================== */

.qty-stepper {
    display: inline-flex;
    align-items: center;
    border: 1px solid #242424;
    background-color: #080808;
}

.btn-step {
    background: transparent;
    border: none;
    color: #888888;
    font-size: 14px;
    font-weight: bold;
    width: 28px;
    height: 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition:
        color 0.15s,
        background-color 0.15s;
}

.btn-step:hover {
    color: #ffffff;
    background-color: #1f1f1f;
}

.input-qty {
    width: 44px;
    background: transparent;
    border: none;
    border-left: 1px solid #242424;
    border-right: 1px solid #242424;
    color: #ffffff;
    text-align: center;
    font-family: inherit;
    font-size: 12px;
    font-weight: bold;
    padding: 4px 0;
    outline: none;
}

.btn-quick-add {
    background-color: #151515;
    border: 1px solid #282828;
    color: #aaaaaa;
    font-family: inherit;
    font-size: 9px;
    letter-spacing: 0.1em;
    padding: 6px 12px;
    cursor: pointer;
    transition:
        background-color 0.2s,
        color 0.2s;
}

.btn-quick-add:hover {
    background-color: #ffffff;
    color: #000000;
}


/* ==========================================================================
   BADGES STOCK
========================================================================== */

.badge-stock {
    display: inline-block;
    font-size: 8px;
    letter-spacing: 0.12em;
    padding: 3px 8px;
    font-weight: bold;
}

.badge-stock-out {
    background-color: #210e0e;
    color: #d32f2f;
    border: 1px solid #441818;
}

.badge-stock-low {
    background-color: #241a0b;
    color: #f39c12;
    border: 1px solid #443216;
}

.badge-stock-ok {
    background-color: #0e1c12;
    color: #2ecc71;
    border: 1px solid #1a3823;
}

.badge-stock-optimal {
    background-color: #0a1f1b;
    color: #1abc9c;
    border: 1px solid #124037;
}


/* ==========================================================================
   BARRE D'ACTIONS
========================================================================== */

.form-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #1a1a1a;
    padding-top: 32px;
    margin-top: 24px;
}

.btn-cancel {
    background-color: transparent;
    border: 1px solid #262626;
    color: #888888;
    padding: 12px 24px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    transition:
        color 0.2s,
        border-color 0.2s;
}

.btn-cancel:hover {
    color: #ffffff;
    border-color: #555555;
}

.btn-submit {
    background-color: #d32f2f;
    border: 1px solid #d32f2f;
    color: #ffffff;
    padding: 12px 34px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.14em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition:
        background-color 0.2s,
        border-color 0.2s,
        color 0.2s;
}

.btn-submit:hover {
    background-color: #ffffff;
    border-color: #ffffff;
    color: #000000;
}


/* ==========================================================================
   RESPONSIVE
========================================================================== */

@media (max-width: 860px) {

    .media-management-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 768px) {

    .top-nav-bar {
        padding: 14px 20px;
        gap: 16px;
    }

    .breadcrumbs {
        flex-wrap: wrap;
    }

    .system-status {
        gap: 8px;
    }

    .status-tag {
        display: none;
    }

    .edit-form-container {
        padding: 36px 18px 0 18px;
    }

    .form-row-2 {
        grid-template-columns: 1fr;
    }

    .form-actions-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
        justify-content: center;
        text-align: center;
    }

    .section-header-wrap {
        align-items: flex-start;
        gap: 12px;
        flex-direction: column;
    }

}


@media (max-width: 480px) {

    .top-nav-bar {
        padding: 12px 14px;
    }

    .edit-form-container {
        padding-left: 14px;
        padding-right: 14px;
    }

    .page-title {
        font-size: 34px;
    }

    .section-title {
        font-size: 19px;
    }

    .variants-table th,
    .variants-table td {
        padding: 10px 12px;
    }

}

</style>

<script>

/**
 * ============================================================
 * NOM DU FICHIER IMAGE
 * ============================================================
 */

function updateFileName(input) {

    const display = document.getElementById('file-name-display');

    if (!display) {
        return;
    }

    if (input.files && input.files[0]) {

        display.textContent = input.files[0].name;

        display.classList.remove('text-muted');

        display.style.color = '#ffffff';

    } else {

        display.textContent = 'Aucun fichier choisi';

        display.classList.add('text-muted');

        display.style.color = '';

    }

}


/**
 * ============================================================
 * STEPPER STOCK
 * ============================================================
 */

function adjustQty(inputId, delta) {

    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    let value = parseInt(input.value, 10);

    if (isNaN(value)) {
        value = 0;
    }

    value += delta;

    value = Math.max(0, value);

    input.value = value;


    const size = inputId.replace('qty-', '');

    updateSizeState(size, value);

}


/**
 * ============================================================
 * MISE À JOUR D'UNE TAILLE
 * ============================================================
 */

function updateSizeState(size, value) {

    let qty = parseInt(value, 10);

    if (isNaN(qty)) {
        qty = 0;
    }

    qty = Math.max(0, qty);


    const input = document.getElementById('qty-' + size);

    if (input && parseInt(input.value, 10) < 0) {
        input.value = 0;
    }


    updateBadge(size, qty);

    updateTotalStock();

}


/**
 * ============================================================
 * BADGE DE DISPONIBILITÉ
 * ============================================================
 */

function updateBadge(size, qty) {

    const badge = document.getElementById('badge-' + size);

    if (!badge) {
        return;
    }


    badge.className = 'badge-stock';


    if (qty === 0) {

        badge.classList.add('badge-stock-out');

        badge.textContent = 'RUPTURE';

    } else if (qty <= 3) {

        badge.classList.add('badge-stock-low');

        badge.textContent = 'STOCK BAS';

    } else if (qty >= 15) {

        badge.classList.add('badge-stock-optimal');

        badge.textContent = 'OPTIMAL';

    } else {

        badge.classList.add('badge-stock-ok');

        badge.textContent = 'DISPONIBLE';

    }

}


/**
 * ============================================================
 * COMPTEUR GLOBAL DU STOCK
 * ============================================================
 */

function updateTotalStock() {

    let total = 0;


    document.querySelectorAll('.size-qty-field').forEach(function(field) {

        let value = parseInt(field.value, 10);

        if (isNaN(value)) {
            value = 0;
        }

        total += Math.max(0, value);

    });


    const counter = document.getElementById('stock-total-counter');

    if (!counter) {
        return;
    }


    counter.innerHTML =
        '<span class="glyph">▤</span> ' +
        total +
        ' PCS EN STOCK';

}


/**
 * ============================================================
 * INITIALISATION DES BADGES
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.size-qty-field').forEach(function(input) {

        const size = input.id.replace('qty-', '');

        const value = parseInt(input.value, 10) || 0;

        updateBadge(size, value);

    });


    updateTotalStock();

});

</script>

@endsection
