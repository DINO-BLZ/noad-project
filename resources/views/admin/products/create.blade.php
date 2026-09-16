@extends('layouts.admin')

@section('title', 'Créer un Produit — NOAD Admin')

@section('content')

<div class="noad-admin-create-product-scope">

    <!-- ================= FIL D'ARIANE & STATUT ================= -->
    <div class="top-nav-bar font-mono">

        <div class="breadcrumbs">
            <span>PORTAL</span>
            <span class="sep">/</span>
            <span>CATALOGUE</span>
            <span class="sep">/</span>
            <span class="active">CREER UN PRODUIT</span>
        </div>

        <div class="system-status">
            <span class="status-tag">MODE ÉDITION : ACTIF</span>
            <span class="user-badge">AD</span>
        </div>

    </div>


    <div class="create-form-container">

        <!-- ================= EN-TÊTE DE LA PAGE ================= -->
        <div class="form-header">

            <h1 class="page-title">
                CREER UN PRODUIT
            </h1>

            <p class="page-subtitle">
                Ajoutez un produit, ses informations et ses variantes.
            </p>

        </div>


        <form
            action="{{ route('admin.products.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="product-form"
        >

            @csrf


            <!-- =========================================================
                 01. INFORMATIONS GÉNÉRALES
            ========================================================== -->

            <div class="form-section">

                <div class="section-badge font-mono">
                    01
                </div>

                <h2 class="section-title">
                    INFORMATIONS GÉNÉRALES
                </h2>


                <!-- Nom du produit -->

                <div class="form-group">

                    <label
                        for="name"
                        class="form-label font-mono"
                    >
                        NOM DU PRODUIT
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Ex : Harrington Jacket Black"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >

                    @error('name')
                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- Catégorie -->

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
                                {{ old('category_id') ? '' : 'selected' }}
                            >
                                Sélectionner une catégorie
                            </option>

                            @if(isset($categories) && count($categories) > 0)

                                @foreach($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}
                                    >
                                        {{ strtoupper($category->name) }}
                                    </option>

                                @endforeach

                            @else

                                <option
                                    value="1"
                                    {{ old('category_id') == 1 ? 'selected' : '' }}
                                >
                                    VESTES
                                </option>

                                <option
                                    value="2"
                                    {{ old('category_id') == 2 ? 'selected' : '' }}
                                >
                                    SWEATS & HOODIES
                                </option>

                                <option
                                    value="3"
                                    {{ old('category_id') == 3 ? 'selected' : '' }}
                                >
                                    T-SHIRTS
                                </option>

                                <option
                                    value="4"
                                    {{ old('category_id') == 4 ? 'selected' : '' }}
                                >
                                    PANTALONS & CARGOS
                                </option>

                                <option
                                    value="5"
                                    {{ old('category_id') == 5 ? 'selected' : '' }}
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


                <!-- Prix -->

                <div class="form-group">

                    <label
                        for="price"
                        class="form-label font-mono"
                    >
                        PRIX (DA)
                    </label>

                    <div class="input-currency-wrapper">

                        <input
                            type="number"
                            name="price"
                            id="price"
                            step="100"
                            min="0"
                            value="{{ old('price', '0.00') }}"
                            placeholder="0.00"
                            class="form-control @error('price') is-invalid @enderror"
                            required
                        >

                        <span class="currency-tag font-mono">
                            DZD
                        </span>

                    </div>

                    @error('price')
                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- Description -->

                <div class="form-group">

                    <label
                        for="description"
                        class="form-label font-mono"
                    >
                        DESCRIPTION
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        placeholder="Description du produit..."
                        class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description') }}</textarea>

                    <span class="field-hint font-mono">
                        Indiquez la coupe, la composition textile et l'inspiration NOAD Terrace.
                    </span>

                    @error('description')
                        <span class="error-feedback font-mono">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

            </div>


            <!-- =========================================================
                 02. IMAGE PRINCIPALE
            ========================================================== -->

            <div class="form-section">

                <div class="section-badge font-mono">
                    02
                </div>

                <h2 class="section-title">
                    IMAGE PRINCIPALE
                </h2>

                <div class="file-upload-box">

                    <label class="custom-file-upload">

                        <input
                            type="file"
                            name="image"
                            id="product-image"
                            accept="image/png, image/jpeg, image/webp"
                            onchange="updateFileName(this)"
                            class="@error('image') is-invalid @enderror"
                            required
                        >

                        <span class="btn-browse font-mono">
                            Choisir un fichier
                        </span>

                    </label>

                    <span
                        id="file-name-display"
                        class="file-chosen-text font-mono text-muted"
                    >
                        Aucun fichier choisi
                    </span>

                </div>

                <span class="field-hint font-mono">
                    Formats acceptés : JPG, PNG, WEBP. Résolution recommandée : minimum 1200x1600px.
                </span>

                @error('image')
                    <span class="error-feedback font-mono">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <!-- =========================================================
                 03. VARIANTES DE TAILLES & STOCKS
            ========================================================== -->

            <div class="form-section">

                <div class="section-badge font-mono">
                    03
                </div>

                <h2 class="section-title">
                    DÉCLINAISONS & STOCKS PAR TAILLE
                </h2>

                <p class="section-sub">
                    Définissez la quantité initiale en stock pour chaque taille de cette pièce.
                </p>

                <div class="variants-grid font-mono">

                    @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)

                        <div class="variant-item">

                            <span class="size-label">
                                {{ $size }}
                            </span>

                            <input
                                type="number"
                                name="sizes[{{ $size }}][stock]"
                                min="0"
                                value="{{ old('sizes.' . $size . '.stock', 0) }}"
                                class="variant-input"
                            >

                            <span class="unit-label">
                                PCS
                            </span>

                        </div>

                    @endforeach

                </div>

                @error('sizes')
                    <span class="error-feedback font-mono">
                        {{ $message }}
                    </span>
                @enderror

                @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)

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

                <a
                    href="{{ route('admin.products.index') }}"
                    class="btn-cancel"
                >
                    ANNULER
                </a>

                <button
                    type="submit"
                    class="btn-submit"
                >
                    ENREGISTRER LE PRODUIT
                </button>

            </div>

        </form>

    </div>

