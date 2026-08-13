<header class="site-header">

    @php
        if (auth()->check()) {
            $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
        } else {
            $cartCount = \App\Models\CartItem::whereNull('user_id')->where('session_id', session()->getId())->sum('quantity');
        }
    @endphp

    <nav class="site-header__nav">
        <a href="{{ route('shop.index') }}">Boutique</a>
        <a href="{{ route('drops.index') }}">Drops</a>
    </nav>

    <a href="/" class="site-header__logo">
        <img src="/images/noad-logo.png" alt="Noad Logo">
    </a>

    <div class="site-header__actions">
        @auth
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Déconnexion
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@else
    <a href="{{ route('login') }}">Compte</a>
@endauth
        <div class="site-header__search-wrapper">
            <form action="{{ route('search.index') }}" method="GET" class="site-header__search">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..." aria-label="Rechercher un produit" autocomplete="off">
            </form>
            <div class="site-header__search-results"></div>
        </div>
        <a href="{{ route('cart.index') }}" class="site-header__cart-link">
            Panier
            <span class="site-header__cart-badge" id="cart-badge" @if($cartCount == 0) style="display:none;" @endif>{{ $cartCount }}</span>
        </a>

        <button id="theme-toggle" class="theme-toggle" aria-label="Changer de thème">
            <span class="theme-toggle__dot"></span>
        </button>
    </div>

</header>

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

html[data-theme="light"]{
    --bg:#F5F5F5;
    --text:#0A0A0A;
    --accent:#B02E26;
    --border:rgba(10,10,10,.12);
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

/* HEADER */

.site-header{
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    padding:1.3rem 3rem;
    border-bottom:1px solid var(--border);
    background:var(--bg);
    position:sticky;
    top:0;
    z-index:1000;
}

/* LEFT */

.site-header__nav{
    display:flex;
    align-items:center;
    gap:2rem;
}

.site-header__nav a,
.site-header__actions a{
    position:relative;
    color:var(--text);
    text-decoration:none;
    font-size:.9rem;
    font-weight:600;
    letter-spacing:.08em;
    text-transform:uppercase;
    transition:.3s;
}

.site-header__nav a::after,
.site-header__actions a::after{
    content:"";
    position:absolute;
    left:0;
    bottom:-6px;
    width:0;
    height:2px;
    background:var(--accent);
    transition:.3s;
}

.site-header__nav a:hover,
.site-header__actions a:hover{
    color:var(--accent);
}

.site-header__nav a:hover::after,
.site-header__actions a:hover::after{
    width:100%;
}

/* LOGO */

.site-header__logo{
    justify-self:center;
}

.site-header__logo img{
    display:block;
    height:34px;
}

html[data-theme="light"] .site-header__logo img{
    filter:invert(1);
}

/* RIGHT */

.site-header__actions{
    justify-self:end;
    display:flex;
    align-items:center;
    gap:1.5rem;
}

/* SEARCH */

.site-header__search-wrapper{
    position:relative;
}

.site-header__search input{
    width:160px;
    padding:.5rem .8rem;
    border:1px solid var(--border);
    border-radius:20px;
    background:transparent;
    color:var(--text);
    font-size:.8rem;
    letter-spacing:.03em;
    transition:.3s;
}

.site-header__search input::placeholder{
    color:var(--text);
    opacity:.5;
}

.site-header__search input:focus{
    outline:none;
    border-color:var(--accent);
    width:200px;
}

.site-header__search-results{
    display:none;
    position:absolute;
    top:calc(100% + .6rem);
    right:0;
    width:280px;
    background:var(--bg);
    border:1px solid var(--border);
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.4);
}

.site-header__search-results.is-open{
    display:block;
}

.site-header__search-item{
    display:flex;
    align-items:center;
    gap:.7rem;
    padding:.6rem .9rem;
    text-decoration:none;
    color:var(--text);
    border-bottom:1px solid var(--border);
    transition:.2s;
}

.site-header__search-item:last-child{
    border-bottom:none;
}

.site-header__search-item:hover{
    background:var(--accent);
}

.site-header__search-item:hover,
.site-header__search-item:hover .site-header__search-item-name,
.site-header__search-item:hover .site-header__search-item-price{
    color:#fff;
}

.site-header__search-item img{
    width:34px;
    height:34px;
    object-fit:cover;
    border-radius:6px;
    flex-shrink:0;
}

.site-header__search-item-name{
    flex:1;
    font-size:.8rem;
}

