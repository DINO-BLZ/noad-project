<header class="site-header">

    @php
        if (auth()->check()) {
            $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
        } else {
            $cartCount = \App\Models\CartItem::whereNull('user_id')->where('session_id', session()->getId())->sum('quantity');
        }
    @endphp

    {{-- GAUCHE : NAVIGATION PRINCIPALE --}}
    <div class="site-header__left">
        @unless(auth()->check() && auth()->user()->is_admin)
            <nav class="site-header__nav">
                <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') ? 'is-active' : '' }}">Boutique</a>
                <a href="{{ route('drops.index') }}" class="{{ request()->routeIs('drops.*') ? 'is-active' : '' }}">Drops</a>
            </nav>
        @endunless
    </div>

    {{-- CENTRE : LOGO NOAD STRICTEMENT CENTRÉ --}}
    <div class="site-header__center">
        <a href="/" class="site-header__logo" aria-label="NOAD — Accueil">
            <img src="/images/noad-logo.png" alt="Noad Logo">
        </a>
    </div>

    {{-- DROITE : ACTIONS (RECHERCHE, COMPTE, PANIER, THÈME) --}}
    <div class="site-header__right">

        @if(auth()->check() && auth()->user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="site-header__admin-link font-mono">Dashboard Admin</a>
            <a href="{{ route('logout') }}" class="site-header__link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Déconnexion
            </a>
        @else
            {{-- BARRE DE RECHERCHE MINIMALE --}}
            <div class="site-header__search-wrapper">
                <form action="{{ route('search.index') }}" method="GET" class="site-header__search">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..." aria-label="Rechercher un produit" autocomplete="off">
                </form>
                <div class="site-header__search-results"></div>
            </div>

            {{-- MENU COMPTE DÉROULANT OU LIEN LOGIN --}}
            @auth
                <div class="site-header__account" id="account-dropdown">
                    <button type="button" class="account-trigger" id="account-btn" aria-haspopup="true" aria-expanded="false">
                        <span class="account-label">Compte</span>
                        <svg class="account-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="account-dropdown-menu font-mono">
                        <div class="account-dropdown-user">
                            <span class="account-dropdown-tag">COMPTE ACTIF</span>
                            <span class="account-dropdown-name">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="account-dropdown-divider"></div>
                        <a href="{{ route('orders.index') }}" class="account-dropdown-item">
                            <span class="dropdown-item-icon">📦</span>
                            <span>Mes commandes</span>
                        </a>
                        <a href="{{ route('whitelist.index') }}" class="account-dropdown-item">
                            <span class="dropdown-item-icon">🎟️</span>
                            <span>Mes whitelists</span>
                        </a>
                        <div class="account-dropdown-divider"></div>
                        <a href="{{ route('logout') }}" class="account-dropdown-item account-dropdown-item--logout"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span class="dropdown-item-icon">✕</span>
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="site-header__link">Compte</a>
            @endauth

            {{-- PANIER AVEC BADGE --}}
            <a href="{{ route('cart.index') }}" class="site-header__cart-link">
                Panier
                <span class="site-header__cart-badge" id="cart-badge" @if($cartCount == 0) style="display:none;" @endif>{{ $cartCount }}</span>
            </a>
        @endif

        {{-- FORMULAIRE DÉCONNEXION CACHÉ --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

</header>

{{-- TIROIR PANIER --}}
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer__overlay" id="cart-drawer-overlay"></div>
    <div class="cart-drawer__panel">
        <div class="cart-drawer__head">
            <h3>Panier</h3>
            <button type="button" class="cart-drawer__close" id="cart-drawer-close">&times;</button>
        </div>
        <div class="cart-drawer__items" id="cart-drawer-items"></div>
        <div class="cart-drawer__foot">
            <div class="cart-drawer__total">
                <span>Total</span>
                <span id="cart-drawer-total">0 DA</span>
            </div>
            <a href="{{ route('checkout.index') }}" class="cart-drawer__pay">Payer</a>
            <button type="button" class="cart-drawer__later" id="cart-drawer-later">Plus tard</button>
        </div>
    </div>
</div>

<style>
:root{
    --bg:#0A0A0A;
    --text:#F5F5F5;
    --accent:#B02E26;
    --border:rgba(245,245,245,.12);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:"Archivo",sans-serif;
    background:var(--bg);
    color:var(--text);
    transition:.3s ease;
}

/* ==========================================================================
   HEADER RESTYLISÉ & ÉQUILIBRÉ
   ========================================================================== */

.site-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.15rem 3rem;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
    backdrop-filter: blur(12px);
    transition: background .3s ease, border-color .3s ease;
}

/* 1. GAUCHE */
.site-header__left {
    display: flex;
    align-items: center;
    flex: 1 1 0%;
    justify-content: flex-start;
}

.site-header__nav {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.site-header__nav a,
.site-header__link {
    position: relative;
    color: var(--text);
    text-decoration: none;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    transition: color .2s ease;
}

.site-header__nav a::after,
.site-header__link::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -6px;
    width: 0;
    height: 2px;
    background: var(--accent);
    transition: width .25s ease;
}

.site-header__nav a:hover,
.site-header__link:hover,
.site-header__nav a.is-active {
    color: var(--accent);
}

.site-header__nav a:hover::after,
.site-header__link:hover::after,
.site-header__nav a.is-active::after {
    width: 100%;
}

/* 2. CENTRE : PARFAITEMENT CENTRÉ */
.site-header__center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
}

.site-header__logo {
    display: inline-block;
    line-height: 1;
}

.site-header__logo img {
    display: block;
    height: 32px;
    width: auto;
    transition: transform .25s ease, filter .25s ease;
}

.site-header__logo:hover img {
    transform: scale(1.04);
}

html[data-theme="light"] .site-header__logo img {
    filter: invert(1);
}

/* 3. DROITE */
.site-header__right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1.5rem;
    flex: 1 1 0%;
}

