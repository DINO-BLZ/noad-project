@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FALLBACKS TEMPORAIRES
    |--------------------------------------------------------------------------
    | Le backend Drops n'envoie pas encore toutes les variables.
    | Ces valeurs permettent à la maquette de fonctionner sans erreur.
    |--------------------------------------------------------------------------
    */

    $activeDrop = $activeDrop ?? null;

    $upcomingDrops = $upcomingDrops ?? collect();

    $archivedDrops = $archivedDrops ?? collect();

    $products = $products ?? collect();

    $drops = $drops ?? collect();

    $activeDropRevenue = $activeDropRevenue ?? 0;

    $activeDropSold = $activeDropSold ?? 0;

    $activeDropSoldPercentage = $activeDropSoldPercentage ?? 0;

    $activeDropQuota = $activeDropQuota ?? 0;

    $activeDropSalesRate = $activeDropSalesRate ?? null;

    $whitelistCount = $whitelistCount ?? 0;

    $archivedRevenue = $archivedRevenue ?? 0;
@endphp


<div class="admin-drops-page">

    {{-- =========================================================
         01. BARRE SYSTÈME / NAVIGATION
    ========================================================== --}}

    <header class="admin-sys-bar">

        <div class="sys-left">

            <span class="sys-id">
                SYS.ADMIN //
            </span>

            <nav class="sys-nav">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="sys-nav-link"
                >
                    VUE D'ENSEMBLE
                </a>

                <a
                    href="{{ route('admin.drops.index') }}"
                    class="sys-nav-link active"
                >
                    DROPS &amp; ALLOCATIONS
                </a>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="sys-nav-link"
                >
                    INVENTAIRE SÉRIE
                </a>

                <a
                    href="{{ route('admin.whitelist.index') }}"
                    class="sys-nav-link"
                >
                    REGISTRE WHITELIST
                </a>

                <a
                    href="#"
                    class="sys-nav-link"
                >
                    LOGISTIQUE DÉPÔT
                </a>

            </nav>

        </div>


        <div class="sys-right">

            <span class="server-node-indicator">

                <span class="dot-pulse"></span>

                <span class="node-txt">
                    NŒUD SERVEUR : ALGER-01 [STABLE]
                </span>

            </span>

        </div>

    </header>


    {{-- =========================================================
         02. HEADER PRINCIPAL
    ========================================================== --}}

    <div class="drops-header-section">

        <div class="header-titles">

            <span class="protocol-tag">
                NOAD PROTOCOL EXECUTION // CONTRÔLE CENTRAL
            </span>

            <h1 class="page-title">
                PILOTAGE DES DROPS //
                PROTOCOLE DE DISTRIBUTION NUMÉROTÉE
            </h1>

            <p class="page-lead">
                Gouvernance des tirages limités, fenêtres de frappe
                whitelist, allocations d'ateliers et archivage
                immuable des séries.
            </p>

        </div>


        <div class="header-actions">

            <a
                href="#new-drop-form"
                class="btn-action-outline"
            >
                <span class="btn-plus">+</span>
                NOUVELLE ALLOCATION
            </a>

            <button
                type="button"
                class="btn-action-solid"
            >
                EXPORTER TOUT LE REGISTRE
            </button>

        </div>

    </div>


    {{-- =========================================================
         03. KPI
    ========================================================== --}}

    <section class="drops-kpi-grid">


        {{-- =====================================================
             DROP ACTIF
        ====================================================== --}}

        <div class="kpi-card active-drop">

            <div class="kpi-top">

                <span class="kpi-tag">

                    <span class="dot-red">
                        ●
                    </span>

                    DROP ACTIF EN DIRECT

                </span>

                <span class="status-pill open">
                    SALVE OUVERTE
                </span>

            </div>


            <div class="kpi-body">

                <h2 class="kpi-title">
                    {{ $activeDrop?->title ?? 'AUCUN DROP ACTIF' }}
                </h2>

                <span class="kpi-sub">
                    Tirage public général • Clôture stricte dans :
                </span>


                @if($activeDrop)

                    <div
                        class="countdown-display"
                        data-end="{{ optional($activeDrop->ends_at)->toIso8601String() }}"
                    >

                        <div class="timer-segment">

                            <span class="val">
                                --
                            </span>

                            <span class="lbl">
                                J
                            </span>

                        </div>

                        <span class="timer-sep">
                            :
                        </span>

                        <div class="timer-segment">

                            <span class="val">
                                --
                            </span>

                            <span class="lbl">
                                H
                            </span>

                        </div>

                        <span class="timer-sep">
                            :
                        </span>

                        <div class="timer-segment">

                            <span class="val">
                                --
                            </span>

                            <span class="lbl">
                                M
                            </span>

                        </div>

                        <span class="timer-sep">
                            :
                        </span>

                        <div class="timer-segment">

                            <span class="val">
                                --
                            </span>

                            <span class="lbl">
                                S
                            </span>

                        </div>

                    </div>

                @else

                    <div class="countdown-empty">
                        AUCUN DROP EN COURS
                    </div>

                @endif

            </div>


            <div class="kpi-bottom">

                <span>

                    PIÈCES VENDUES :

                    <strong class="text-white">
                        {{ $activeDropSoldPercentage }}%
                    </strong>

                </span>

                <span>
                    {{ $activeDropQuota }} UNITÉS ALLOUÉES
                </span>

            </div>

        </div>


        {{-- =====================================================
             PROCHAIN DROP
        ====================================================== --}}

        <div class="kpi-card prep-drop">

            <div class="kpi-top">

                <span class="kpi-tag">
                    🔒 DROP EN PRÉPARATION
                </span>

                <span class="status-pill whitelist">
                    WHITELIST ONLY
                </span>

            </div>


            <div class="kpi-body">

                <h2 class="kpi-title">

                    {{ $upcomingDrops->first()?->title
                        ?? 'AUCUN DROP PROGRAMMÉ'
                    }}

                </h2>

                <span class="kpi-sub">
                    Ouverture programmée :
                </span>


                <div class="release-date-display">

                    @if($upcomingDrops->first()?->release_at)

                        {{ $upcomingDrops->first()->release_at->format('d.m.Y // H:i') }}

                    @else

                        —

                    @endif

                </div>

            </div>


            <div class="kpi-bottom">

                <span>

                    INSCRITS WHITELIST :

                    <strong class="text-white">
                        {{ $whitelistCount }}
                    </strong>

                </span>

                <span>

                    QUOTA MAX :

                    {{ $upcomingDrops->first()?->max_quota ?? 0 }}

                    PCS

                </span>

            </div>

        </div>


        {{-- =====================================================
             ARCHIVES
        ====================================================== --}}

        <div class="kpi-card archive-drop">

            <div class="kpi-top">

                <span class="kpi-tag">

                    🗂
                    {{ $archivedDrops->count() }}
                    DROPS ARCHIVÉS

                </span>

                <span class="status-pill soldout">
                    SOLDOUT
                </span>

            </div>


            <div class="kpi-body">

                <h2 class="kpi-title">
                    HISTORIQUE SCELLÉ
                </h2>


                <div class="archive-mini-list">

                    @forelse($archivedDrops->take(2) as $drop)

                        <div class="archive-row">

                            <span>
                                {{ $drop->title }}
                            </span>

                            <span class="mono">

                                {{ $drop->sold_quantity ?? 0 }}

                                /

                                {{ $drop->max_quota }}

                                PCS

                            </span>

                        </div>

                    @empty

                        <div class="archive-row">

                            <span>
                                AUCUN ARCHIVE
                            </span>

                        </div>

                    @endforelse

                </div>

            </div>


            <div class="kpi-bottom">

                <span>
                    VOLUME TOTAL TRAITÉ
                </span>

                <span class="mono text-white font-bold">

                    {{ number_format(
                        $archivedRevenue,
                        0,
                        ',',
                        ' '
                    ) }}

                    DA

                </span>

            </div>

        </div>

    </section>


    {{-- =========================================================
         04. MONITORING DROP ACTIF
    ========================================================== --}}

    <section class="live-monitor-card">

        <div class="section-title-bar">

            <div class="title-with-glyph">

                <span class="square-bullet">
                    ■
                </span>

                <h2 class="section-title">

                    MONITORING DIRECT //

                    {{ $activeDrop?->title
                        ?? 'AUCUN DROP ACTIF'
                    }}

                </h2>

            </div>

            <span class="mono-meta">
                TRANSMISSION ATELIER &amp; STOCK EN CONTINU
            </span>

        </div>


        @if($activeDrop)

            <div class="monitor-content-grid">


                {{-- =================================================
                     VISUEL
                ================================================== --}}

                <div class="monitor-visual-pane">

                    <span class="salve-badge">
                        ● SALVE ACTIVE
                    </span>


                    <div class="model-image-wrap">

                        @if(!empty($activeDrop->image))

                            <img
                                src="{{ asset('storage/' . $activeDrop->image) }}"
                                alt="{{ $activeDrop->title }}"
                            >

                        @else

                            <div class="image-fallback-model">

                                <div class="brand-monogram">
                                    NOAD
                                </div>

                                <span class="fallback-caption">
                                    DEFEND YOUR PRINCIPLE
                                </span>

                            </div>

                        @endif

                    </div>


                    <div class="visual-footer-info">

                        <span class="mat-info">

                            DROP

                            <br>

                            <strong>
                                {{ $activeDrop->title }}
                            </strong>

                        </span>


                        <span class="edition-info">

                            ÉDITION
                            {{ $activeDrop->max_quota }}

                        </span>

                    </div>

                </div>


                {{-- =================================================
                     DONNÉES
                ================================================== --}}

                <div class="monitor-data-pane">


                    <div>

                        <div class="data-ref-tag">

                            RÉFÉRENCE SYSTÈME #

                            {{ str_pad(
                                $activeDrop->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            ) }}

                        </div>


                        <h3 class="data-hero-title">
                            {{ $activeDrop->title }}
                        </h3>

                    </div>


                    {{-- CA --}}

                    <div class="financial-box">

                        <span class="fin-label">
                            CHIFFRE D'AFFAIRES GÉNÉRÉ
                        </span>

                        <span class="fin-amount">

                            {{ number_format(
                                $activeDropRevenue,
                                0,
                                ',',
                                ' '
                            ) }}

                            DA

                        </span>

                    </div>


                    {{-- JAUGE --}}

                    <div class="gauge-container">

                        <div class="gauge-labels">

                            <span class="gauge-title">
                                JAUGE D'ÉPUISEMENT DE SÉRIE
                            </span>

                            <span class="gauge-ratio">

                                <strong>

                                    {{ $activeDropSold }}

                                    /

                                    {{ $activeDrop->max_quota }}

                                </strong>

                                PIÈCES VENDUES

                            </span>

                        </div>


                        <div class="gauge-track">

                            <div
                                class="gauge-fill"
                                style="width: {{ min(100, $activeDropSoldPercentage) }}%;"
                            ></div>

                        </div>


                        <div class="gauge-foot">

                            <span>

                                RESTE :

                                <strong>

                                    {{ max(
                                        0,
                                        $activeDrop->max_quota - $activeDropSold
                                    ) }}

                                    UNITÉS DISPONIBLES

                                </strong>

                            </span>


                            <span>

                                TAUX D'ACQUISITION :

                                {{ $activeDropSalesRate ?? '—' }}

                            </span>

                        </div>

                    </div>


                    {{-- PRODUITS DU DROP --}}

                    <div class="pieces-allocation-grid">

                        @forelse($activeDrop->products ?? collect() as $product)

                            <div class="piece-box">

                                <span class="piece-name">
                                    {{ $product->name }}
                                </span>


                                <div class="piece-numbers">

                                    <span class="piece-total">

                                        {{ $product->drop_sold_quantity ?? 0 }}

                                        /

                                        {{ $product->pivot->quota ?? 0 }}

                                        EX

                                    </span>


                                    @php

                                        $productQuota =
                                            $product->pivot->quota ?? 0;

                                        $productSold =
                                            $product->drop_sold_quantity ?? 0;

                                        $productRemaining =
                                            max(
                                                0,
                                                $productQuota - $productSold
                                            );

                                    @endphp


                                    <span
                                        class="
                                            piece-rest
                                            @if($productRemaining <= 2)
                                                alert
                                            @elseif($productRemaining <= 12)
                                                warning
                                            @endif
                                        "
                                    >

                                        RESTE
                                        {{ $productRemaining }}

                                    </span>

                                </div>

                            </div>

                        @empty

                            <div class="piece-box">

                                <span class="piece-name">
                                    AUCUN PRODUIT ASSOCIÉ
                                </span>

                            </div>

                        @endforelse

                    </div>


                    {{-- ACTIONS --}}

                    <div class="monitor-actions-row">

                        <a
                            href="{{ route(
                                'admin.drops.edit',
                                $activeDrop
                            ) }}"
                            class="btn-mon-tool"
                        >
                            ⚙ MODIFIER LES ALLOCATIONS
                        </a>


                        @if(Route::has('admin.drops.export'))

                            <a
                                href="{{ route(
                                    'admin.drops.export',
                                    $activeDrop
                                ) }}"
                                class="btn-mon-tool"
                            >
                                ⤓ EXPORTER LISTE DES ACQUÉREURS (.CSV)
                            </a>

                        @endif


                        @if(Route::has('admin.drops.close'))

                            <form
                                action="{{ route(
                                    'admin.drops.close',
                                    $activeDrop
                                ) }}"
                                method="POST"
                                class="inline-form"
                            >

                                @csrf

                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn-mon-danger"
                                    onclick="return confirm('Confirmer la clôture immédiate du drop ?')"
                                >
                                    ✕ CLÔTURER LE DROP
                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            </div>

        @else

            <div class="empty-state">
                AUCUN DROP ACTIF ACTUELLEMENT
            </div>

        @endif

    </section>


    {{-- =========================================================
         05. TABLEAU DES DROPS
    ========================================================== --}}

    <section class="drops-table-section">

        <div class="section-title-bar">

            <div class="title-with-glyph">

                <span class="square-bullet">
                    ■
                </span>

                <h2 class="section-title">
                    CALENDRIER DES DROPS // PROGRAMMATION SYSTÈME
                </h2>

            </div>


            <div class="table-filters-stack">

                <span class="filter-lbl">
                    FILTRER PAR :
                </span>


                <a
                    href="{{ route('admin.drops.index') }}"
                    class="btn-filter-tag {{ !request('status') ? 'active' : '' }}"
                >
                    TOUS
                </a>


                <a
                    href="{{ route(
                        'admin.drops.index',
                        ['status' => 'active']
                    ) }}"
                    class="btn-filter-tag {{ request('status') === 'active' ? 'active' : '' }}"
                >
                    ACTIFS
                </a>


                <a
                    href="{{ route(
                        'admin.drops.index',
                        ['status' => 'scheduled']
                    ) }}"
                    class="btn-filter-tag {{ request('status') === 'scheduled' ? 'active' : '' }}"
                >
                    PROGRAMMÉS
                </a>


                <a
                    href="{{ route(
                        'admin.drops.index',
                        ['status' => 'archived']
                    ) }}"
                    class="btn-filter-tag {{ request('status') === 'archived' ? 'active' : '' }}"
                >
                    SCELLÉS
                </a>

            </div>

        </div>


        <div class="table-container">

            <table class="drops-data-table">

                <thead>

                    <tr>

                        <th>
                            SÉRIE / TITRE
                        </th>

                        <th>
                            LANCEMENT
                        </th>

                        <th>
                            ACCÈS PROTOCOLE
                        </th>

                        <th>
                            RÉFÉRENCES
                        </th>

                        <th>
                            QUOTA TOTAL
                        </th>

                        <th>
                            STATUT OPÉRATIONNEL
                        </th>

                        <th class="text-right">
                            ACTIONS
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($drops as $drop)

                        <tr
                            class="{{ $drop->status === 'archived'
                                ? 'row-archived'
                                : ''
                            }}"
                        >

                            <td>

                                <div class="series-name-cell">

                                    <span class="primary-series">
                                        {{ $drop->title }}
                                    </span>

                                    <span class="series-hash">

                                        ID:
                                        #{{ str_pad(
                                            $drop->id,
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        ) }}

                                    </span>

                                </div>

                            </td>


                            <td>

                                <div class="datetime-cell">

                                    <span class="date">

                                        {{ $drop->release_at
                                            ? $drop->release_at->format('d.m.Y // H:i')
                                            : '—'
                                        }}

                                    </span>

                                </div>

                            </td>


                            <td>

                                <span
                                    class="
                                        access-pill
                                        {{ $drop->whitelist_only
                                            ? 'whitelist'
                                            : 'public'
                                        }}
                                    "
                                >

                                    {{ $drop->whitelist_only
                                        ? 'WHITELIST PRIORITAIRE'
                                        : 'PUBLIC OUVERT'
                                    }}

                                </span>

                            </td>


                            <td>

                                <span class="mono">

                                    {{ $drop->products_count
                                        ?? ($drop->products?->count() ?? 0)
                                    }}

                                    PRODUITS

                                </span>

                            </td>


                            <td>

                                <div class="quota-cell">

                                    <span class="quota-val">

                                        {{ $drop->max_quota }}

                                        UNITÉS

                                    </span>

                                    <span class="quota-sub">

                                        {{ $drop->sold_quantity ?? 0 }}

                                        VENDUES

                                    </span>

                                </div>

                            </td>


                            <td>

                                @switch($drop->status)

                                    @case('active')

                                        <span class="operational-status live">

                                            <span class="dot-live">
                                                ●
                                            </span>

                                            ACTIF // DISTRIBUTION

                                        </span>

                                        @break


                                    @case('scheduled')

                                        <span class="operational-status locked">

                                            🔒 VÉRIFICATION PROTOCOLE

                                        </span>

                                        @break


                                    @case('archived')

                                        <span class="operational-status sealed">

                                            ARCHIVÉ // ÉPUISÉ

                                        </span>

                                        @break


                                    @default

                                        <span class="operational-status">

                                            {{ strtoupper($drop->status) }}

                                        </span>

                                @endswitch

                            </td>


                            <td class="text-right">

                                <a
                                    href="{{ route(
                                        'admin.drops.edit',
                                        $drop
                                    ) }}"
                                    class="btn-table-action"
                                >
                                    GÉRER
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7">

                                <div class="empty-state">
                                    AUCUN DROP ENREGISTRÉ
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- =========================================================
         06. CRÉATION D'UN NOUVEAU DROP
    ========================================================== --}}

    <section
        class="new-drop-form-section"
        id="new-drop-form"
    >

        <div class="section-title-bar">

            <div class="title-with-glyph">

                <span class="square-bullet">
                    ■
                </span>

                <h2 class="section-title">
                    CRÉATION / PARAMÉTRAGE NOUVEAU DROP
                </h2>

            </div>

            <span class="mono-meta">
                ID SESSION CONFIG: #SYS-DRP-BUILDER-V3
            </span>

        </div>


        <form
            action="{{ route('admin.drops.store') }}"
            method="POST"
            class="new-drop-form-body"
        >

            @csrf


            {{-- BANNIÈRE --}}

            <div class="matrix-banner">

                <div class="matrix-left">

                    <span class="matrix-icon">
                        ⬡
                    </span>

                    <div>

                        <span class="matrix-title">
                            MATRICE DE DÉPLOIEMENT TACTIQUE
                        </span>

                        <p class="matrix-desc">
                            Assurez-vous que les pièces sélectionnées
                            ont été préalablement marquées dans la réserve atelier.
                        </p>

                    </div>

                </div>


                <div class="matrix-right">

                    <span class="gateway-tag">
                        PASSERELLE PAIEMENT : DA SEULEMENT
                    </span>

                </div>

            </div>


            <div class="form-dual-columns">


                {{-- =================================================
                     PARAMÈTRES GÉNÉRAUX
                ================================================== --}}

                <div class="form-col-inputs">


                    {{-- TITRE --}}

                    <div class="input-block">

                        <label for="title">
                            TITRE DU DROP
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Ex: DROP 02 — URBAN ARMOUR"
                        >

                        @error('title')

                            <span class="input-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- SOUS-TITRE --}}

                    <div class="input-block">

                        <label for="subtitle">
                            SOUS-TITRE &amp; SLOGAN DE CAMPAGNE
                        </label>

                        <input
                            type="text"
                            id="subtitle"
                            name="subtitle"
                            value="{{ old('subtitle') }}"
                            placeholder="DEFEND YOUR PRINCIPLE"
                        >

                    </div>


                    {{-- DATE + QUOTA --}}

                    <div class="input-row-twin">


                        <div class="input-block">

                            <label for="release_at">
                                DATE &amp; HEURE DE RELEASE
                            </label>

                            <input
                                type="datetime-local"
                                id="release_at"
                                name="release_at"
                                value="{{ old('release_at') }}"
                                required
                            >

                            @error('release_at')

                                <span class="input-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        <div class="input-block">

                            <label for="max_quota">
                                VOLUME GLOBAL MAXIMAL
                            </label>

                            <div class="quota-input-badge">

                                <span class="prefix">
                                    #
                                </span>

                                <input
                                    type="number"
                                    id="max_quota"
                                    name="max_quota"
                                    value="{{ old('max_quota') }}"
                                    min="1"
                                    required
                                >

                                <span class="suffix">
                                    PIÈCES
                                </span>

                            </div>


                            @error('max_quota')

                                <span class="input-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>

                    </div>


                    {{-- WHITELIST --}}

                    <div class="radio-selection-block">

                        <span class="block-label">
                            FENÊTRE D'EXCLUSIVITÉ WHITELIST
                        </span>


                        <div class="radio-options-stack">


                            <label class="custom-radio-row">

                                <input
                                    type="radio"
                                    name="whitelist_window"
                                    value="24h"
                                    {{ old(
                                        'whitelist_window',
                                        '24h'
                                    ) === '24h'
                                        ? 'checked'
                                        : ''
                                    }}
                                >

                                <span class="radio-bullet"></span>

                                <span class="radio-text">
                                    24 HEURES AVANT OUVERTURE PUBLIQUE
                                </span>

                                <span class="reco-tag">
                                    RECOMMANDÉ
                                </span>

                            </label>


                            <label class="custom-radio-row">

                                <input
                                    type="radio"
                                    name="whitelist_window"
                                    value="12h"
                                    {{ old('whitelist_window') === '12h'
                                        ? 'checked'
                                        : ''
                                    }}
                                >

                                <span class="radio-bullet"></span>

                                <span class="radio-text">
                                    12 HEURES AVANT OUVERTURE PUBLIQUE
                                </span>

                            </label>


                            <label class="custom-radio-row">

                                <input
                                    type="radio"
                                    name="whitelist_window"
                                    value="none"
                                    {{ old('whitelist_window') === 'none'
                                        ? 'checked'
                                        : ''
                                    }}
                                >

                                <span class="radio-bullet"></span>

                                <span class="radio-text">
                                    AUCUN SAS EXCLUSIF
                                </span>

                            </label>

                        </div>

                    </div>


                    {{-- MANIFESTE --}}

                    <div class="input-block">

                        <label for="manifesto">
                            MANIFESTE DU DROP
                        </label>

                        <textarea
                            id="manifesto"
                            name="manifesto"
                            rows="5"
                            placeholder="Texte du manifeste..."
                        >{{ old('manifesto') }}</textarea>

                    </div>

                </div>


                {{-- =================================================
                     PRODUITS
                ================================================== --}}

                <div class="form-col-products">

                    <div class="products-selection-head">

                        <span class="col-title">
                            PRODUITS ASSOCIÉS AU DROP
                        </span>

                        <span
                            class="selected-count"
                            id="selected-products-count"
                        >
                            0 PRODUIT
                        </span>

                    </div>


                    <div class="product-selection-list">

                        @forelse($products as $product)

                            <div class="product-select-row">


                                <label class="checkbox-col">

                                    <input
                                        type="checkbox"
                                        name="products[]"
                                        value="{{ $product->id }}"
                                    >

                                    <span class="custom-check">
                                        ✓
                                    </span>

                                </label>


                                <div class="product-thumb">

                                    @if($product->image)

                                        <img
                                            src="{{ asset(
                                                'storage/' . $product->image
                                            ) }}"
                                            alt="{{ $product->name }}"
                                        >

                                    @else

                                        <div class="thumb-inner">
                                            NOAD
                                        </div>

                                    @endif

                                </div>


                                <div class="product-meta">

                                    <span class="prod-title">
                                        {{ $product->name }}
                                    </span>

                                    <span class="prod-sub">

                                        SKU :
                                        {{ $product->sku ?? 'N/A' }}

                                        •

                                        PRIX :

                                        {{ number_format(
                                            $product->price,
                                            0,
                                            ',',
                                            ' '
                                        ) }}

                                        DA

                                    </span>

                                </div>


                                <div class="product-quota-input">

                                    <input
                                        type="number"
                                        name="product_quotas[{{ $product->id }}]"
                                        value="0"
                                        min="0"
                                        class="quota-sm"
                                    >

                                    <span class="unit-lbl">
                                        PIÈCES
                                    </span>

                                </div>

                            </div>

                        @empty

                            <div class="empty-products">
                                AUCUN PRODUIT DISPONIBLE
                            </div>

                        @endforelse

                    </div>


                    {{-- RÉSUMÉ QUOTA --}}

                    <div class="quota-summary-box">

                        <span class="summary-caption">
                            RÉSUMÉ DU QUOTA VALIDÉ
                        </span>


                        <div class="calculated-row">

                            <span class="calc-label">
                                TOTAL CALCULÉ :
                            </span>

                            <span
                                class="calc-number"
                                id="calculated-quota"
                            >
                                0 UNITÉS
                            </span>

                        </div>


                        <p class="calc-expl">

                            L'enregistrement verrouille la génération
                            des numéros de série et déclenche le protocole
                            de distribution du drop.

                        </p>


                        <div class="form-cta-row">

                            <button
                                type="submit"
                                class="btn-submit-drop"
                            >

                                PROGRAMMER LE DROP

                                <span>
                                    →
                                </span>

                            </button>


                            <button
                                type="button"
                                class="btn-draft-drop"
                            >
                                BROUILLON LOCAL
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </section>


    {{-- =========================================================
         07. FOOTER AUDIT
    ========================================================== --}}

    <footer class="drops-footer-audit">

        <div class="audit-left">

            <span class="audit-label">
                JOURNAL AUDIT SÉRIE :
            </span>

            <span class="audit-val">
                DERNIÈRE SYNCHRO ATELIER :
                IL Y A 4 MIN
            </span>

            <span class="audit-sep">
                •
            </span>

            <span class="audit-val">
                SERVEUR WHITELIST :
                EN ATTENTE DU SIGNAL CLÉ
            </span>

        </div>


        <div class="audit-right">

            <span class="shield-check">
                🛡 SYSTÈME OPÉRATIONNEL CONFORME NOAD-OPS
            </span>

        </div>

    </footer>