</div>


<style>

/* ==========================================================================
   NOAD ADMIN : CREER UN PRODUIT
   TERRACE BRUTALISM
========================================================================== */

.noad-admin-create-product-scope {
    width: 100%;
    min-height: 100vh;
    background-color: #0d0d0d;
    color: #e5e5e5;
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    box-sizing: border-box;
    padding-bottom: 80px;
}

.noad-admin-create-product-scope *,
.noad-admin-create-product-scope *::before,
.noad-admin-create-product-scope *::after {
    box-sizing: border-box;
}

.noad-admin-create-product-scope a {
    color: inherit;
    text-decoration: none;
}

.font-mono {
    font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
}

.text-muted {
    color: #666666 !important;
}


/* ==========================================================================
   TOP NAV BAR / BREADCRUMBS
========================================================================== */

.top-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 48px;
    border-bottom: 1px solid #1a1a1a;
    background-color: #0a0a0a;
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

.status-tag {
    background-color: #141414;
    border: 1px solid #242424;
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
   CONTAINER PRINCIPAL
========================================================================== */

.create-form-container {
    max-width: 920px;
    margin: 0 auto;
    padding: 48px 24px 0 24px;
}


/* ==========================================================================
   EN-TÊTE
========================================================================== */

.form-header {
    margin-bottom: 36px;
}

.page-title {
    font-size: clamp(38px, 5vw, 56px);
    font-weight: 900;
    letter-spacing: 0.02em;
    line-height: 1;
    margin: 0 0 10px 0;
    color: #ffffff;
    text-transform: uppercase;
}

.page-subtitle {
    font-size: 14px;
    color: #888888;
    margin: 0;
    font-weight: 400;
}


/* ==========================================================================
   SECTIONS
========================================================================== */

.form-section {
    border-top: 1px solid #1a1a1a;
    padding-top: 36px;
    margin-bottom: 40px;
}

.section-badge {
    color: #d32f2f;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 6px;
}

.section-title {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 0.04em;
    color: #ffffff;
    text-transform: uppercase;
    margin: 0 0 24px 0;
}

.section-sub {
    font-size: 12px;
    color: #777777;
    margin: -16px 0 20px 0;
}


/* ==========================================================================
   FORM GROUPS & INPUTS
========================================================================== */

.form-group {
    margin-bottom: 24px;
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

.form-control {
    width: 100%;
    background-color: #121212;
    border: 1px solid #202020;
    color: #ffffff;
    padding: 14px 16px;
    font-size: 14px;
    font-family: inherit;
    border-radius: 0;
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: #555555;
    background-color: #151515;
}

.form-control::placeholder {
    color: #444444;
}

textarea.form-control {
    resize: vertical;
    min-height: 110px;
}

.field-hint {
    display: block;
    font-size: 10px;
    color: #555555;
    margin-top: 8px;
    letter-spacing: 0.05em;
}

.error-feedback {
    display: block;
    color: #d32f2f;
    font-size: 11px;
    margin-top: 6px;
}


/* ==========================================================================
   SELECTEUR PERSONNALISE
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
   CHAMP PRIX
========================================================================== */

.input-currency-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-currency-wrapper input {
    padding-right: 60px;
}

.currency-tag {
    position: absolute;
    right: 16px;
    font-size: 11px;
    color: #555555;
    font-weight: 700;
    letter-spacing: 0.1em;
    pointer-events: none;
}


/* ==========================================================================
   UPLOAD FICHIER
========================================================================== */

.file-upload-box {
    display: flex;
    align-items: center;
    gap: 16px;
    background-color: #101010;
    border: 1px dashed #222222;
    padding: 16px 20px;
    margin-bottom: 8px;
}

.custom-file-upload input[type="file"] {
    display: none;
}

.btn-browse {
    display: inline-block;
    background-color: #ffffff;
    color: #000000;
    padding: 9px 18px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
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
    font-size: 12px;
}


/* ==========================================================================
   GRILLE DES VARIANTES
========================================================================== */

.variants-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.variant-item {
    background-color: #121212;
    border: 1px solid #202020;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.size-label {
    font-size: 12px;
    font-weight: 800;
    color: #ffffff;
}

.variant-input {
    width: 100%;
    background-color: #0a0a0a;
    border: 1px solid #2a2a2a;
    color: #ffffff;
    text-align: center;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    padding: 6px 2px;
    outline: none;
}

.variant-input:focus {
    border-color: #d32f2f;
}

.unit-label {
    font-size: 8px;
    color: #666666;
    letter-spacing: 0.1em;
}


/* ==========================================================================
   BARRE DE SOUMISSION
========================================================================== */

.form-actions-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 16px;
    border-top: 1px solid #1a1a1a;
    padding-top: 32px;
    margin-top: 20px;
}

.btn-cancel {
    background-color: transparent;
    border: 1px solid #2a2a2a;
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
    padding: 12px 32px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.14em;
    cursor: pointer;
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

    .create-form-container {
        padding: 36px 18px 0 18px;
    }

    .page-title {
        font-size: 40px;
    }

    .variants-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-actions-bar {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
        text-align: center;
    }

}


@media (max-width: 480px) {

    .top-nav-bar {
        padding: 12px 14px;
    }

    .create-form-container {
        padding-left: 14px;
        padding-right: 14px;
    }

    .page-title {
        font-size: 34px;
    }

    .section-title {
        font-size: 20px;
    }

    .variants-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

</style>


<script>

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

</script>

@endsection