/* SEARCH */
.site-header__search-wrapper {
    position: relative;
}

.site-header__search input {
    width: 150px;
    padding: .45rem .85rem;
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255, 255, 255, .03);
    color: var(--text);
    font-size: .78rem;
    letter-spacing: .03em;
    font-family: inherit;
    transition: all .25s ease;
}

.site-header__search input::placeholder {
    color: var(--text);
    opacity: .45;
}

.site-header__search input:focus {
    outline: none;
    border-color: var(--accent);
    background: rgba(255, 255, 255, .06);
    width: 190px;
}

.site-header__search-results {
    display: none;
    position: absolute;
    top: calc(100% + .6rem);
    right: 0;
    width: 280px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.5);
    z-index: 1050;
}

.site-header__search-results.is-open {
    display: block;
}

.site-header__search-item {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .6rem .9rem;
    text-decoration: none;
    color: var(--text);
    border-bottom: 1px solid var(--border);
    transition: .2s;
}

.site-header__search-item:last-child {
    border-bottom: none;
}

.site-header__search-item:hover {
    background: var(--accent);
}

.site-header__search-item:hover,
.site-header__search-item:hover .site-header__search-item-name,
.site-header__search-item:hover .site-header__search-item-price {
    color: #fff;
}

.site-header__search-item img {
    width: 34px;
    height: 34px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.site-header__search-item-name {
    flex: 1;
    font-size: .8rem;
}

.site-header__search-item-price {
    font-size: .75rem;
    opacity: .7;
    white-space: nowrap;
}

.site-header__search-empty {
    padding: .9rem;
    font-size: .8rem;
    opacity: .6;
    text-align: center;
}

/* MENU COMPTE DÉROULANT */
.site-header__account {
    position: relative;
}

.account-trigger {
    background: transparent;
    border: none;
    color: var(--text);
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 0;
    font-family: inherit;
    transition: color .2s ease;
}

.account-trigger:hover,
.site-header__account:hover .account-trigger {
    color: var(--accent);
}

.account-chevron {
    transition: transform .25s ease;
}

.site-header__account:hover .account-chevron,
.account-dropdown-menu.is-open ~ .account-trigger .account-chevron {
    transform: rotate(180deg);
}

.account-dropdown-menu {
    position: absolute;
    top: calc(100% + .5rem);
    right: 0;
    width: 220px;
    background: var(--bg);
    border: 1px solid var(--border);
    padding: .5rem 0;
    box-shadow: 0 12px 35px rgba(0, 0, 0, .55);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity .2s ease, transform .2s ease, visibility .2s;
    z-index: 1050;
}

.site-header__account:hover .account-dropdown-menu,
.site-header__account:focus-within .account-dropdown-menu,
.account-dropdown-menu.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.account-dropdown-user {
    padding: .6rem 1rem .4rem;
}

.account-dropdown-tag {
    display: block;
    font-size: .62rem;
    letter-spacing: .12em;
    color: var(--accent);
    margin-bottom: .2rem;
}

.account-dropdown-name {
    display: block;
    font-size: .82rem;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.account-dropdown-divider {
    height: 1px;
    background: var(--border);
    margin: .4rem 0;
}

.account-dropdown-item {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .6rem 1rem;
    color: var(--text);
    text-decoration: none;
    font-size: .78rem;
    letter-spacing: .06em;
    transition: all .2s ease;
}

.account-dropdown-item:hover {
    background: rgba(255, 255, 255, .05);
    color: var(--accent);
    padding-left: 1.25rem;
}

.account-dropdown-item--logout:hover {
    color: #ef4444;
}

.dropdown-item-icon {
    font-size: .85rem;
    opacity: .75;
}

/* PANIER & BADGE */
.site-header__cart-link {
    position: relative;
    color: var(--text);
    text-decoration: none;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    transition: color .2s ease;
}

.site-header__cart-link:hover {
    color: var(--accent);
}

.site-header__cart-badge {
    position: absolute;
    top: -8px;
    right: -14px;
    background: var(--accent);
    color: #fff;
    font-size: .62rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    box-shadow: 0 2px 6px rgba(176, 46, 38, .4);
}

/* ADMIN LINK */
.site-header__admin-link {
    background: var(--accent);
    color: #fff !important;
    padding: .45rem .9rem;
    border-radius: 4px;
    font-size: .75rem !important;
    letter-spacing: .08em;
    text-decoration: none;
    font-weight: 700;
    transition: opacity .2s;
}

.site-header__admin-link:hover {
    opacity: .9;
}

/* CART DRAWER */
.cart-drawer {
    position: fixed;
    inset: 0;
    z-index: 2000;
    pointer-events: none;
}

.cart-drawer.is-open {
    pointer-events: auto;
}

.cart-drawer__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.5);
    opacity: 0;
    transition: .3s;
}