</div>


{{-- =============================================================
     CSS
============================================================= --}}

<style>

.admin-drops-page {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 24px 80px;
    box-sizing: border-box;
    color: var(--text, #e5e5e5);
    font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #0c0c0c;
}

.admin-drops-page *,
.admin-drops-page *::before,
.admin-drops-page *::after {
    box-sizing: border-box;
}

.admin-drops-page a {
    color: inherit;
    text-decoration: none;
}

.mono {
    font-family: monospace;
}

.text-white {
    color: #fff;
}

.font-bold {
    font-weight: 800;
}


/* =============================================================
   SYSTEM BAR
============================================================= */

.admin-sys-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.sys-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.sys-id {
    font-family: monospace;
    font-size: 11px;
    font-weight: 800;
    color: #666;
    letter-spacing: .1em;
}

.sys-nav {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.sys-nav-link {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    color: #888;
    padding: 4px 0;
    position: relative;
    transition: color .2s;
}

.sys-nav-link:hover,
.sys-nav-link.active {
    color: #fff;
}

.sys-nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -13px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--accent, #b02e26);
}

.server-node-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: monospace;
    font-size: 9px;
    color: #aaa;
    background-color: #121212;
    border: 1px solid var(--border, #242424);
    padding: 4px 10px;
}

.dot-pulse {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: var(--accent, #b02e26);
    box-shadow: 0 0 0 2px rgba(176, 46, 38, .3);
}


/* =============================================================
   HEADER
============================================================= */

.drops-header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.protocol-tag {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .15em;
    color: var(--accent, #b02e26);
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

.page-title {
    font-size: clamp(26px, 3.8vw, 42px);
    font-weight: 900;
    letter-spacing: .04em;
    line-height: 1.1;
    margin: 0 0 8px;
    color: #fff;
    text-transform: uppercase;
}

.page-lead {
    font-size: 13px;
    color: #888;
    margin: 0;
    max-width: 800px;
    line-height: 1.4;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-action-outline,
.btn-action-solid {
    padding: 12px 18px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .12em;
    transition: all .2s;
    cursor: pointer;
}

.btn-action-outline {
    border: 1px solid var(--border, #242424);
    background-color: #101010;
    color: #fff;
}

.btn-action-outline:hover {
    border-color: #fff;
}

.btn-action-solid {
    background-color: #fff;
    color: #000;
    border: 1px solid #fff;
}

.btn-action-solid:hover {
    background-color: var(--accent, #b02e26);
    border-color: var(--accent, #b02e26);
    color: #fff;
}

.btn-plus {
    font-size: 16px;
}


/* =============================================================
   KPI
============================================================= */

.drops-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.kpi-card {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 190px;
}

.kpi-card.active-drop {
    border-left: 3px solid var(--accent, #b02e26);
}

.kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.kpi-tag {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
    color: #888;
}

.dot-red {
    color: var(--accent, #b02e26);
}

.status-pill {
    font-family: monospace;
    font-size: 9px;
    padding: 2px 6px;
    font-weight: bold;
    letter-spacing: .1em;
}

.status-pill.open {
    background-color: rgba(176, 46, 38, .15);
    color: var(--accent, #b02e26);
    border: 1px solid var(--accent, #b02e26);
}

.status-pill.whitelist {
    background-color: #1a1a1a;
    color: #fff;
    border: 1px solid #444;
}

.status-pill.soldout {
    background-color: #141414;
    color: #777;
    border: 1px solid #282828;
}

.kpi-title {
    font-size: 18px;
    font-weight: 900;
    letter-spacing: .08em;
    margin: 0 0 4px;
    color: #fff;
}

.kpi-sub {
    font-size: 11px;
    color: #777;
    display: block;
    margin-bottom: 12px;
}

.countdown-display {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin: 12px 0 16px;
}

.timer-segment {
    display: flex;
    align-items: baseline;
    gap: 2px;
    font-family: monospace;
}

.timer-segment .val {
    font-size: 32px;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}

.timer-segment .lbl {
    font-size: 12px;
    font-weight: bold;
    color: #888;
}

.timer-sep {
    font-family: monospace;
    font-size: 20px;
    color: #444;
}

.release-date-display {
    font-family: monospace;
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    margin: 12px 0 16px;
}

.countdown-empty {
    font-family: monospace;
    font-size: 13px;
    color: #555;
    margin: 20px 0;
}

.archive-mini-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-family: monospace;
    font-size: 10px;
    margin: 10px 0 16px;
    color: #888;
}

.archive-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.kpi-bottom {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-family: monospace;
    font-size: 10px;
    color: #777;
    border-top: 1px solid #1a1a1a;
    padding-top: 12px;
}


/* =============================================================
   SECTION BAR
============================================================= */

.section-title-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background-color: #121212;
    border-bottom: 1px solid var(--border, #242424);
    flex-wrap: wrap;
    gap: 10px;
}

.title-with-glyph {
    display: flex;
    align-items: center;
    gap: 8px;
}

.square-bullet {
    color: var(--accent, #b02e26);
    font-size: 11px;
}

.section-title {
    font-size: 14px;
    font-weight: 900;
    letter-spacing: .12em;
    margin: 0;
    color: #fff;
    text-transform: uppercase;
}

.mono-meta {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: .1em;
}


/* =============================================================
   MONITORING
============================================================= */

.live-monitor-card {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    margin-bottom: 32px;
}

.monitor-content-grid {
    display: grid;
    grid-template-columns: .85fr 1.15fr;
}

.monitor-visual-pane {
    padding: 24px;
    border-right: 1px solid var(--border, #242424);
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background-color: #0d0d0d;
}

.salve-badge {
    position: absolute;
    top: 34px;
    left: 34px;
    background-color: #000;
    border: 1px solid var(--border, #242424);
    color: var(--accent, #b02e26);
    font-family: monospace;
    font-size: 9px;
    font-weight: bold;
    padding: 3px 8px;
    letter-spacing: .1em;
    z-index: 2;
}

.model-image-wrap {
    width: 100%;
    aspect-ratio: 4 / 5;
    background-color: #151515;
    border: 1px solid var(--border, #242424);
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.model-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-fallback-model {
    text-align: center;
    color: #444;
}

.brand-monogram {
    font-size: 40px;
    font-weight: 900;
    letter-spacing: .1em;
    color: #2a2a2a;
}

.fallback-caption {
    font-family: monospace;
    font-size: 10px;
    color: #555;
}

.visual-footer-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 14px;
    font-family: monospace;
    font-size: 9px;
    color: #777;
}

.visual-footer-info strong {
    color: #fff;
    font-size: 11px;
}

.edition-info {
    background-color: #161616;
    border: 1px solid #2a2a2a;
    padding: 3px 8px;
    color: #aaa;
    font-weight: bold;
}

.monitor-data-pane {
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
}

.data-ref-tag {
    font-family: monospace;
    font-size: 10px;
    color: #666;
    letter-spacing: .12em;
    margin-bottom: 4px;
}

.data-hero-title {
    font-size: 26px;
    font-weight: 900;
    letter-spacing: .06em;
    margin: 0 0 20px;
    color: #fff;
    text-transform: uppercase;
}

.financial-box {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 15px;
}

.fin-label {
    font-family: monospace;
    font-size: 10px;
    color: #888;
    letter-spacing: .1em;
}

.fin-amount {
    font-family: monospace;
    font-size: 26px;
    font-weight: 900;
    color: #fff;
}

.gauge-container {
    margin-bottom: 24px;
}

.gauge-labels {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-family: monospace;
    font-size: 10px;
    color: #aaa;
    margin-bottom: 8px;
}

.gauge-track {
    width: 100%;
    height: 6px;
    background-color: #1a1a1a;
    overflow: hidden;
    margin-bottom: 8px;
}

.gauge-fill {
    height: 100%;
    background-color: var(--accent, #b02e26);
    transition: width .3s ease;
}

.gauge-foot {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-family: monospace;
    font-size: 10px;
    color: #666;
}

.pieces-allocation-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}

.piece-box {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 12px;
}

.piece-name {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    color: #fff;
    display: block;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.piece-numbers {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 8px;
    font-family: monospace;
    font-size: 10px;
}

.piece-total {
    color: #aaa;
    font-weight: bold;
}

.piece-rest {
    color: #666;
    font-size: 9px;
}

.piece-rest.alert {
    color: var(--accent, #b02e26);
    font-weight: bold;
}

.piece-rest.warning {
    color: #f39c12;
    font-weight: bold;
}

.monitor-actions-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-mon-tool,
.btn-mon-danger {
    padding: 10px 14px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .1em;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-mon-tool {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #ccc;
}

.btn-mon-tool:hover {
    color: #fff;
    border-color: #666;
}

.btn-mon-danger {
    background-color: transparent;
    border: 1px solid rgba(176, 46, 38, .4);
    color: var(--accent, #b02e26);
}

.btn-mon-danger:hover {
    background-color: var(--accent, #b02e26);
    color: #fff;
}

.inline-form {
    margin: 0;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    font-family: monospace;
    font-size: 11px;
    letter-spacing: .12em;
    color: #555;
}


/* =============================================================
   TABLE
============================================================= */

.drops-table-section {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    margin-bottom: 32px;
}

.table-filters-stack {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-lbl {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: .1em;
}

.btn-filter-tag {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #888;
    padding: 3px 8px;
    font-family: monospace;
    font-size: 9px;
    cursor: pointer;
    transition: all .2s;
}

.btn-filter-tag:hover,
.btn-filter-tag.active {
    color: #fff;
    border-color: #666;
}

.btn-filter-tag.active {
    background-color: #1e1e1e;
}

.table-container {
    width: 100%;
    overflow-x: auto;
}

.drops-data-table {
    width: 100%;
    border-collapse: collapse;
    font-family: monospace;
    font-size: 11px;
    text-align: left;
}

.drops-data-table th {
    background-color: #0c0c0c;
    padding: 12px 18px;
    color: #666;
    font-size: 9px;
    letter-spacing: .12em;
    border-bottom: 1px solid var(--border, #242424);
    font-weight: 600;
    white-space: nowrap;
}

.drops-data-table td {
    padding: 16px 18px;
    border-bottom: 1px solid #161616;
    vertical-align: middle;
}

.drops-data-table tr:hover td {
    background-color: #141414;
}

.series-name-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.primary-series {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 14px;
    font-weight: 900;
    color: #fff;
    letter-spacing: .05em;
}

.series-hash {
    font-size: 9px;
    color: #555;
}

.datetime-cell .date {
    color: #aaa;
    white-space: nowrap;
}

.access-pill {
    display: inline-block;
    padding: 2px 6px;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: .1em;
    border: 1px solid transparent;
    white-space: nowrap;
}

.access-pill.public {
    background-color: #1a1a1a;
    border-color: #333;
    color: #ddd;
}

.access-pill.whitelist {
    background-color: rgba(176, 46, 38, .15);
    border-color: var(--accent, #b02e26);
    color: var(--accent, #b02e26);
}

.quota-cell {
    display: flex;
    flex-direction: column;
}

.quota-val {
    color: #fff;
    font-weight: bold;
}

.quota-sub {
    font-size: 9px;
    color: #666;
}

.operational-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: bold;
    white-space: nowrap;
}

.operational-status.live {
    color: var(--accent, #b02e26);
}

.operational-status.locked {
    color: #aaa;
}

.operational-status.sealed {
    color: #555;
}

.dot-live {
    font-size: 8px;
}

.btn-table-action {
    border: 1px solid var(--border, #242424);
    background-color: #161616;
    color: #aaa;
    padding: 4px 10px;
    font-size: 9px;
    letter-spacing: .1em;
    transition: all .2s;
    display: inline-block;
}

.btn-table-action:hover {
    color: #fff;
    border-color: #666;
}

.row-archived td {
    opacity: .6;
}


/* =============================================================
   NEW DROP FORM
============================================================= */

.new-drop-form-section {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    margin-bottom: 32px;
}

.new-drop-form-body {
    padding: 24px;
}

.matrix-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 14px 18px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.matrix-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.matrix-icon {
    font-size: 16px;
    color: var(--accent, #b02e26);
}

.matrix-title {
    font-weight: 800;
    font-size: 11px;
    letter-spacing: .12em;
    color: #fff;
    display: block;
    margin-bottom: 2px;
}

.matrix-desc {
    font-size: 11px;
    color: #666;
    margin: 0;
}

.gateway-tag {
    font-family: monospace;
    font-size: 9px;
    color: #888;
    letter-spacing: .1em;
}

.form-dual-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
}

.input-block {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 18px;
}

.input-block label {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;
    color: #888;
    text-transform: uppercase;
}

.input-block input,
.input-block textarea {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    color: #fff;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 13px;
    padding: 12px 14px;
    outline: none;
    width: 100%;
    transition: border-color .2s;
}

.input-block input:focus,
.input-block textarea:focus {
    border-color: #666;
}

.input-block textarea {
    resize: vertical;
}

.input-error {
    color: var(--accent, #b02e26);
    font-family: monospace;
    font-size: 9px;
}

.input-row-twin {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 16px;
}

.quota-input-badge {
    display: flex;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    align-items: center;
}

.quota-input-badge .prefix {
    padding: 0 10px;
    font-family: monospace;
    color: var(--accent, #b02e26);
    font-weight: bold;
}

.quota-input-badge input {
    border: none;
    background: transparent;
    padding: 12px 6px;
    width: 70px;
}

.quota-input-badge .suffix {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    padding-right: 12px;
    white-space: nowrap;
}


/* =============================================================
   WHITELIST
============================================================= */

.radio-selection-block {
    margin-bottom: 20px;
    border-top: 1px solid #1a1a1a;
    padding-top: 16px;
}

.block-label {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: .12em;
    color: #888;
    display: block;
    margin-bottom: 10px;
}

.radio-options-stack {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.custom-radio-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 10px 14px;
    cursor: pointer;
    position: relative;
}

.custom-radio-row input {
    position: absolute;
    opacity: 0;
}

.radio-bullet {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 1px solid #444;
    background-color: #0c0c0c;
}

.custom-radio-row input:checked + .radio-bullet {
    background-color: var(--accent, #b02e26);
    border-color: var(--accent, #b02e26);
    box-shadow:
        0 0 0 2px #0c0c0c,
        0 0 0 3px var(--accent, #b02e26);
}

.radio-text {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    color: #ccc;
    flex: 1;
}

.reco-tag {
    font-family: monospace;
    font-size: 8px;
    color: var(--accent, #b02e26);
    font-weight: bold;
    letter-spacing: .1em;
}


/* =============================================================
   PRODUITS
============================================================= */

.products-selection-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-bottom: 1px solid var(--border, #242424);
    padding-bottom: 10px;
    margin-bottom: 16px;
}

.col-title {
    font-family: monospace;
    font-size: 10px;
    letter-spacing: .12em;
    color: #888;
}

.selected-count {
    font-family: monospace;
    font-size: 9px;
    color: var(--accent, #b02e26);
    font-weight: bold;
}

.product-selection-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 24px;
}

.product-select-row {
    display: flex;
    align-items: center;
    gap: 12px;
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 10px 14px;
    transition: border-color .2s;
}

.product-select-row.active {
    border-color: #444;
}

.product-select-row:has(input:checked) {
    border-color: var(--accent, #b02e26);
}

.checkbox-col {
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
}

.checkbox-col input {
    position: absolute;
    opacity: 0;
}

.custom-check {
    width: 14px;
    height: 14px;
    border: 1px solid #444;
    background-color: #121212;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #fff;
}

.checkbox-col input:checked + .custom-check {
    background-color: var(--accent, #b02e26);
    border-color: var(--accent, #b02e26);
}

.product-thumb {
    width: 42px;
    height: 48px;
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumb-inner {
    font-family: monospace;
    font-size: 8px;
    color: #555;
}

.product-meta {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.prod-title {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
}

.prod-sub {
    font-family: monospace;
    font-size: 9px;
    color: #666;
}

.product-quota-input {
    display: flex;
    align-items: center;
    gap: 6px;
}

.quota-sm {
    background-color: #161616;
    border: 1px solid var(--border, #242424);
    color: #fff;
    font-family: monospace;
    font-size: 12px;
    font-weight: bold;
    width: 60px;
    padding: 6px;
    text-align: center;
    outline: none;
}

.quota-sm:focus {
    border-color: var(--accent, #b02e26);
}

.unit-lbl {
    font-family: monospace;
    font-size: 9px;
    color: #666;
}

.empty-products {
    padding: 30px;
    text-align: center;
    border: 1px dashed #292929;
    color: #555;
    font-family: monospace;
    font-size: 10px;
}


/* =============================================================
   QUOTA SUMMARY
============================================================= */

.quota-summary-box {
    background-color: #0c0c0c;
    border: 1px solid var(--border, #242424);
    padding: 20px;
}

.summary-caption {
    font-family: monospace;
    font-size: 9px;
    color: #666;
    letter-spacing: .12em;
    display: block;
    margin-bottom: 8px;
}

.calculated-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 8px;
}

.calc-label {
    font-family: monospace;
    font-size: 11px;
    font-weight: bold;
    color: #aaa;
}

.calc-number {
    font-family: monospace;
    font-size: 26px;
    font-weight: 900;
    color: #fff;
}

.calc-expl {
    font-size: 11px;
    color: #666;
    margin: 0 0 20px;
    line-height: 1.4;
}

.form-cta-row {
    display: flex;
    gap: 12px;
}

.btn-submit-drop {
    flex: 1;
    background-color: #fff;
    color: #000;
    border: 1px solid #fff;
    padding: 14px;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: .12em;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit-drop:hover {
    background-color: var(--accent, #b02e26);
    border-color: var(--accent, #b02e26);
    color: #fff;
}

.btn-draft-drop {
    background-color: #101010;
    border: 1px solid var(--border, #242424);
    color: #888;
    padding: 14px 18px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .1em;
    cursor: pointer;
}

.btn-draft-drop:hover {
    color: #fff;
    border-color: #666;
}


/* =============================================================
   FOOTER
============================================================= */

.drops-footer-audit {
    margin-top: 32px;
    border-top: 1px solid var(--border, #242424);
    padding-top: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: monospace;
    font-size: 9px;
    color: #555;
    flex-wrap: wrap;
    gap: 12px;
}

.audit-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.audit-label {
    color: var(--accent, #b02e26);
    font-weight: bold;
}

.audit-sep {
    color: #333;
}

.shield-check {
    color: #777;
    letter-spacing: .08em;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1100px) {

    .sys-left {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }

    .sys-nav {
        gap: 12px;
    }

    .monitor-content-grid {
        grid-template-columns: 1fr;
    }

    .monitor-visual-pane {
        border-right: none;
        border-bottom: 1px solid var(--border, #242424);
    }

}


@media (max-width: 950px) {

    .drops-kpi-grid {
        grid-template-columns: 1fr;
    }

    .form-dual-columns {
        grid-template-columns: 1fr;
    }

    .pieces-allocation-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .admin-drops-page {
        padding: 12px 14px 60px;
    }

    .sys-nav {
        flex-direction: column;
        gap: 8px;
    }

    .sys-nav-link.active::after {
        display: none;
    }

    .header-actions {
        width: 100%;
    }

    .btn-action-outline,
    .btn-action-solid {
        width: 100%;
        justify-content: center;
        text-align: center;
    }

    .input-row-twin {
        grid-template-columns: 1fr;
    }

    .financial-box {
        flex-direction: column;
        gap: 8px;
    }

    .gauge-labels,
    .gauge-foot {
        flex-direction: column;
    }

    .product-select-row {
        flex-wrap: wrap;
    }

    .product-meta {
        min-width: calc(100% - 100px);
    }

    .product-quota-input {
        margin-left: 26px;
    }

    .form-cta-row {
        flex-direction: column;
    }

    .btn-submit-drop,
    .btn-draft-drop {
        width: 100%;
    }

}


@media (prefers-reduced-motion: reduce) {

    .admin-drops-page *,
    .admin-drops-page *::before,
    .admin-drops-page *::after {
        transition: none !important;
        animation: none !important;
    }

}

</style>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       CALCULATEUR DE QUOTA
    ========================================================== */

    const quotaInputs =
        document.querySelectorAll('.quota-sm');

    const quotaDisplay =
        document.getElementById('calculated-quota');

    const selectedDisplay =
        document.getElementById('selected-products-count');

    const maxQuotaInput =
        document.getElementById('max_quota');


    function updateQuota() {

        let total = 0;
        let selected = 0;


        document.querySelectorAll(
            '.product-select-row'
        ).forEach(function (row) {

            const checkbox =
                row.querySelector(
                    'input[type="checkbox"]'
                );

            const quota =
                row.querySelector('.quota-sm');


            if (
                checkbox &&
                checkbox.checked
            ) {

                selected++;


                if (quota) {

                    total += parseInt(
                        quota.value || 0,
                        10
                    );

                }

            }

        });


        if (quotaDisplay) {

            quotaDisplay.textContent =
                total.toLocaleString('fr-FR') +
                ' UNITÉS';

        }


        if (selectedDisplay) {

            selectedDisplay.textContent =
                selected +
                (
                    selected > 1
                        ? ' PRODUITS'
                        : ' PRODUIT'
                );

        }


        if (maxQuotaInput && total > 0) {

            maxQuotaInput.value = total;

        }

    }


    quotaInputs.forEach(function (input) {

        input.addEventListener(
            'input',
            updateQuota
        );

    });


    document.querySelectorAll(
        '.product-select-row input[type="checkbox"]'
    ).forEach(function (checkbox) {

        checkbox.addEventListener(
            'change',
            updateQuota
        );

    });


    updateQuota();


    /* =========================================================
       COUNTDOWN
    ========================================================== */

    const countdown =
        document.querySelector(
            '.countdown-display'
        );


    if (countdown) {

        const endDate =
            countdown.dataset.end;


        if (endDate) {

            const segments =
                countdown.querySelectorAll(
                    '.timer-segment .val'
                );


            function updateCountdown() {

                const now =
                    new Date().getTime();

                const end =
                    new Date(endDate).getTime();

                let difference =
                    end - now;


                if (difference <= 0) {

                    segments.forEach(
                        function (segment) {

                            segment.textContent = '00';

                        }
                    );

                    return;

                }


                const days =
                    Math.floor(
                        difference /
                        (1000 * 60 * 60 * 24)
                    );

                difference %=
                    1000 * 60 * 60 * 24;


                const hours =
                    Math.floor(
                        difference /
                        (1000 * 60 * 60)
                    );

                difference %=
                    1000 * 60 * 60;


                const minutes =
                    Math.floor(
                        difference /
                        (1000 * 60)
                    );

                difference %=
                    1000 * 60;


                const seconds =
                    Math.floor(
                        difference /
                        1000
                    );


                segments[0].textContent =
                    String(days).padStart(2, '0');

                segments[1].textContent =
                    String(hours).padStart(2, '0');

                segments[2].textContent =
                    String(minutes).padStart(2, '0');

                segments[3].textContent =
                    String(seconds).padStart(2, '0');

            }


            updateCountdown();


            setInterval(
                updateCountdown,
                1000
            );

        }

    }

});

</script>

@endsection