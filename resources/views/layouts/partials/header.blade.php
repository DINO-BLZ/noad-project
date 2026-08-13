<header class="site-header">

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
        <a href="{{ route('cart.index') }}">Panier</a>

        <button id="theme-toggle" class="theme-toggle" aria-label="Changer de thème">
            <span class="theme-toggle__dot"></span>
        </button>
    </div>

</header>

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