.cart-drawer.is-open .cart-drawer__overlay {
    opacity: 1;
}

.cart-drawer__panel {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 380px;
    max-width: 90vw;
    background: var(--bg);
    border-left: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: .3s ease;
}

.cart-drawer.is-open .cart-drawer__panel {
    transform: translateX(0);
}

.cart-drawer__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.3rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.cart-drawer__head h3 {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.cart-drawer__close {
    background: none;
    border: none;
    color: var(--text);
    font-size: 1.4rem;
    cursor: pointer;
    line-height: 1;
}

.cart-drawer__items {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.5rem;
}

.cart-drawer__item {
    display: flex;
    gap: .8rem;
    align-items: center;
    padding: .8rem 0;
    border-bottom: 1px solid var(--border);
}

.cart-drawer__item img {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.cart-drawer__item-info {
    flex: 1;
}

.cart-drawer__item-info p {
    font-size: .75rem;
    opacity: .7;
}

.cart-drawer__item-name {
    font-size: .85rem;
    text-transform: uppercase;
}

.cart-drawer__item-subtotal {
    font-size: .8rem;
    font-weight: 700;
    white-space: nowrap;
}

.cart-drawer__empty {
    text-align: center;
    opacity: .6;
    padding: 2rem 0;
    font-size: .85rem;
}

.cart-drawer__foot {
    padding: 1.3rem 1.5rem;
    border-top: 1px solid var(--border);
}

.cart-drawer__total {
    display: flex;
    justify-content: space-between;
    font-weight: 900;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

.cart-drawer__pay {
    display: block;
    text-align: center;
    background: var(--accent);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .9rem;
    border-radius: 4px;
    margin-bottom: .6rem;
    transition: opacity .2s;
}

.cart-drawer__pay:hover {
    opacity: .92;
}

.cart-drawer__later {
    display: block;
    width: 100%;
    text-align: center;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: .8rem;
    padding: .8rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all .2s;
}

.cart-drawer__later:hover {
    border-color: var(--text);
}

/* RESPONSIVE */
@media(max-width: 992px){
    .site-header {
        padding: 1rem 1.5rem;
    }
    .site-header__search-wrapper {
        display: none;
    }
}

@media(max-width: 768px){
    .site-header__left {
        display: none;
    }
    .site-header__center {
        position: static;
        transform: none;
    }
    .site-header__right {
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var accountDropdown = document.getElementById('account-dropdown');
    var accountBtn = document.getElementById('account-btn');

    if (accountBtn && accountDropdown) {
        var menu = accountDropdown.querySelector('.account-dropdown-menu');

        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (menu) {
                menu.classList.toggle('is-open');
                var isExpanded = menu.classList.contains('is-open');
                accountBtn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            }
        });

        document.addEventListener('click', function(e) {
            if (menu && !accountDropdown.contains(e.target)) {
                menu.classList.remove('is-open');
                accountBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
</script>