.site-header__search-item-price{
    font-size:.75rem;
    opacity:.7;
    white-space:nowrap;
}

.site-header__search-empty{
    padding:.9rem;
    font-size:.8rem;
    opacity:.6;
    text-align:center;
}

/* CART BADGE */

.site-header__cart-link{
    position:relative;
}

.site-header__cart-badge{
    position:absolute;
    top:-8px;
    right:-14px;
    background:var(--accent);
    color:#fff;
    font-size:.65rem;
    font-weight:700;
    min-width:18px;
    height:18px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0 4px;
}

/* CART DRAWER */

.cart-drawer{
    position:fixed;
    inset:0;
    z-index:2000;
    pointer-events:none;
}

.cart-drawer.is-open{
    pointer-events:auto;
}

.cart-drawer__overlay{
    position:absolute;
    inset:0;
    background:rgba(0,0,0,.5);
    opacity:0;
    transition:.3s;
}

.cart-drawer.is-open .cart-drawer__overlay{
    opacity:1;
}

.cart-drawer__panel{
    position:absolute;
    top:0;
    right:0;
    height:100%;
    width:380px;
    max-width:90vw;
    background:var(--bg);
    border-left:1px solid var(--border);
    display:flex;
    flex-direction:column;
    transform:translateX(100%);
    transition:.3s ease;
}

.cart-drawer.is-open .cart-drawer__panel{
    transform:translateX(0);
}

.cart-drawer__head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:1.3rem 1.5rem;
    border-bottom:1px solid var(--border);
}

.cart-drawer__head h3{
    font-size:1rem;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.cart-drawer__close{
    background:none;
    border:none;
    color:var(--text);
    font-size:1.4rem;
    cursor:pointer;
    line-height:1;
}

.cart-drawer__items{
    flex:1;
    overflow-y:auto;
    padding:1rem 1.5rem;
}

.cart-drawer__item{
    display:flex;
    gap:.8rem;
    align-items:center;
    padding:.8rem 0;
    border-bottom:1px solid var(--border);
}

.cart-drawer__item img{
    width:54px;
    height:54px;
    object-fit:cover;
    border-radius:6px;
    flex-shrink:0;
}

.cart-drawer__item-info{
    flex:1;
}

.cart-drawer__item-info p{
    font-size:.75rem;
    opacity:.7;
}

.cart-drawer__item-name{
    font-size:.85rem;
    text-transform:uppercase;
}

.cart-drawer__item-subtotal{
    font-size:.8rem;
    font-weight:700;
    white-space:nowrap;
}

.cart-drawer__empty{
    text-align:center;
    opacity:.6;
    padding:2rem 0;
    font-size:.85rem;
}

.cart-drawer__foot{
    padding:1.3rem 1.5rem;
    border-top:1px solid var(--border);
}

.cart-drawer__total{
    display:flex;
    justify-content:space-between;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:1rem;
}

.cart-drawer__pay{
    display:block;
    text-align:center;
    background:var(--accent);
    color:#fff;
    text-decoration:none;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:.9rem;
    border-radius:6px;
    margin-bottom:.6rem;
}

.cart-drawer__later{
    display:block;
    width:100%;
    text-align:center;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    text-transform:uppercase;
    letter-spacing:.05em;
    font-size:.8rem;
    padding:.8rem;
    border-radius:6px;
    cursor:pointer;
}

/* THEME BUTTON */

.theme-toggle{
    width:44px;
    height:44px;
    border-radius:50%;
    border:1px solid var(--border);
    background:transparent;
    cursor:pointer;
    display:flex;
    justify-content:center;
    align-items:center;
    transition:.3s;
}

.theme-toggle:hover{
    background:var(--accent);
    border-color:var(--accent);
}

.theme-toggle__dot{
    width:18px;
    height:18px;
    border-radius:50%;
    background:var(--text);
    transition:.3s;
}

.theme-toggle:hover .theme-toggle__dot{
    transform:scale(.8);
}

/* RESPONSIVE */

@media(max-width:900px){
.site-header{
    grid-template-columns:auto auto;
    gap:1rem;
    padding:1rem 1.5rem;
}

.site-header__nav{
    display:none;
}

.site-header__logo{
    justify-self:start;
}

.site-header__actions{
    justify-self:end;
    gap:1rem;
}

.site-header__actions a{
    font-size:.8rem;
}
}

@media(max-width:600px){
.site-header__actions a,
.site-header__search-wrapper{
    display:none;
}
}
</style>