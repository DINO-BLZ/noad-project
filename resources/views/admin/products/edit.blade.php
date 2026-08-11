@extends('layouts.app')

@section('content')
<div class="admin-form-page">

    <h1>Modifier le produit</h1>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf
        @method('PUT')

        <label>Nom du produit
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
        </label>

        <label>Catégorie
            <select name="category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>Prix (DA)
            <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" required>
        </label>

        <label>Description
            <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>
        </label>

        <label>Photo actuelle</label>
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:120px;">

        <label>Remplacer la photo (optionnel)
            <input type="file" name="image" accept="image/*">
        </label>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Mettre à jour</button>
    </form>

</div>

<style>
.admin-form-page{
    padding:3rem;
    max-width:600px;
    margin:0 auto;
}

.admin-form-page h1{
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:2rem;
}

.admin-form{
    display:flex;
    flex-direction:column;
    gap:1.2rem;
}

.admin-form label{
    display:flex;
    flex-direction:column;
    gap:.4rem;
    font-size:.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.05em;
    opacity:.8;
}

.admin-form input,
.admin-form select,
.admin-form textarea{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
    padding:.8rem;
    font-family:inherit;
    font-size:.9rem;
}

.admin-form select option{
    background:var(--bg);
    color:var(--text);
}

.admin-form__errors{
    background:rgba(176,46,38,.1);
    border:1px solid var(--accent);
    color:var(--accent);
    padding:.8rem 1rem;
    font-size:.8rem;
}

.admin-btn{
    background:var(--accent);
    color:#fff;
    border:none;
    padding:1rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
    text-align:center;
    text-decoration:none;
}
</style>
@endsection