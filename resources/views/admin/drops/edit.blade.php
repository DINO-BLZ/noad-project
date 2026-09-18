@extends('layouts.admin')

@section('title', 'Modifier le Drop — ' . $drop->name)

@section('content')

<style>
    /* =========================================================
       NOAD — ADMIN EDIT DROP
       Terrace Brutalism
    ========================================================= */

    .noad-admin-edit-drop-scope {
        --noad-bg: #0c0c0c;
        --noad-panel: #111111;
        --noad-panel-alt: #151515;
        --noad-border: #242424;
        --noad-border-light: #303030;
        --noad-text: #f2f2f2;
        --noad-muted: #8d8d8d;
        --noad-muted-light: #b0b0b0;
        --noad-red: #b02e26;
        --noad-red-light: #d32f2f;
        --noad-green: #3fb950;
        --noad-yellow: #d6a72c;
        --noad-blue: #4b8bea;

        min-height: 100vh;
        background: var(--noad-bg);
        color: var(--noad-text);
        font-family: "Barlow Condensed", Arial, sans-serif;
        padding: 0 0 50px;
    }

    .noad-admin-edit-drop-scope *,
    .noad-admin-edit-drop-scope *::before,
    .noad-admin-edit-drop-scope *::after {
        box-sizing: border-box;
    }

    .noad-admin-edit-drop-scope a {
        color: inherit;
        text-decoration: none;
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .edit-drop-header {
        border-bottom: 1px solid var(--noad-border);
        background: #0c0c0c;
        padding: 28px 32px;
    }

    .edit-drop-breadcrumb {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 22px;
        color: var(--noad-muted);
        font-size: 13px;
        letter-spacing: .02em;
    }

    .edit-drop-breadcrumb a {
        transition: color .2s ease;
    }

    .edit-drop-breadcrumb a:hover {
        color: var(--noad-text);
    }

    .edit-drop-header-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 25px;
    }

    .edit-drop-title-area {
        min-width: 0;
    }

    .edit-drop-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 13px;
    }

    .edit-drop-title-row h1 {
        margin: 0;
        color: var(--noad-text);
        font-size: clamp(28px, 4vw, 42px);
        font-weight: 700;
        line-height: 1;
        letter-spacing: -.02em;
    }

    .edit-drop-title-area > p {
        margin: 11px 0 0;
        color: var(--noad-muted);
        font-size: 15px;
    }

    .drop-status-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 4px 11px;
        border: 1px solid;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .drop-status-active {
        border-color: rgba(63, 185, 80, .4);
        background: rgba(63, 185, 80, .08);
        color: #63d471;
    }

    .drop-status-upcoming {
        border-color: rgba(75, 139, 234, .4);
        background: rgba(75, 139, 234, .08);
        color: #76a7f0;
    }

    .drop-status-ended {
        border-color: var(--noad-border-light);
        background: #171717;
        color: var(--noad-muted-light);
    }

    .edit-drop-header-actions,
    .edit-drop-save-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    /* =========================================================
       BUTTONS
    ========================================================= */

    .edit-drop-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border: 1px solid var(--noad-border-light);
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition:
            background .2s ease,
            border-color .2s ease,
            color .2s ease,
            transform .15s ease;
    }

    .edit-drop-btn:hover {
        transform: translateY(-1px);
    }

    .edit-drop-btn-secondary {
        background: #141414;
        color: var(--noad-text);
    }

    .edit-drop-btn-secondary:hover {
        border-color: #414141;
        background: #1a1a1a;
    }

    .edit-drop-btn-primary {
        border-color: var(--noad-red);
        background: var(--noad-red);
        color: #fff;
    }

    .edit-drop-btn-primary:hover {
        border-color: var(--noad-red-light);
        background: var(--noad-red-light);
    }

    /* =========================================================
       ALERTS
    ========================================================= */

    .edit-drop-alert {
        margin: 24px 32px 0;
        padding: 15px 18px;
        border: 1px solid;
        font-size: 14px;
    }

    .edit-drop-alert-success {
        border-color: rgba(63, 185, 80, .35);
        background: rgba(63, 185, 80, .07);
        color: #72d77d;
    }

    .edit-drop-alert-error {
        border-color: rgba(211, 47, 47, .4);
        background: rgba(211, 47, 47, .07);
        color: #ef6a6a;
    }

    .edit-drop-alert-error strong {
        display: block;
        margin-bottom: 8px;
    }

    .edit-drop-alert-error ul {
        margin: 0;
        padding-left: 20px;
    }

    .edit-drop-alert-error li + li {
        margin-top: 4px;
    }

    /* =========================================================
       LAYOUT
    ========================================================= */

    #drop-edit-form {
        max-width: 1500px;
        margin: 0 auto;
        padding: 32px;
    }

    .edit-drop-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 24px;
        align-items: start;
    }

    .edit-drop-main,
    .edit-drop-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
    }

    /* =========================================================
       CARDS
    ========================================================= */

    .dashboard-card {
        overflow: hidden;
        border: 1px solid var(--noad-border);
        background: var(--noad-panel);
    }

    .dashboard-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--noad-border);
    }

    .dashboard-card-header h2 {
        margin: 0;
        color: var(--noad-text);
        font-size: 20px;
        font-weight: 700;
        line-height: 1.1;
    }

    .dashboard-card-header p {
        margin: 7px 0 0;
        color: var(--noad-muted);
        font-size: 14px;
        line-height: 1.4;
    }

    .dashboard-card-header-meta {
        flex-shrink: 0;
        padding: 7px 10px;
        border: 1px solid var(--noad-border);
        background: #151515;
        color: var(--noad-muted-light);
        font-size: 12px;
        font-weight: 700;
    }

    .dashboard-card-body {
        padding: 24px;
    }

    /* =========================================================
       KPI
    ========================================================= */

    .edit-drop-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .edit-drop-kpi {
        min-height: 145px;
        padding: 20px;
        border-right: 1px solid var(--noad-border);
    }

    .edit-drop-kpi:last-child {
        border-right: 0;
    }

    .edit-drop-kpi-label {
        color: var(--noad-muted);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .edit-drop-kpi-value {
        margin-top: 17px;
        color: var(--noad-text);
        font-size: 30px;
        font-weight: 700;
        line-height: 1;
    }

    .edit-drop-kpi-value span {
        color: var(--noad-muted);
        font-size: 14px;
        font-weight: 600;
    }

    .edit-drop-kpi-meta {
        margin-top: 12px;
        color: #696969;
        font-size: 12px;
    }

    .edit-drop-progress {
        height: 5px;
        margin-top: 18px;
        overflow: hidden;
        background: #242424;
    }

    .edit-drop-progress-bar {
        height: 100%;
        background: var(--noad-red);
        transition: width .3s ease;
    }

    /* =========================================================
       FORM
    ========================================================= */

    .form-group {
        margin-bottom: 21px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #d6d6d6;
        font-size: 14px;
        font-weight: 700;
    }

    .form-input {
        display: block;
        width: 100%;
        min-height: 45px;
        padding: 11px 13px;
        border: 1px solid var(--noad-border-light);
        border-radius: 0;
        outline: none;
        background: #0d0d0d;
        color: var(--noad-text);
        font-family: inherit;
        font-size: 15px;
        transition:
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .form-input::placeholder {
        color: #555;
    }

    .form-input:focus {
        border-color: var(--noad-red);
        box-shadow: 0 0 0 1px var(--noad-red);
    }

    .form-textarea {
        min-height: 145px;
        resize: vertical;
        line-height: 1.5;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-error {
        margin-top: 7px;
        color: #ed6666;
        font-size: 12px;
    }

    /* =========================================================
       PRODUCTS
    ========================================================= */

    .drop-products-list {
        width: 100%;
    }

    .drop-product-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--noad-border);
    }

    .drop-product-row:last-child {
        border-bottom: 0;
    }

    .drop-product-info {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        flex: 1;
    }

    .drop-product-image {
        width: 72px;
        height: 72px;
        flex: 0 0 72px;
        border: 1px solid var(--noad-border-light);
        object-fit: cover;
        background: #0b0b0b;
    }

    .drop-product-image-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .06em;
    }

    .drop-product-details {
        min-width: 0;
    }

    .drop-product-details h3 {
        margin: 0;
        overflow: hidden;
        color: var(--noad-text);
        font-size: 17px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .drop-product-price {
        margin-top: 6px;
        color: var(--noad-muted-light);
        font-size: 14px;
    }

    .drop-product-stock {
        margin-top: 5px;
        color: #686868;
        font-size: 12px;
    }

    .drop-product-quota {
        width: 230px;
        flex-shrink: 0;
    }

    .stepper-box {
        display: flex;
        width: 100%;
        height: 44px;
        border: 1px solid var(--noad-border-light);
        background: #0c0c0c;
    }

    .stepper-btn {
        width: 43px;
        flex: 0 0 43px;
        border: 0;
        background: #151515;
        color: #cfcfcf;
        font-family: inherit;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        transition:
            background .2s ease,
            color .2s ease;
    }

    .stepper-btn:hover {
        background: var(--noad-red);
        color: #fff;
    }

    .stepper-input {
        width: 100%;
        min-width: 0;
        border: 0;
        border-right: 1px solid var(--noad-border);
        border-left: 1px solid var(--noad-border);
        outline: none;
        background: #0c0c0c;
        color: #fff;
        text-align: center;
        font-family: inherit;
        font-size: 16px;
        font-weight: 700;
    }

    .stepper-input:focus {
        box-shadow: inset 0 0 0 1px var(--noad-red);
    }

    .stepper-input::-webkit-outer-spin-button,
    .stepper-input::-webkit-inner-spin-button {
        margin: 0;
        appearance: none;
    }

    .stepper-input[type="number"] {
        appearance: textfield;
    }

    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .empty-state {
        padding: 50px 25px;
        text-align: center;
    }

    .empty-state h3 {
        margin: 0;
        color: #cfcfcf;
        font-size: 16px;
    }

    .empty-state p {
        margin: 8px 0 0;
        color: #626262;
        font-size: 13px;
    }

    /* =========================================================
       WHITELIST
    ========================================================= */

    .whitelist-list {
        width: 100%;
    }

    .whitelist-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 19px 24px;
        border-bottom: 1px solid var(--noad-border);
    }

    .whitelist-row:last-child {
        border-bottom: 0;
    }

    .whitelist-user {
        min-width: 0;
    }

    .whitelist-user-name {
        color: #ededed;
        font-size: 15px;
        font-weight: 700;
    }

    .whitelist-user-email {
        margin-top: 4px;
        color: var(--noad-muted);
        font-size: 13px;
    }

    .whitelist-date {
        margin-top: 5px;
        color: #555;
        font-size: 11px;
    }

    .whitelist-status {
        flex-shrink: 0;
    }

    .whitelist-badge {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 4px 10px;
        border: 1px solid;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .whitelist-badge-approved {
        border-color: rgba(63, 185, 80, .35);
        color: #62d16d;
        background: rgba(63, 185, 80, .07);
    }

    .whitelist-badge-rejected {
        border-color: rgba(211, 47, 47, .35);
        color: #e96666;
        background: rgba(211, 47, 47, .07);
    }

    .whitelist-badge-pending {
        border-color: rgba(214, 167, 44, .35);
        color: #dcb74f;
        background: rgba(214, 167, 44, .07);
    }

    /* =========================================================
       SIDEBAR
    ========================================================= */

    .sidebar-card-body {
        padding: 20px;
    }

    .status-panel {
        padding: 16px;
        border: 1px solid;
    }

    .status-panel strong {
        display: block;
        font-size: 15px;
        font-weight: 700;
    }

    .status-panel p {
        margin: 7px 0 0;
        font-size: 12px;
        line-height: 1.5;
    }

    .status-panel-active {
        border-color: rgba(63, 185, 80, .3);
        background: rgba(63, 185, 80, .05);
        color: #64d270;
    }

    .status-panel-upcoming {
        border-color: rgba(75, 139, 234, .3);
        background: rgba(75, 139, 234, .05);
        color: #78a9ee;
    }

    .status-panel-ended {
        border-color: var(--noad-border-light);
        background: #151515;
        color: #a5a5a5;
    }

    .info-list {
        margin: 0;
        padding: 0;
    }

    .info-list-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #1d1d1d;
    }

    .info-list-row:first-child {
        padding-top: 0;
    }

    .info-list-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .info-list-row dt {
        color: #656565;
        font-size: 13px;
    }

    .info-list-row dd {
        margin: 0;
        color: #d4d4d4;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
    }

    .edit-drop-info-box {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px;
        border: 1px solid rgba(214, 167, 44, .25);
        background: rgba(214, 167, 44, .04);
    }

    .edit-drop-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        border: 1px solid rgba(214, 167, 44, .5);
        color: #dcb74f;
        font-size: 12px;
        font-weight: 700;
    }

    .edit-drop-info-box strong {
        display: block;
        color: #dcb74f;
        font-size: 14px;
    }

    .edit-drop-info-box p {
        margin: 6px 0 0;
        color: #8e7d4a;
        font-size: 12px;
        line-height: 1.5;
    }

    /* =========================================================
       SAVE BAR
    ========================================================= */

    .edit-drop-save-bar {
        position: sticky;
        bottom: 15px;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 25px;
        padding: 15px 18px;
        border: 1px solid var(--noad-border-light);
        background: rgba(17, 17, 17, .97);
        box-shadow: 0 15px 40px rgba(0, 0, 0, .4);
    }

    .edit-drop-save-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .edit-drop-save-info strong {
        color: #e8e8e8;
        font-size: 14px;
    }

    .edit-drop-save-info span {
        color: #656565;
        font-size: 12px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .edit-drop-layout {
            grid-template-columns: 1fr;
        }

        .edit-drop-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .edit-drop-sidebar .dashboard-card:last-child {
            grid-column: 1 / -1;
        }

        .edit-drop-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .edit-drop-kpi:nth-child(2) {
            border-right: 0;
        }

        .edit-drop-kpi:nth-child(-n + 2) {
            border-bottom: 1px solid var(--noad-border);
        }

    }

    @media (max-width: 760px) {

        .edit-drop-header {
            padding: 22px 18px;
        }

        #drop-edit-form {
            padding: 22px 18px;
        }

        .edit-drop-header-row {
            align-items: stretch;
            flex-direction: column;
        }

        .edit-drop-header-actions {
            width: 100%;
        }

        .edit-drop-header-actions .edit-drop-btn {
            flex: 1;
        }

        .edit-drop-kpi-grid {
            grid-template-columns: 1fr;
        }

        .edit-drop-kpi {
            border-right: 0;
            border-bottom: 1px solid var(--noad-border);
        }

        .edit-drop-kpi:last-child {
            border-bottom: 0;
        }

        .form-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .drop-product-row {
            align-items: stretch;
            flex-direction: column;
        }

        .drop-product-quota {
            width: 100%;
        }

        .whitelist-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-drop-sidebar {
            display: flex;
        }

        .edit-drop-save-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .edit-drop-save-actions {
            width: 100%;
        }

        .edit-drop-save-actions .edit-drop-btn {
            flex: 1;
        }

    }

    @media (max-width: 480px) {

        .edit-drop-title-row h1 {
            font-size: 28px;
        }

        .dashboard-card-header {
            padding: 18px;
        }

        .dashboard-card-body {
            padding: 18px;
        }

        .drop-product-row {
            padding: 18px;
        }

        .drop-product-image {
            width: 58px;
            height: 58px;
            flex-basis: 58px;
        }

        .edit-drop-header-actions,
        .edit-drop-save-actions {
            flex-direction: column;
        }

        .edit-drop-header-actions .edit-drop-btn,
        .edit-drop-save-actions .edit-drop-btn {
            width: 100%;
        }

    }
