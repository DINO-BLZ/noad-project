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
        <a href="#">Recherche</a>
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
.site-header__actions a{
    display:none;
}
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const html = document.documentElement;

    const savedTheme = localStorage.getItem("noad-theme");

    if(savedTheme){
        html.setAttribute("data-theme", savedTheme);
    }

    const toggle = document.getElementById("theme-toggle");

    toggle.addEventListener("click", () => {

        const current = html.getAttribute("data-theme") === "light"
            ? "light"
            : "dark";

        const next = current === "light"
            ? "dark"
            : "light";

        html.setAttribute("data-theme", next);

        localStorage.setItem("noad-theme", next);

    });

});
</script>