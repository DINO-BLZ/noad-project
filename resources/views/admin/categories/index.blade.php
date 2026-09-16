@extends('layouts.admin')

@section('content')

<div class="admin-categories-page">

    {{-- =========================================================
         01. BARRE SYSTÈME
    ========================================================== --}}

    <div class="terminal-status-strip">

        <div class="status-left">
            <span class="status-dot"></span>

            <span class="terminal-id">
                SYS.TERMINAL // ALGER-01 ACTIVE
            </span>

            <span class="node-id">
                NODE: CAT-TAX-BUILD-4.8
            </span>
        </div>

        <div class="status-right">
            <span class="terminal-slogan">
                DEFEND YOUR PRINCIPLE
            </span>

            <span class="terminal-rev">
                REV. 2025.01
            </span>
        </div>

    </div>


    {{-- =========================================================
         02. EN-TÊTE PRINCIPAL
    ========================================================== --}}

    <header class="cat-header">

        <div class="cat-header-left">

            <span class="protocol-tag">
                NOAD PROTOCOL // CATALOGUE TAXONOMY // SYS_ADMIN ALGER-01
            </span>

            <h1 class="cat-page-title">
                GESTION &amp; CRÉATION DES CATÉGORIES // FAMILLES TEXTILES
            </h1>

            <p class="cat-page-lead">
                Architecture des rayons du vestiaire NOAD,
                arborescence des collections permanentes et quotas
                d'attribution atelier.
            </p>

        </div>

        <div class="cat-header-actions">

            <button
                type="button"
                class="btn-tax-outline"
                id="reorganize-categories"
            >
                <span class="btn-icon">⇅</span>
                RÉORGANISER L'ORDRE
            </button>

            <button
                type="button"
                class="btn-tax-outline"
                id="export-categories"
            >
                <span class="btn-icon">⤓</span>
                EXPORT ARBORESCENCE
            </button>

            <a
                href="#create-category-block"
                class="btn-tax-solid"
            >
                + NOUVELLE CATÉGORIE
            </a>

        </div>

    </header>


    {{-- =========================================================
         03. MESSAGES SESSION
    ========================================================== --}}

    @if(session('success'))

        <div class="category-alert category-alert-success">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>

    @endif

    @if(session('error'))

        <div class="category-alert category-alert-error">
            <span>!</span>
            <span>{{ session('error') }}</span>
        </div>

    @endif

    @if($errors->any())

        <div class="category-alert category-alert-error">

            <div>
                <strong>ERREUR DE VALIDATION</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        </div>

    @endif


    {{-- =========================================================
         04. KPIs
    ========================================================== --}}

    <section class="tax-kpi-grid">

        {{-- KPI 01 --}}
        <div class="tax-kpi-card">

            <div class="kpi-head">

                <span class="kpi-num">
                    01 //
                </span>

                <span class="kpi-lbl">
                    FAMILLES ACTIVES
                </span>

                <span class="kpi-glyph">
                    ⬡
                </span>

            </div>

            <div class="kpi-body">

                <span class="kpi-val">
                    {{ sprintf('%02d', $activeCategoriesCount ?? (isset($categories) ? $categories->count() : 0)) }}
                </span>

                <span class="kpi-sub">
                    SEGMENTS VALIDES
                </span>

            </div>

            <span class="kpi-foot">
                Catégories actuellement enregistrées
            </span>

        </div>


        {{-- KPI 02 --}}
        <div class="tax-kpi-card">

            <div class="kpi-head">

                <span class="kpi-num">
                    02 //
                </span>

                <span class="kpi-lbl">
                    ARTICLES RATTACHÉS
                </span>

                <span class="kpi-glyph">
                    🗂
                </span>

            </div>

            <div class="kpi-body">

                <span class="kpi-val">
                    {{ $totalProducts ?? 0 }}
                </span>

                <span class="kpi-sub">
                    PRODUITS EN LIGNE
                </span>

            </div>

            <span class="kpi-foot">
                Produits actuellement associés aux familles
            </span>

        </div>


        {{-- KPI 03 --}}
        <div class="tax-kpi-card">

            <div class="kpi-head">

                <span class="kpi-num">
                    03 //
                </span>

                <span class="kpi-lbl">
                    CATÉGORIE PHARE
                </span>

                <span class="kpi-glyph alert">
                    📈
                </span>

            </div>

            <div class="kpi-body">

                <span class="kpi-val alert">
                    {{ $featuredCategoryShare ?? 0 }}%
                </span>

                <span class="kpi-sub">
                    DU VOLUME TOTAL
                </span>

            </div>

            <span class="kpi-foot">
                {{ $featuredCategoryName ?? 'Aucune catégorie disponible' }}
            </span>

        </div>


        {{-- KPI 04 --}}
        <div class="tax-kpi-card">

            <div class="kpi-head">

                <span class="kpi-num">
                    04 //
                </span>

                <span class="kpi-lbl">
                    STATUT ROUTAGE
                </span>

                <span class="kpi-glyph dot-status">
                    ■
                </span>

            </div>

            <div class="kpi-body">

                <span class="kpi-val">
                    SYNC
                </span>

                <span class="kpi-sub green">
                    TEMPS RÉEL
                </span>

            </div>

            <span class="kpi-foot">
                Boutique &amp; Drops connectés au registre
            </span>

        </div>

    </section>


    {{-- =========================================================
         05. CONTENU PRINCIPAL
    ========================================================== --}}

    <div class="tax-main-layout">


        {{-- =====================================================
             05A. FORMULAIRE
        ====================================================== --}}

        <section
            class="tax-form-col"
            id="create-category-block"
        >

            <div class="form-container-card">

                <div class="card-head-row">

                    <div>

                        <span class="sub-head-badge">
                            PARAMÈTRES TAXONOMIE
                        </span>

                        <h2 class="card-box-title">
                            01 // CRÉER UNE NOUVELLE FAMILLE TEXTILE
                        </h2>

                    </div>

                    <span class="mode-status-tag">
                        MODE: ÉCRITURE
                    </span>

                </div>


                <p class="form-desc-lead">
                    Définissez l'ancrage algorithmique et l'ordre
                    d'affichage dans le catalogue NOAD Alger.
                </p>


                {{-- FORMULAIRE --}}

                <form
                    action="{{ route('admin.categories.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="category-create-form"
                >

                    @csrf


                    {{-- NOM --}}

                    <div class="input-form-group">

                        <label for="category-name">
                            NOM DE LA CATÉGORIE *
                        </label>

                        <input
                            type="text"
                            id="category-name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                            placeholder="Ex: SURCHEMISES & OVERSHIRTS"
                        >

                        <span class="input-caption">
                            Affiché sur le store front-end et le rail de filtres.
                        </span>

                    </div>


                    {{-- SLUG --}}

                    <div class="input-form-group">

                        <div class="label-with-meta">

                            <label for="category-slug">
                                SLUG URL NORMALISÉ *
                            </label>

                            <span class="meta-indicator">
                                AUTO-GÉNÉRÉ
                            </span>

                        </div>

                        <div class="slug-input-wrapper">

                            <span class="slug-url-base">
                                noad.dz/boutique/
                            </span>

                            <input
                                type="text"
                                id="category-slug"
                                name="slug"
                                value="{{ old('slug') }}"
                                required
                                maxlength="255"
                                placeholder="surchemises-overshirts"
                            >

                        </div>

                    </div>


                    {{-- PARENT + ORDRE --}}

                    <div class="input-grid-twin">

                        <div class="input-form-group">

                            <label for="parent_category">
                                FAMILLE PARENTE
                            </label>

                            <div class="custom-select-box">

                                <select
                                    id="parent_category"
                                    name="parent_id"
                                >

                                    <option value="">
                                        RACINE // VESTIAIRE PRINCIPAL
                                    </option>

                                    @foreach(($parentCategories ?? collect()) as $parent)

                                        <option
                                            value="{{ $parent->id }}"
                                            @selected(old('parent_id') == $parent->id)
                                        >
                                            {{ $parent->name }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>


                        <div class="input-form-group">

                            <label for="sort_order">
                                ORDRE D'AFFICHAGE
                            </label>

                            <input
                                type="number"
                                id="sort_order"
                                name="sort_order"
                                value="{{ old('sort_order', 1) }}"
                                min="0"
                                class="input-text-center"
                            >

                        </div>

                    </div>


                    {{-- DESCRIPTION --}}

                    <div class="input-form-group">

                        <label for="category-manifesto">
                            MANIFESTE TEXTILE DE LA FAMILLE
                        </label>

                        <textarea
                            id="category-manifesto"
                            name="description"
                            rows="4"
                            maxlength="300"
                            placeholder="Description technique..."
                        >{{ old('description') }}</textarea>

                        <div class="textarea-footer-meta">

                            <span>
                                Caractère brut, technique et fonctionnel.
                            </span>

                            <span
                                class="count-chars"
                                id="description-counter"
                            >
                                0/300
                            </span>

                        </div>

                    </div>


                    {{-- BANNER --}}

                    <div class="input-form-group">

                        <div class="label-with-meta">

                            <label for="category-banner">
                                VISUEL D'EN-TÊTE DE RAYON (16:9)
                            </label>

                            <span class="meta-indicator">
                                SPECS: 1920X1080 MAX 3MB
                            </span>

                        </div>


                        <label
                            class="dropzone-area"
                            for="category-banner"
                        >

                            <input
                                type="file"
                                name="banner"
                                id="category-banner"
                                accept="image/jpeg,image/png,image/webp"
                                class="file-hidden"
                            >

                            <div class="dropzone-preview-wrap">

                                <div
                                    class="dropzone-art-mock"
                                    id="banner-preview"
                                >

                                    <span
                                        class="mock-asset-code"
                                        id="banner-label"
                                    >
                                        IMAGE TEMP: OVER_07.RAW
                                    </span>

                                </div>

                                <span class="dropzone-action-text">

                                    <span class="icon-plus">
                                        +
                                    </span>

                                    GLISSER LE FICHIER OU PARCOURIR
                                    LES DOSSIERS ATELIER

                                </span>

                            </div>

                        </label>

                    </div>


                    {{-- FLAGS --}}

                    <div class="flags-settings-block">

                        <span class="flags-group-title">
                            FLAGS D'INDEXATION &amp; ACCESSIBILITÉ
                        </span>


                        {{-- NAVIGATION --}}

                        <div class="flag-toggle-row">

                            <div class="flag-text-col">

                                <span class="flag-name">
                                    VISIBLE DANS LA NAVIGATION PRINCIPALE
                                </span>

                                <span class="flag-sub">
                                    Afficher immédiatement dans la barre d'onglets
                                    du haut et pied de page.
                                </span>

                            </div>

                            <label class="toggle-switch">

                                <input
                                    type="checkbox"
                                    name="is_visible_nav"
                                    value="1"
                                    @checked(old('is_visible_nav', true))
                                >

                                <span class="toggle-slider"></span>

                            </label>

                        </div>


                        {{-- FILTRES --}}

                        <div class="flag-toggle-row">

                            <div class="flag-text-col">

                                <span class="flag-name">
                                    ACTIVER DANS LES FILTRES BOUTIQUE
                                </span>

                                <span class="flag-sub">
                                    Injecter comme facette sélectionnable
                                    sur l'ensemble du vestiaire.
                                </span>

                            </div>

                            <label class="toggle-switch">

                                <input
                                    type="checkbox"
                                    name="is_filterable"
                                    value="1"
                                    @checked(old('is_filterable', true))
                                >

                                <span class="toggle-slider"></span>

                            </label>

                        </div>


                        {{-- RESTRICTION --}}

                        <div class="flag-toggle-row">

                            <div class="flag-text-col">

                                <span class="flag-name">
                                    ACCÈS RÉSERVÉ WHITELIST / DROP ONLY
                                </span>

                                <span class="flag-sub">
                                    Verrouiller l'accès aux abonnés détenteurs
                                    d'un pass de session actif.
                                </span>

                            </div>

                            <label class="toggle-switch">

                                <input
                                    type="checkbox"
                                    name="is_restricted"
                                    value="1"
                                    @checked(old('is_restricted'))
                                >

                                <span class="toggle-slider"></span>

                            </label>

                        </div>

                    </div>


                    {{-- ACTIONS --}}

                    <div class="form-submit-row">

                        <button
                            type="reset"
                            class="btn-form-reset"
                        >
                            RÉINITIALISER
                        </button>

                        <button
                            type="submit"
                            class="btn-form-save"
                        >

                            <span class="save-icon">
                                ✓
                            </span>

                            ENREGISTRER LA CATÉGORIE

                        </button>

                    </div>

                </form>

            </div>

        </section>


        {{-- =====================================================
             05B. TABLEAU
        ====================================================== --}}

        <section class="tax-table-col">

            <div class="table-container-card">

                <div class="card-head-row">

                    <div>

                        <span class="sub-head-badge">
                            INVENTAIRE CENTRAL
                        </span>

                        <h2 class="card-box-title">
                            02 // REGISTRE DES CATÉGORIES EN COURS
                            ({{ isset($categories) ? $categories->count() : 0 }})
                        </h2>

                    </div>


                    <div class="table-filter-input-wrap">

                        <input
                            type="search"
                            id="category-filter"
                            placeholder="FILTRER REGISTRE..."
                            class="input-table-filter"
                            autocomplete="off"
                        >

                        <span class="filter-icon">
                            ⚙
                        </span>

                    </div>

                </div>


                {{-- TABLE --}}

                <div class="table-responsive-wrapper">

                    <table class="category-data-table">

                        <thead>

                            <tr>

                                <th>
                                    ORDRE
                                </th>

                                <th>
                                    FAMILLE &amp; SLUG
                                </th>

                                <th>
                                    PARENT
                                </th>

                                <th>
                                    PIÈCES
                                </th>

                                <th>
                                    STATUT
                                </th>

                                <th class="text-right">
                                    MODÉRATION
                                </th>

                            </tr>

                        </thead>


                        <tbody id="category-table-body">

                            @forelse(($categories ?? collect()) as $category)

                                <tr
                                    class="category-table-row"
                                    data-search="{{ strtolower($category->name . ' ' . $category->slug) }}"
                                >

                                    {{-- ORDRE --}}

                                    <td>

                                        <span class="order-cell-idx">

                                            {{ str_pad(
                                                $category->sort_order,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- IDENTITÉ --}}

                                    <td>

                                        <div class="cat-identity-cell">

                                            <div class="cat-icon-symbol">

                                                {{ $category->icon ?? '⬡' }}

                                            </div>


                                            <div class="cat-name-stack">

                                                <span class="cat-name-text">

                                                    {{ $category->name }}

                                                </span>

                                                <span class="cat-slug-text">

                                                    slug: {{ $category->slug }}

                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- PARENT --}}

                                    <td>

                                        @if($category->parent)

                                            <span class="cat-parent-tag">

                                                {{ $category->parent->name }}

                                            </span>

                                        @else

                                            <span class="cat-parent-tag">

                                                RACINE STORE

                                            </span>

                                        @endif

                                    </td>


                                    {{-- PRODUITS --}}

                                    <td>

                                        <div class="cat-sku-stack">

                                            <span class="sku-number">

                                                {{ $category->products_count ?? $category->products()->count() }}
                                                SKU

                                            </span>

                                            @if(($totalProducts ?? 0) > 0)

                                                <span class="sku-percentage">

                                                    {{ round(
                                                        (($category->products_count ?? $category->products()->count()) / $totalProducts) * 100
                                                    ) }}%
                                                    SHARE

                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- STATUS --}}

                                    <td>

                                        @if($category->is_restricted)

                                            <span class="status-badge-live restricted">
                                                RESTREINT
                                            </span>

                                        @else

                                            <span class="status-badge-live online">
                                                EN LIGNE
                                            </span>

                                        @endif

                                    </td>


                                    {{-- ACTIONS --}}

                                    <td class="text-right">

                                        <div class="table-action-buttons">

                                            @if(Route::has('admin.categories.edit'))

                                                <a
                                                    href="{{ route('admin.categories.edit', $category) }}"
                                                    class="btn-row-action"
                                                    title="Éditer"
                                                    aria-label="Éditer {{ $category->name }}"
                                                >
                                                    ✏
                                                </a>

                                            @endif


                                            @if(Route::has('admin.categories.show'))

                                                <a
                                                    href="{{ route('admin.categories.show', $category) }}"
                                                    class="btn-row-action"
                                                    title="Prévisualiser"
                                                    aria-label="Prévisualiser {{ $category->name }}"
                                                >
                                                    ↗
                                                </a>

                                            @endif


                                            @if(Route::has('admin.categories.destroy'))

                                                <form
                                                    action="{{ route('admin.categories.destroy', $category) }}"
                                                    method="POST"
                                                    class="inline-form"
                                                    onsubmit="return confirm('Archiver cette catégorie ?');"
                                                >

                                                    @csrf

                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn-row-action"
                                                        title="Archiver"
                                                        aria-label="Archiver {{ $category->name }}"
                                                    >
                                                        🗂
                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="empty-category-state"
                                    >

                                        AUCUNE CATÉGORIE ENREGISTRÉE.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- =================================================
                     DISTRIBUTION
                ================================================== --}}

                <div class="distribution-footer-panel">

                    <div class="distribution-header-row">

                        <span class="dist-caption">
                            RÉPARTITION DU CATALOGUE EN ATELIER //
                            % VOLUME PRODUITS
                        </span>

                        <span class="dist-max-capacity">
                            CAPACITÉ TOTALE :
                            {{ $totalProducts ?? 0 }} / 50 SKU
                        </span>

                    </div>


                    <div class="distribution-multi-bar">

                        @foreach(($distribution ?? []) as $item)

                            <div
                                class="bar-segment"
                                style="width: {{ $item['percentage'] ?? 0 }}%;"
                            ></div>

                        @endforeach

                        @if(empty($distribution))

                            <div
                                class="bar-segment"
                                style="width: 100%;"
                            ></div>

                        @endif

                    </div>


                    <div class="distribution-legend-row">

                        @foreach(($distribution ?? []) as $item)

                            <span class="legend-item">

                                <span class="color-dot">
                                    ■
                                </span>

                                {{ strtoupper($item['name'] ?? 'CATÉGORIE') }}
                                ({{ $item['percentage'] ?? 0 }}%)

                            </span>

                        @endforeach

                    </div>

                </div>

            </div>

        </section>

    </div>


    {{-- =========================================================
         06. AUDIT & TRAÇABILITÉ
    ========================================================== --}}

    <footer class="tax-audit-section">

        <div class="audit-manifest-summary">

            <span class="audit-sub-tag">
                AUDIT DE CONFORMITÉ INDEXATION
            </span>

            <h3 class="audit-headline">
                TRAÇABILITÉ &amp; STRUCTURE DES RAYONS NOAD
            </h3>

            <p class="audit-text">
                Toutes les modifications taxonomiques déclenchent
                une recompilation des métadonnées OpenGraph et des
                sitemaps XML pour la zone Algérie &amp; international.
            </p>

        </div>


        <div class="audit-nodes-stack">


            {{-- OPEN GRAPH --}}

            <div class="audit-node-box">

                <div class="node-head">

                    <span class="node-title">
                        OPEN GRAPH TAGS
                    </span>

                    <span class="node-icon">
                        🛡
                    </span>

                </div>

                <span class="node-val">
                    GÉNÉRATION INSTANTANÉE
                </span>

                <span class="node-sub">
                    Image 1200x630 auto-mappée sur le serveur d'Alger.
                </span>

            </div>


            {{-- CANONICAL --}}

            <div class="audit-node-box">

                <div class="node-head">

                    <span class="node-title">
                        CANONICAL &amp; MULTI-LANGUE
                    </span>

                    <span class="node-icon">
                        🌐
                    </span>

                </div>

                <span class="node-val">
                    HREFLANG ACTIF
                </span>

                <span class="node-sub">
                    FR-DZ prioritaire avec fallback direct EN-GLOBAL.
                </span>

            </div>


            {{-- CACHE --}}

            <div class="audit-node-box">

                <div class="node-head">

                    <span class="node-title">
                        CACHE REVALIDATION
                    </span>

                    <span class="node-icon">
                        ↺
                    </span>

                </div>

                <span class="node-val">
                    TTL 60 SECONDES
                </span>

                <span class="node-sub">
                    Purge automatique lors de la création d'une famille.
                </span>

            </div>


        </div>

    </footer>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | SLUG AUTOMATIQUE
    |--------------------------------------------------------------------------
    */

    const nameInput = document.getElementById('category-name');
    const slugInput = document.getElementById('category-slug');

    if (nameInput && slugInput) {

        let slugManuallyEdited = false;

        slugInput.addEventListener('input', function () {
            slugManuallyEdited = true;
        });

        nameInput.addEventListener('input', function () {

            if (slugManuallyEdited) {
                return;
            }

            let slug = this.value
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim()
                .replace(/&/g, ' et ')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | COMPTEUR DESCRIPTION
    |--------------------------------------------------------------------------
    */

    const description = document.getElementById('category-manifesto');
    const counter = document.getElementById('description-counter');

    function updateDescriptionCounter() {

        if (!description || !counter) {
            return;
        }

        const length = description.value.length;

        counter.textContent = length + '/300';

        if (length >= 280) {
            counter.classList.add('counter-warning');
        } else {
            counter.classList.remove('counter-warning');
        }
    }

    if (description) {

        description.addEventListener(
            'input',
            updateDescriptionCounter
        );

        updateDescriptionCounter();
    }


    /*
    |--------------------------------------------------------------------------
    | PRÉVISUALISATION BANNER
    |--------------------------------------------------------------------------
    */

    const bannerInput = document.getElementById('category-banner');
    const bannerPreview = document.getElementById('banner-preview');
    const bannerLabel = document.getElementById('banner-label');

    if (bannerInput && bannerPreview && bannerLabel) {

        bannerInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                bannerPreview.style.backgroundImage =
                    'url("' + event.target.result + '")';

                bannerPreview.style.backgroundSize = 'cover';
                bannerPreview.style.backgroundPosition = 'center';

                bannerLabel.textContent =
                    file.name.toUpperCase();

            };

            reader.readAsDataURL(file);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRE CATÉGORIES
    |--------------------------------------------------------------------------
    */

    const filterInput =
        document.getElementById('category-filter');

    const rows =
        document.querySelectorAll('.category-table-row');

    if (filterInput) {

        filterInput.addEventListener('input', function () {

            const search =
                this.value.toLowerCase().trim();

            rows.forEach(function (row) {

                const content =
                    row.dataset.search || '';

                row.style.display =
                    content.includes(search)
                        ? ''
                        : 'none';

            });
        });
    }


    /*
    |--------------------------------------------------------------------------
    | RESET FORM
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById('category-create-form');

    if (form) {

        form.addEventListener('reset', function () {

            setTimeout(function () {

                updateDescriptionCounter();

                if (bannerPreview) {

                    bannerPreview.style.backgroundImage = '';

                    bannerLabel.textContent =
                        'IMAGE TEMP: OVER_07.RAW';

                }

            }, 0);

        });
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT ARBORESCENCE
    |--------------------------------------------------------------------------
    */

    const exportButton =
        document.getElementById('export-categories');

    if (exportButton) {

        exportButton.addEventListener('click', function () {

            const rows =
                document.querySelectorAll(
                    '.category-table-row'
                );

            let output =
                'NOAD // CATALOGUE TAXONOMY\n\n';

            rows.forEach(function (row) {

                if (row.style.display === 'none') {
                    return;
                }

                const order =
                    row.querySelector(
                        '.order-cell-idx'
                    )?.textContent.trim() || '';

                const name =
                    row.querySelector(
                        '.cat-name-text'
                    )?.textContent.trim() || '';

                const slug =
                    row.querySelector(
                        '.cat-slug-text'
                    )?.textContent.trim() || '';

                output +=
                    order +
                    ' // ' +
                    name +
                    ' // ' +
                    slug +
                    '\n';

            });

            const blob =
                new Blob(
                    [output],
                    { type: 'text/plain;charset=utf-8' }
                );

            const url =
                URL.createObjectURL(blob);

            const link =
                document.createElement('a');

            link.href = url;

            link.download =
                'noad-categories.txt';

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            URL.revokeObjectURL(url);
        });
    }

});
</script>


{{-- =============================================================
     CSS
============================================================= --}}

<style>

.admin-categories-page {

    width: 100%;
    max-width: 1440px;

    margin: 0 auto;

    padding:
        16px
        24px
        80px
        24px;

    box-sizing: border-box;

    color:
        var(--text, #e5e5e5);

    font-family:
        'Barlow Condensed',
        -apple-system,
        BlinkMacSystemFont,
        sans-serif;

    background-color:
        #0c0c0c;

}

.admin-categories-page *,
.admin-categories-page *::before,
.admin-categories-page *::after {

    box-sizing: border-box;

}

.admin-categories-page a {

    color: inherit;

    text-decoration: none;

}

.admin-categories-page button,
.admin-categories-page input,
.admin-categories-page select,
.admin-categories-page textarea {

    font: inherit;

}


/* =============================================================
   ALERTS
============================================================= */

.category-alert {

    display: flex;

    align-items: flex-start;

    gap: 10px;

    margin-bottom: 20px;

    padding: 12px 14px;

    border: 1px solid
        var(--border, #242424);

    background: #101010;

    font-family: monospace;

    font-size: 10px;

    letter-spacing: .05em;

}

.category-alert-success {

    color: #2ecc71;

    border-color:
        rgba(46, 204, 113, .35);

}

.category-alert-error {

    color:
        var(--accent, #d32f2f);

    border-color:
        rgba(211, 47, 47, .35);

}

.category-alert ul {

    margin: 8px 0 0 16px;

    padding: 0;

}


/* =============================================================
   TERMINAL
============================================================= */

.terminal-status-strip {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid
        var(--border, #242424);

    padding-bottom: 10px;

    margin-bottom: 24px;

    font-family: monospace;

    font-size: 10px;

    letter-spacing: .1em;

    flex-wrap: wrap;

    gap: 12px;

}

.status-left {

    display: flex;

    align-items: center;

    gap: 12px;

}

.status-dot {

    width: 6px;

    height: 6px;

    background:
        var(--accent, #d32f2f);

    display: inline-block;

}

.terminal-id {

    color: #fff;

    font-weight: bold;

}

.node-id {

    color: #666;

}

.status-right {

    display: flex;

    align-items: center;

    gap: 16px;

}

.terminal-slogan {

    color: #888;

    letter-spacing: .15em;

    font-weight: 700;

}

.terminal-rev {

    color: #555;

}


/* =============================================================
   HEADER
============================================================= */

.cat-header {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    border-bottom:
        1px solid
        var(--border, #242424);

    padding-bottom: 24px;

    margin-bottom: 28px;

    flex-wrap: wrap;

    gap: 20px;

}

.cat-header-left {

    max-width: 820px;

}

.protocol-tag {

    font-family: monospace;

    font-size: 10px;

    letter-spacing: .15em;

    color:
        var(--accent, #d32f2f);

    font-weight: bold;

    display: block;

    margin-bottom: 8px;

}

.cat-page-title {

    font-size:
        clamp(26px, 3.8vw, 42px);

    font-weight: 900;

    letter-spacing: .03em;

    line-height: 1.08;

    margin: 0 0 8px;

    color: #fff;

    text-transform: uppercase;

}

.cat-page-lead {

    font-size: 13px;

    color: #888;

    margin: 0;

    line-height: 1.45;

}

.cat-header-actions {

    display: flex;

    gap: 10px;

    align-items: center;

    flex-wrap: wrap;

}

.btn-tax-outline {

    background: #101010;

    border:
        1px solid
        var(--border, #242424);

    color: #ccc;

    padding: 10px 14px;

    font-family: monospace;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: .1em;

    cursor: pointer;

    transition: all .2s;

    display: flex;

    align-items: center;

    gap: 6px;

}

.btn-tax-outline:hover {

    color: #fff;

    border-color: #666;

}

.btn-tax-solid {

    background: #fff;

    color: #000;

    border: 1px solid #fff;

    padding: 11px 18px;

    font-size: 11px;

    font-weight: 900;

    letter-spacing: .12em;

    cursor: pointer;

    transition: all .2s;

    display: inline-block;

}

.btn-tax-solid:hover {

    background:
        var(--accent, #d32f2f);

    border-color:
        var(--accent, #d32f2f);

    color: #fff;

}


/* =============================================================
   KPI
============================================================= */

.tax-kpi-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 16px;

    margin-bottom: 28px;

}

.tax-kpi-card {

    background: #101010;

    border:
        1px solid
        var(--border, #242424);

    padding: 18px 20px;

    display: flex;

    flex-direction: column;

    justify-content: space-between;

    min-height: 140px;

}

.kpi-head {

    display: flex;

    align-items: center;

    gap: 6px;

    margin-bottom: 8px;

}

.kpi-num {

    font-family: monospace;

    font-size: 10px;

    color:
        var(--accent, #d32f2f);

    font-weight: bold;

}

.kpi-lbl {

    font-family: monospace;

    font-size: 9px;

    color: #888;

    letter-spacing: .1em;

    flex: 1;

}

.kpi-glyph {

    font-size: 11px;

    opacity: .5;

}

.kpi-glyph.alert {

    color:
        var(--accent, #d32f2f);

    opacity: 1;

}

.kpi-glyph.dot-status {

    color:
        var(--accent, #d32f2f);

    font-size: 8px;

    opacity: 1;

}

.kpi-body {

    display: flex;

    align-items: baseline;

    gap: 8px;

    margin-bottom: 6px;

}

.kpi-val {

    font-family: monospace;

    font-size: 28px;

    font-weight: 900;

    color: #fff;

    line-height: 1;

}

.kpi-val.alert {

    color:
        var(--accent, #d32f2f);

}

.kpi-sub {

    font-family: monospace;

    font-size: 9px;

    color: #666;

    letter-spacing: .08em;

}

.kpi-sub.green {

    color: #2ecc71;

    font-weight: bold;

}

.kpi-foot {

    font-size: 11px;

    color: #777;

    border-top:
        1px solid #181818;

    padding-top: 8px;

}


/* =============================================================
   MAIN LAYOUT
============================================================= */

.tax-main-layout {

    display: grid;

    grid-template-columns:
        .95fr 1.05fr;

    gap: 28px;

    align-items: flex-start;

    margin-bottom: 40px;

}

.form-container-card,
.table-container-card {

    background: #101010;

    border:
        1px solid
        var(--border, #242424);

    padding: 24px;

}

.card-head-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid
        var(--border, #242424);

    padding-bottom: 14px;

    margin-bottom: 12px;

    gap: 15px;

}

.sub-head-badge {

    font-family: monospace;

    font-size: 9px;

    color:
        var(--accent, #d32f2f);

    font-weight: bold;

    display: block;

    margin-bottom: 4px;

    letter-spacing: .12em;

}

.card-box-title {

    font-size: 15px;

    font-weight: 900;

    letter-spacing: .1em;

    margin: 0;

    color: #fff;

    text-transform: uppercase;

}

.mode-status-tag {

    font-family: monospace;

    font-size: 9px;

    color: #777;

    letter-spacing: .1em;

    white-space: nowrap;

}

.form-desc-lead {

    font-size: 12px;

    color: #777;

    margin: 0 0 20px;

}


/* =============================================================
   FORM
============================================================= */

.input-form-group {

    display: flex;

    flex-direction: column;

    gap: 6px;

    margin-bottom: 16px;

}

.input-form-group label {

    font-family: monospace;

    font-size: 9px;

    color: #888;

    letter-spacing: .1em;

    text-transform: uppercase;

}

.input-form-group input,
.input-form-group select,
.input-form-group textarea {

    background: #0c0c0c;

    border:
        1px solid
        var(--border, #242424);

    color: #fff;

    font-family:
        'Barlow Condensed',
        sans-serif;

    font-size: 13px;

    padding: 10px 12px;

    outline: none;

    width: 100%;

    transition: border-color .2s;

}

.input-form-group input:focus,
.input-form-group select:focus,
.input-form-group textarea:focus {

    border-color: #666;

}

.input-caption {

    font-size: 10px;

    color: #666;

}

.label-with-meta {

    display: flex;

    justify-content: space-between;

    align-items: baseline;

    gap: 10px;

}

.meta-indicator {

    font-family: monospace;

    font-size: 8px;

    color: #666;

    letter-spacing: .08em;

}

.slug-input-wrapper {

    display: flex;

    align-items: stretch;

    min-width: 0;

}

.slug-url-base {

    background: #141414;

    border:
        1px solid
        var(--border, #242424);

    border-right: none;

    padding: 10px 12px;

    font-family: monospace;

    font-size: 11px;

    color: #777;

    white-space: nowrap;

}

.slug-input-wrapper input {

    min-width: 0;

}

.input-grid-twin {

    display: grid;

    grid-template-columns:
        1.35fr .65fr;

    gap: 14px;

}

.custom-select-box select {

    appearance: none;

    cursor: pointer;

    background-image:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");

    background-repeat: no-repeat;

    background-position:
        right 12px center;

}

.input-text-center {

    text-align: center;

    font-family: monospace !important;

    font-weight: bold;

}

.textarea-footer-meta {

    display: flex;

    justify-content: space-between;

    gap: 10px;

    font-size: 10px;

    color: #666;

}

.count-chars {

    font-family: monospace;

    font-size: 9px;

    color: #555;

}

.counter-warning {

    color:
        var(--accent, #d32f2f);

}


/* =============================================================
   UPLOAD
============================================================= */

.dropzone-area {

    border:
        1px dashed
        var(--border, #333);

    background: #0c0c0c;

    padding: 12px;

    cursor: pointer;

    display: block;

    transition: border-color .2s;

}

.dropzone-area:hover {

    border-color: #666;

}

.file-hidden {

    position: absolute;

    width: 1px;

    height: 1px;

    opacity: 0;

    pointer-events: none;

}

.dropzone-preview-wrap {

    display: flex;

    flex-direction: column;

    gap: 8px;

    align-items: center;

    text-align: center;

}

.dropzone-art-mock {

    width: 100%;

    height: 140px;

    background-color: #141414;

    border:
        1px solid
        var(--border, #242424);

    display: flex;

    align-items: center;

    justify-content: center;

    background-image:
        radial-gradient(
            #202020 1px,
            transparent 1px
        );

    background-size: 8px 8px;

}

.mock-asset-code {

    font-family: monospace;

    font-size: 9px;

    color:
        var(--accent, #d32f2f);

    font-weight: bold;

    background: #0a0a0a;

    padding: 4px 8px;

    border: 1px solid #222;

}

.dropzone-action-text {

    font-family: monospace;

    font-size: 9px;

    color: #888;

    letter-spacing: .08em;

}

.icon-plus {

    color: #fff;

    margin-right: 4px;

}


/* =============================================================
   FLAGS
============================================================= */

.flags-settings-block {

    border-top:
        1px solid #1a1a1a;

    padding-top: 16px;

    margin: 20px 0;

    display: flex;

    flex-direction: column;

    gap: 12px;

}

.flags-group-title {

    font-family: monospace;

    font-size: 9px;

    color: #777;

    letter-spacing: .12em;

}

.flag-toggle-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    background: #0c0c0c;

    border:
        1px solid
        var(--border, #242424);

    padding: 10px 14px;

    gap: 16px;

}

.flag-text-col {

    display: flex;

    flex-direction: column;

    gap: 2px;

}

.flag-name {

    font-size: 11px;

    font-weight: 800;

    color: #eee;

    letter-spacing: .05em;

}

.flag-sub {

    font-size: 10px;

    color: #666;

}

.toggle-switch {

    position: relative;

    display: inline-block;

    width: 34px;

    height: 18px;

    flex-shrink: 0;

    cursor: pointer;

}

.toggle-switch input {

    opacity: 0;

    width: 0;

    height: 0;

}

.toggle-slider {

    position: absolute;

    inset: 0;

    cursor: pointer;

    background: #1a1a1a;

    border:
        1px solid #333;

    transition: .2s;

}

.toggle-slider:before {

    position: absolute;

    content: "";

    height: 12px;

    width: 12px;

    left: 2px;

    bottom: 2px;

    background: #fff;

    transition: .2s;

}

.toggle-switch input:checked
+ .toggle-slider {

    background:
        var(--accent, #d32f2f);

    border-color:
        var(--accent, #d32f2f);

}

.toggle-switch input:checked
+ .toggle-slider:before {

    transform:
        translateX(16px);

}


/* =============================================================
   FORM BUTTONS
============================================================= */

.form-submit-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 12px;

    margin-top: 24px;

}

.btn-form-reset {

    background: transparent;

    border:
        1px solid
        var(--border, #242424);

    color: #777;

    padding: 12px 18px;

    font-family: monospace;

    font-size: 10px;

    letter-spacing: .1em;

    cursor: pointer;

    transition: all .2s;

}

.btn-form-reset:hover {

    color: #fff;

    border-color: #555;

}

.btn-form-save {

    flex: 1;

    background: #fff;

    color: #000;

    border: 1px solid #fff;

    padding: 12px 20px;

    font-size: 11px;

    font-weight: 900;

    letter-spacing: .12em;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    transition: all .2s;

}

.btn-form-save:hover {

    background:
        var(--accent, #d32f2f);

    border-color:
        var(--accent, #d32f2f);

    color: #fff;

}


/* =============================================================
   TABLE
============================================================= */

.table-filter-input-wrap {

    display: flex;

    align-items: center;

    background: #0c0c0c;

    border:
        1px solid
        var(--border, #242424);

    padding: 0 8px;

}

.input-table-filter {

    background: transparent;

    border: none;

    color: #fff;

    font-family: monospace;

    font-size: 9px;

    padding: 7px 4px;

    outline: none;

    letter-spacing: .08em;

    width: 140px;

}

.filter-icon {

    color: #666;

    font-size: 10px;

}

.table-responsive-wrapper {

    width: 100%;

    overflow-x: auto;

}

.category-data-table {

    width: 100%;

    border-collapse: collapse;

    font-family: monospace;

    font-size: 10px;

    text-align: left;

}

.category-data-table th {

    background: #0c0c0c;

    padding: 10px 12px;

    color: #666;

    font-size: 8px;

    letter-spacing: .12em;

    border-bottom:
        1px solid
        var(--border, #242424);

    font-weight: 700;

    white-space: nowrap;

}

.category-data-table td {

    padding: 14px 12px;

    border-bottom:
        1px solid #161616;

    vertical-align: middle;

}

.category-data-table tr:hover td {

    background: #141414;

}

.order-cell-idx {

    font-weight: bold;

    color: #666;

}

.cat-identity-cell {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 180px;

}

.cat-icon-symbol {

    font-size: 14px;

    opacity: .8;

}

.cat-name-stack {

    display: flex;

    flex-direction: column;

    min-width: 0;

}

.cat-name-text {

    font-family:
        'Barlow Condensed',
        sans-serif;

    font-size: 13px;

    font-weight: 800;

    color: #fff;

    letter-spacing: .05em;

    display: block;

}

.cat-slug-text {

    font-size: 9px;

    color: #666;

}

.cat-parent-tag {

    background: #141414;

    border:
        1px solid #242424;

    padding: 2px 6px;

    font-size: 8px;

    color: #888;

    white-space: nowrap;

}

.cat-sku-stack {

    display: flex;

    flex-direction: column;

}

.sku-number {

    color: #fff;

    font-weight: bold;

    white-space: nowrap;

}

.sku-percentage {

    font-size: 8px;

    color: #666;

}

.status-badge-live {

    display: inline-block;

    padding: 2px 6px;

    font-size: 8px;

    font-weight: bold;

    letter-spacing: .08em;

    white-space: nowrap;

}

.status-badge-live.online {

    background:
        rgba(46, 204, 113, .1);

    color: #2ecc71;

    border:
        1px solid #2ecc71;

}

.status-badge-live.restricted {

    background:
        rgba(211, 47, 47, .15);

    color:
        var(--accent, #d32f2f);

    border:
        1px solid
        var(--accent, #d32f2f);

}

.table-action-buttons {

    display: flex;

    gap: 6px;

    justify-content: flex-end;

}

.inline-form {

    display: inline-flex;

    margin: 0;

}

.btn-row-action {

    background: #161616;

    border:
        1px solid
        var(--border, #242424);

    color: #aaa;

    width: 28px;

    height: 28px;

    cursor: pointer;

    font-size: 10px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    transition: all .2s;

}

.btn-row-action:hover {

    color: #fff;

    border-color: #666;

}

.empty-category-state {

    text-align: center;

    color: #666;

    padding: 40px !important;

    font-family: monospace;

    font-size: 10px;

}


/* =============================================================
   DISTRIBUTION
============================================================= */

.distribution-footer-panel {

    margin-top: 24px;

    border-top:
        1px solid #1a1a1a;

    padding-top: 18px;

}

.distribution-header-row {

    display: flex;

    justify-content: space-between;

    gap: 15px;

    font-family: monospace;

    font-size: 9px;

    color: #777;

    margin-bottom: 8px;

}

.distribution-multi-bar {

    display: flex;

    height: 6px;

    background: #141414;

    overflow: hidden;

    margin-bottom: 10px;

}

.bar-segment {

    background:
        var(--accent, #d32f2f);

    border-right:
        1px solid #0c0c0c;

}

.distribution-legend-row {

    display: flex;

    gap: 14px;

    font-family: monospace;

    font-size: 8px;

    color: #666;

    flex-wrap: wrap;

}

.color-dot {

    color:
        var(--accent, #d32f2f);

}


/* =============================================================
   AUDIT
============================================================= */

.tax-audit-section {

    border-top:
        1px solid
        var(--border, #242424);

    padding-top: 28px;

    display: grid;

    grid-template-columns:
        .8fr 1.2fr;

    gap: 28px;

}

.audit-sub-tag {

    font-family: monospace;

    font-size: 9px;

    color:
        var(--accent, #d32f2f);

    font-weight: bold;

    letter-spacing: .12em;

    display: block;

    margin-bottom: 4px;

}

.audit-headline {

    font-size: 16px;

    font-weight: 900;

    letter-spacing: .08em;

    margin: 0 0 6px;

    color: #fff;

    text-transform: uppercase;

}

.audit-text {

    font-size: 12px;

    color: #777;

    margin: 0;

    line-height: 1.45;

}

.audit-nodes-stack {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

}

.audit-node-box {

    background: #101010;

    border:
        1px solid
        var(--border, #242424);

    padding: 14px;

    display: flex;

    flex-direction: column;

    gap: 4px;

}

.node-head {

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.node-title {

    font-family: monospace;

    font-size: 8px;

    color: #666;

    letter-spacing: .1em;

}

.node-icon {

    font-size: 11px;

    opacity: .6;

}

.node-val {

    font-family: monospace;

    font-size: 11px;

    font-weight: bold;

    color: #fff;

}

.node-sub {

    font-size: 10px;

    color: #777;

    line-height: 1.35;

}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1100px) {

    .tax-main-layout {

        grid-template-columns: 1fr;

    }

}

@media (max-width: 1024px) {

    .tax-kpi-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}

@media (max-width: 900px) {

    .tax-audit-section {

        grid-template-columns: 1fr;

    }

}

@media (max-width: 700px) {

    .admin-categories-page {

        padding:
            12px
            14px
            50px;

    }

    .tax-kpi-grid {

        grid-template-columns: 1fr;

    }

    .input-grid-twin {

        grid-template-columns: 1fr;

        gap: 0;

    }

    .cat-header-actions {

        width: 100%;

    }

    .btn-tax-outline,
    .btn-tax-solid {

        width: 100%;

        justify-content: center;

        text-align: center;

    }

    .card-head-row {

        align-items: flex-start;

        flex-direction: column;

    }

    .mode-status-tag {

        align-self: flex-start;

    }

    .audit-nodes-stack {

        grid-template-columns: 1fr;

    }

    .slug-input-wrapper {

        flex-direction: column;

    }

    .slug-url-base {

        border-right:
            1px solid
            var(--border, #242424);

        border-bottom: none;

    }

}

@media (max-width: 600px) {

    .terminal-status-strip {

        align-items: flex-start;

        flex-direction: column;

    }

    .status-left,
    .status-right {

        flex-wrap: wrap;

    }

    .form-submit-row {

        flex-direction: column-reverse;

        align-items: stretch;

    }

    .btn-form-reset,
    .btn-form-save {

        width: 100%;

    }

    .distribution-header-row {

        flex-direction: column;

    }

}

</style>

@endsection