</style>


<div class="noad-admin-edit-drop-scope">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <header class="edit-drop-header">

        <div class="edit-drop-breadcrumb">

            <a href="{{ route('admin.drops.index') }}">
                Drops
            </a>

            <span>/</span>

            <span>
                Modifier
            </span>

        </div>


        <div class="edit-drop-header-row">

            <div class="edit-drop-title-area">

                <div class="edit-drop-title-row">

                    <h1>
                        {{ $drop->name }}
                    </h1>


                    {{-- Le statut est calculé par le modèle.
                         Il n'est PAS envoyé dans le formulaire. --}}

                    @if ($drop->isActive())

                        <span class="drop-status-badge drop-status-active">
                            Actif
                        </span>

                    @elseif ($drop->isUpcoming())

                        <span class="drop-status-badge drop-status-upcoming">
                            À venir
                        </span>

                    @else

                        <span class="drop-status-badge drop-status-ended">
                            Terminé
                        </span>

                    @endif

                </div>


                <p>
                    Modification des paramètres et des quotas du Drop.
                </p>

            </div>


            <div class="edit-drop-header-actions">

                <a
                    href="{{ route('admin.drops.index') }}"
                    class="edit-drop-btn edit-drop-btn-secondary"
                >
                    Retour
                </a>


                <button
                    type="submit"
                    form="drop-edit-form"
                    class="edit-drop-btn edit-drop-btn-primary"
                >
                    Enregistrer
                </button>

            </div>

        </div>

    </header>


    {{-- =========================================================
         ALERTS
    ========================================================== --}}

    @if (session('success'))

        <div class="edit-drop-alert edit-drop-alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if ($errors->any())

        <div class="edit-drop-alert edit-drop-alert-error">

            <strong>
                Impossible d'enregistrer les modifications :
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         FORMULAIRE PRINCIPAL
    ========================================================== --}}

    <form
        id="drop-edit-form"
        method="POST"
        action="{{ route('admin.drops.update', $drop) }}"
    >

        @csrf

        @method('PUT')


        <div class="edit-drop-layout">


            {{-- =================================================
                 COLONNE PRINCIPALE
            ================================================== --}}

            <main class="edit-drop-main">


                {{-- =================================================
                     KPI
                ================================================== --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                Vue d'ensemble
                            </h2>

                            <p>
                                Données calculées à partir des ventes confirmées.
                            </p>

                        </div>

                    </div>


                    <div class="edit-drop-kpi-grid">


                        {{-- CA --}}

                        <div class="edit-drop-kpi">

                            <div class="edit-drop-kpi-label">
                                Chiffre d'affaires
                            </div>

                            <div class="edit-drop-kpi-value">

                                {{ number_format($dropRevenue, 2, ',', ' ') }}

                                <span>
                                    DA
                                </span>

                            </div>

                            <div class="edit-drop-kpi-meta">
                                Paid · Shipped · Delivered
                            </div>

                        </div>


                        {{-- VENTES --}}

                        <div class="edit-drop-kpi">

                            <div class="edit-drop-kpi-label">
                                Articles vendus
                            </div>

                            <div class="edit-drop-kpi-value">
                                {{ number_format($dropSold, 0, ',', ' ') }}
                            </div>

                            <div class="edit-drop-kpi-meta">
                                Commandes confirmées
                            </div>

                        </div>


                        {{-- QUOTA --}}

                        <div class="edit-drop-kpi">

                            <div class="edit-drop-kpi-label">
                                Quota total
                            </div>

                            <div class="edit-drop-kpi-value">
                                {{ number_format($dropQuota, 0, ',', ' ') }}
                            </div>

                            <div class="edit-drop-kpi-meta">
                                Produits associés
                            </div>

                        </div>


                        {{-- PROGRESSION --}}

                        <div class="edit-drop-kpi">

                            <div class="edit-drop-kpi-label">
                                Progression
                            </div>

                            <div class="edit-drop-kpi-value">
                                {{ $dropSoldPercentage }}%
                            </div>


                            <div class="edit-drop-progress">

                                <div
                                    class="edit-drop-progress-bar"
                                    style="width: {{ min($dropSoldPercentage, 100) }}%;"
                                ></div>

                            </div>


                            <div class="edit-drop-kpi-meta">

                                {{ $dropSold }}
                                /
                                {{ $dropQuota }}
                                unités

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     PARAMÈTRES GÉNÉRAUX
                ================================================== --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                Paramètres généraux
                            </h2>

                            <p>
                                Informations principales du Drop.
                            </p>

                        </div>

                    </div>


                    <div class="dashboard-card-body">


                        {{-- NOM --}}

                        <div class="form-group">

                            <label
                                for="name"
                                class="form-label"
                            >
                                Nom du Drop
                            </label>


                            <input
                                id="name"
                                type="text"
                                name="name"
                                class="form-input"
                                value="{{ old('name', $drop->name) }}"
                                required
                            >


                            @error('name')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- DESCRIPTION --}}

                        <div class="form-group">

                            <label
                                for="description"
                                class="form-label"
                            >
                                Description
                            </label>


                            <textarea
                                id="description"
                                name="description"
                                rows="6"
                                class="form-input form-textarea"
                                placeholder="Description du Drop..."
                            >{{ old('description', $drop->description) }}</textarea>


                            @error('description')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        <div class="form-grid-2">


                            {{-- DATE DEBUT --}}

                            <div class="form-group">

                                <label
                                    for="start_date"
                                    class="form-label"
                                >
                                    Date de début
                                </label>


                                <input
                                    id="start_date"
                                    type="datetime-local"
                                    name="start_date"
                                    class="form-input"
                                    value="{{ old('start_date', optional($drop->start_date)->format('Y-m-d\TH:i')) }}"
                                    required
                                >


                                @error('start_date')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- DATE FIN --}}

                            <div class="form-group">

                                <label
                                    for="end_date"
                                    class="form-label"
                                >
                                    Date de fin
                                </label>


                                <input
                                    id="end_date"
                                    type="datetime-local"
                                    name="end_date"
                                    class="form-input"
                                    value="{{ old('end_date', optional($drop->end_date)->format('Y-m-d\TH:i')) }}"
                                    required
                                >


                                @error('end_date')

                                    <div class="form-error">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        </div>


                        {{-- MAX WHITELIST --}}

                        <div class="form-group">

                            <label
                                for="max_whitelist_slots"
                                class="form-label"
                            >
                                Nombre maximum de places whitelist
                            </label>


                            <input
                                id="max_whitelist_slots"
                                type="number"
                                name="max_whitelist_slots"
                                min="0"
                                class="form-input"
                                value="{{ old('max_whitelist_slots', $drop->max_whitelist_slots) }}"
                            >


                            @error('max_whitelist_slots')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     PRODUITS ET QUOTAS
                ================================================== --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                Produits et quotas
                            </h2>

                            <p>
                                Ajuste le quota des produits déjà associés à ce Drop.
                            </p>

                        </div>


                        <div class="dashboard-card-header-meta">

                            {{ $drop->products->count() }}
                            produit(s)

                        </div>

                    </div>


                    <div class="drop-products-list">


                        @forelse ($drop->products as $product)


                            {{-- =================================================
                                 CORRECTIF CRITIQUE
                                 Le contrôleur update() utilise :

                                 $productIds = $request->input('products', []);
                                 $drop->products()->sync(...);

                                 Sans ce hidden, products[] serait vide et
                                 sync() détacherait tous les produits.
                            ================================================== --}}

                            <input
                                type="hidden"
                                name="products[]"
                                value="{{ $product->id }}"
                            >


                            <div class="drop-product-row">


                                {{-- PRODUIT --}}

                                <div class="drop-product-info">


                                    @if ($product->image)

                                        <img
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="drop-product-image"
                                        >

                                    @else

                                        <div class="drop-product-image drop-product-image-empty">
                                            NO IMAGE
                                        </div>

                                    @endif


                                    <div class="drop-product-details">

                                        <h3>
                                            {{ $product->name }}
                                        </h3>


                                        <div class="drop-product-price">

                                            {{ number_format((float) $product->price, 2, ',', ' ') }}
                                            DA

                                        </div>


                                        @if ($product->variants->isNotEmpty())

                                            <div class="drop-product-stock">

                                                Stock catalogue :
                                                {{ $product->variants->sum('stock') }}

                                            </div>

                                        @endif

                                    </div>

                                </div>


                                {{-- QUOTA --}}

                                <div class="drop-product-quota">

                                    <label
                                        for="product_quota_{{ $product->id }}"
                                        class="form-label"
                                    >
                                        Quota
                                    </label>


                                    <div class="stepper-box">


                                        <button
                                            type="button"
                                            class="stepper-btn quota-minus"
                                            data-target="product_quota_{{ $product->id }}"
                                            aria-label="Diminuer le quota"
                                        >
                                            −
                                        </button>


                                        <input
                                            id="product_quota_{{ $product->id }}"
                                            type="number"
                                            name="product_quotas[{{ $product->id }}]"
                                            class="stepper-input"
                                            min="0"
                                            step="1"
                                            value="{{ old('product_quotas.' . $product->id, $product->pivot->quota ?? 0) }}"
                                        >


                                        <button
                                            type="button"
                                            class="stepper-btn quota-plus"
                                            data-target="product_quota_{{ $product->id }}"
                                            aria-label="Augmenter le quota"
                                        >
                                            +
                                        </button>

                                    </div>


                                    @error('product_quotas.' . $product->id)

                                        <div class="form-error">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>

                            </div>


                        @empty


                            <div class="empty-state">

                                <h3>
                                    Aucun produit associé
                                </h3>

                                <p>
                                    Aucun produit n'est actuellement associé à ce Drop.
                                </p>

                            </div>


                        @endforelse

                    </div>

                </section>


                {{-- =================================================
                     WHITELIST
                ================================================== --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                Whitelist
                            </h2>

                            <p>
                                Demandes associées à ce Drop.
                            </p>

                        </div>


                        <div class="dashboard-card-header-meta">

                            {{ $whitelistCount }}
                            demande(s)

                        </div>

                    </div>


                    @if ($whitelistRequests->isNotEmpty())


                        <div class="whitelist-list">


                            @foreach ($whitelistRequests as $request)


                                <div class="whitelist-row">


                                    <div class="whitelist-user">

                                        <div class="whitelist-user-name">
                                            {{ $request->user->name ?? 'Utilisateur supprimé' }}
                                        </div>


                                        @if ($request->user)

                                            <div class="whitelist-user-email">
                                                {{ $request->user->email }}
                                            </div>

                                        @endif


                                        <div class="whitelist-date">
                                            {{ optional($request->created_at)->format('d/m/Y H:i') }}
                                        </div>

                                    </div>


                                    <div class="whitelist-status">


                                        @if ($request->status === 'approved')

                                            <span class="whitelist-badge whitelist-badge-approved">
                                                Approuvée
                                            </span>


                                        @elseif ($request->status === 'rejected')

                                            <span class="whitelist-badge whitelist-badge-rejected">
                                                Refusée
                                            </span>


                                        @else

                                            <span class="whitelist-badge whitelist-badge-pending">
                                                En attente
                                            </span>

                                        @endif

                                    </div>

                                </div>


                            @endforeach

                        </div>


                    @else


                        <div class="empty-state">

                            <h3>
                                Aucune demande
                            </h3>

                            <p>
                                Aucune demande de whitelist n'est actuellement associée à ce Drop.
                            </p>

                        </div>


                    @endif

                </section>

            </main>


            {{-- =================================================
                 SIDEBAR
            ================================================== --}}

            <aside class="edit-drop-sidebar">


                {{-- ETAT DU DROP --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                État du Drop
                            </h2>

                        </div>

                    </div>


                    <div class="sidebar-card-body">


                        @if ($drop->isActive())

                            <div class="status-panel status-panel-active">

                                <strong>
                                    Drop actif
                                </strong>

                                <p>
                                    Le Drop est actuellement dans sa période de disponibilité.
                                </p>

                            </div>


                        @elseif ($drop->isUpcoming())

                            <div class="status-panel status-panel-upcoming">

                                <strong>
                                    Drop à venir
                                </strong>

                                <p>
                                    Le Drop n'est pas encore commencé.
                                </p>

                            </div>


                        @else

                            <div class="status-panel status-panel-ended">

                                <strong>
                                    Drop terminé
                                </strong>

                                <p>
                                    La période du Drop est terminée.
                                </p>

                            </div>

                        @endif

                    </div>

                </section>


                {{-- INFORMATIONS --}}

                <section class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <h2>
                                Informations
                            </h2>

                        </div>

                    </div>


                    <div class="sidebar-card-body">

                        <dl class="info-list">


                            <div class="info-list-row">

                                <dt>
                                    Début
                                </dt>

                                <dd>
                                    {{ optional($drop->start_date)->format('d/m/Y H:i') }}
                                </dd>

                            </div>


                            <div class="info-list-row">

                                <dt>
                                    Fin
                                </dt>

                                <dd>
                                    {{ optional($drop->end_date)->format('d/m/Y H:i') }}
                                </dd>

                            </div>


                            <div class="info-list-row">

                                <dt>
                                    Produits
                                </dt>

                                <dd>
                                    {{ $drop->products->count() }}
                                </dd>

                            </div>


                            <div class="info-list-row">

                                <dt>
                                    Quota
                                </dt>

                                <dd>
                                    {{ number_format($dropQuota, 0, ',', ' ') }}
                                </dd>

                            </div>


                            <div class="info-list-row">

                                <dt>
                                    Whitelist
                                </dt>

                                <dd>

                                    {{ $whitelistCount }}
                                    /
                                    {{ $drop->max_whitelist_slots ?? 0 }}

                                </dd>

                            </div>

                        </dl>

                    </div>

                </section>


                {{-- INFORMATION PRODUITS --}}

                <section class="dashboard-card">

                    <div class="sidebar-card-body">

                        <div class="edit-drop-info-box">

                            <div class="edit-drop-info-icon">
                                !
                            </div>


                            <div>

                                <strong>
                                    Gestion des produits
                                </strong>

                                <p>
                                    Cette page permet uniquement de modifier
                                    les quotas des produits déjà associés au Drop.
                                    La composition du Drop n'est pas modifiée ici.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>

            </aside>

        </div>


        {{-- =========================================================
             BARRE DE SAUVEGARDE
        ========================================================== --}}

        <div class="edit-drop-save-bar">

            <div class="edit-drop-save-info">

                <strong>
                    Prêt à enregistrer ?
                </strong>

                <span>
                    Les paramètres et quotas seront sauvegardés.
                </span>

            </div>


            <div class="edit-drop-save-actions">

                <a
                    href="{{ route('admin.drops.index') }}"
                    class="edit-drop-btn edit-drop-btn-secondary"
                >
                    Annuler
                </a>


                <button
                    type="submit"
                    class="edit-drop-btn edit-drop-btn-primary"
                >
                    Enregistrer les modifications
                </button>

            </div>

        </div>

    </form>

</div>


{{-- =============================================================
     JAVASCRIPT — STEPPERS QUOTAS
============================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.quota-minus').forEach(function (button) {

        button.addEventListener('click', function () {

            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            const currentValue = parseInt(input.value || '0', 10);

            input.value = Math.max(0, currentValue - 1);

        });

    });


    document.querySelectorAll('.quota-plus').forEach(function (button) {

        button.addEventListener('click', function () {

            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            const currentValue = parseInt(input.value || '0', 10);

            input.value = currentValue + 1;

        });

    });

});
</script>

@endsection