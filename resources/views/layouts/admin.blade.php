<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — Noad</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__logo">
                <img src="/images/noad-logo.png" alt="Noad">
                <span>ADMIN</span>
            </div>

            <nav class="admin-sidebar__nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">Produits</a>
                <a href="{{ route('admin.drops.index') }}" class="{{ request()->routeIs('admin.drops.*') ? 'is-active' : '' }}">Drops</a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">Commandes</a>
                <a href="{{ route('admin.whitelist.index') }}" class="{{ request()->routeIs('admin.whitelist.*') ? 'is-active' : '' }}">Whitelist</a>
            </nav>
            <form action="{{ route('logout') }}" method="POST" class="admin-sidebar__logout">
                @csrf
                <button type="submit">Déconnexion</button>
            </form>
        </aside>

        <main class="admin-main">
            @yield('content')
        </main>
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
    }

    .admin-layout{
        display:grid;
        grid-template-columns:220px 1fr;
        min-height:100vh;
    }

    .admin-sidebar{
        background:#050505;
        border-right:1px solid var(--border);
        display:flex;
        flex-direction:column;
        padding:1.5rem 0;
    }

    .admin-sidebar__logo{
        display:flex;
        align-items:center;
        gap:.6rem;
        padding:0 1.5rem 1.5rem;
        border-bottom:1px solid var(--border);
        margin-bottom:1.5rem;
    }

    .admin-sidebar__logo img{
        height:24px;
    }

    .admin-sidebar__logo span{
        font-size:.7rem;
        font-weight:700;
        letter-spacing:.1em;
        opacity:.6;
    }

    .admin-sidebar__nav{
        display:flex;
        flex-direction:column;
        gap:.3rem;
        flex:1;
        padding:0 .8rem;
    }

    .admin-sidebar__nav a{
        color:var(--text);
        text-decoration:none;
        font-size:.85rem;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:.04em;
        padding:.7rem 1rem;
        border-radius:6px;
        opacity:.6;
        transition:.2s;
    }

    .admin-sidebar__nav a:hover{
        opacity:1;
        background:rgba(245,245,245,.05);
    }

    .admin-sidebar__nav a.is-active{
        opacity:1;
        background:var(--accent);
        color:#fff;
    }

    .admin-sidebar__logout{
        padding:0 1.5rem;
    }

    .admin-sidebar__logout button{
        width:100%;
        background:transparent;
        border:1px solid var(--border);
        color:var(--text);
        padding:.7rem;
        font-size:.8rem;
        text-transform:uppercase;
        letter-spacing:.05em;
        border-radius:6px;
        cursor:pointer;
        opacity:.7;
        transition:.2s;
    }

    .admin-sidebar__logout button:hover{
        opacity:1;
        border-color:var(--accent);
        color:var(--accent);
    }

    .admin-main{
        overflow-x:auto;
    }

    @media(max-width:768px){
        .admin-layout{
            grid-template-columns:1fr;
        }

        .admin-sidebar{
            flex-direction:row;
            align-items:center;
            padding:1rem;
            overflow-x:auto;
        }

        .admin-sidebar__logo{
            border-bottom:none;
            border-right:1px solid var(--border);
            padding:0 1rem 0 0;
            margin-bottom:0;
        }

        .admin-sidebar__nav{
            flex-direction:row;
            padding:0 1rem;
        }

        .admin-sidebar__logout{
            padding:0;
        }
    }
    </style>
</body>
</html>