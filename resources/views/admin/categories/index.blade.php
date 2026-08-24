@extends('layouts.admin')

@section('content')
<div class="admin-categories">

    <div class="admin-categories__header">
        <h1>Catégories</h1>
    </div>

    @if(session('success'))
        <p class="admin-notice">{{ session('success') }}</p>
    @endif

    @if($errors->has('category'))
        <p class="admin-notice admin-notice--error">{{ $errors->first('category') }}</p>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" class="admin-categories__add-form">
        @csrf
        <input type="text" name="name" placeholder="Nom de la nouvelle catégorie (ex: Bobs, Kimonos...)" value="{{ old('name') }}" required>
        <button type="submit" class="admin-btn">+ Ajouter</button>
    </form>

    @if($errors->has('name'))
        <p class="admin-notice admin-notice--error">{{ $errors->first('name') }}</p>
    @endif

    <div class="admin-categories__list">
        @forelse($categories as $category)
            <div class="admin-category-row">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="admin-category-row__edit-form">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $category->name }}" required>
                    <span class="admin-category-row__count">{{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }}</span>
                    <button type="submit">Enregistrer</button>
                </form>

                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-category-row__delete">Supprimer</button>
                </form>
            </div>
        @empty
            <p class="admin-panel__empty">Aucune catégorie pour le moment. Ajoute la première ci-dessus.</p>
        @endforelse
    </div>

</div>

<style>
.admin-categories{
    padding:3rem;
    max-width:700px;
    margin:0 auto;
}

.admin-categories__header{
    margin-bottom:2rem;
}

.admin-categories__header h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
}

.admin-notice{
    background:rgba(46,204,113,.1);
    border:1px solid #2ecc71;
    color:#2ecc71;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    font-size:.85rem;
}

.admin-notice--error{
    background:rgba(176,46,38,.1);
    border-color:var(--accent);
    color:var(--accent);
}

.admin-categories__add-form{
    display:flex;
    gap:.8rem;
    margin-bottom:2rem;
}

.admin-categories__add-form input{
    flex:1;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.8rem;
    font-family:inherit;
    font-size:.9rem;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    border:none;
    text-decoration:none;
    padding:.8rem 1.4rem;
    font-size:.85rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
    white-space:nowrap;
}

.admin-categories__list{
    display:flex;
    flex-direction:column;
    gap:.8rem;
}

.admin-category-row{
    display:flex;
    align-items:center;
    gap:.8rem;
    padding:.8rem 0;
    border-bottom:1px solid var(--border);
}

.admin-category-row__edit-form{
    display:flex;
    align-items:center;
    gap:.8rem;
    flex:1;
}

.admin-category-row__edit-form input{
    flex:1;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.6rem .8rem;
    font-family:inherit;
    font-size:.85rem;
}

.admin-category-row__count{
    font-size:.75rem;
    opacity:.6;
    white-space:nowrap;
}

.admin-category-row__edit-form button,
.admin-category-row__delete{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    font-size:.75rem;
    text-transform:uppercase;
    padding:.5rem 1rem;
    cursor:pointer;
    white-space:nowrap;
}

.admin-category-row__edit-form button:hover{
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}

.admin-category-row__delete:hover{
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
