@extends('layouts.app')

@section('content')
<div class="admin-products">

    <div class="admin-products__header">
        <h1>Produits</h1>
        <a href="{{ route('admin.products.create') }}" class="admin-btn">+ Ajouter un produit</a>
    </div>

    @if(session('success'))
        <p class="admin-notice">{{ session('success') }}</p>
    @endif

    <div class="admin-products__list">
        @forelse($products as $product)
            <div class="admin-product-row">
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                <div class="admin-product-row__info">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->category->name ?? '—' }} • {{ number_format($product->price, 0) }} DA</p>
                </div>
                <div class="admin-product-row__actions">
                    <a href="{{ route('admin.products.edit', $product) }}">Modifier</a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="admin-panel__empty">Aucun produit à afficher pour le moment.</p>
        @endforelse
    </div>

</div>

<style>
.admin-products{
    padding:3rem;
    max-width:1000px;
    margin:0 auto;
}

.admin-products__header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:2rem;
}

.admin-products__header h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    text-decoration:none;
    padding:.8rem 1.4rem;
    font-size:.85rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.admin-notice{
    background:rgba(46,204,113,.1);
    border:1px solid #2ecc71;
    color:#2ecc71;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.admin-product-row{
    display:grid;
    grid-template-columns:60px 1fr auto;
    align-items:center;
    gap:1.2rem;
    padding:1rem 0;
    border-bottom:1px solid var(--border);
}

.admin-product-row img{
    width:60px;
    height:60px;
    object-fit:cover;
    background:var(--border);
}

.admin-product-row__info h3{
    font-size:.9rem;
    text-transform:uppercase;
}

.admin-product-row__info p{
    font-size:.8rem;
    opacity:.7;
    margin-top:.2rem;
}

.admin-product-row__actions{
    display:flex;
    gap:.8rem;
}

.admin-product-row__actions a,
.admin-product-row__actions button{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    font-size:.75rem;
    text-transform:uppercase;
    padding:.5rem 1rem;
    cursor:pointer;
    text-decoration:none;
}

.admin-product-row__actions button:hover{
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}

.admin-panel__empty{
    opacity:.5;
    padding:2rem 0;
}
</style>
@endsection