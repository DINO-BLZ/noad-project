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

        <label>Tailles disponibles et stock</label>
        <div id="sizes-list">
            @foreach($product->variants as $index => $variant)
                <div class="size-row">
                    <label>Taille
                        <input type="text" name="sizes[{{ $index }}][size]" value="{{ $variant->size }}" required>
                    </label>
                    <label>Stock
                        <input type="number" name="sizes[{{ $index }}][stock]" value="{{ $variant->stock }}" min="0" required>
                    </label>
                    <button type="button" class="size-row__remove">Retirer</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-size-row" class="admin-btn-secondary">+ Ajouter une taille</button>

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

.size-row{
    display:grid;
    grid-template-columns:1fr 1fr auto;
    gap:.8rem;
    align-items:end;
    border:1px solid var(--border);
    padding:1rem;
    margin-bottom:.8rem;
}

.size-row label{
    margin:0;
}

.size-row__remove{
    background:transparent;
    border:1px solid var(--accent);
    color:var(--accent);
    font-size:.7rem;
    text-transform:uppercase;
    padding:.6rem;
    cursor:pointer;
    height:fit-content;
}

.admin-btn-secondary{
    background:transparent;
    border:1px dashed var(--border);
    color:var(--text);
    padding:.8rem;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    cursor:pointer;
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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let sizeIndex = {{ $product->variants->count() }};
    const list = document.getElementById('sizes-list');

    document.querySelectorAll('.size-row__remove').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.size-row').remove());
    });

    document.getElementById('add-size-row').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'size-row';
        row.innerHTML = `
            <label>Taille
                <input type="text" name="sizes[${sizeIndex}][size]" placeholder="S, M, L, 42..." required>
            </label>
            <label>Stock
                <input type="number" name="sizes[${sizeIndex}][stock]" min="0" value="1" required>
            </label>
            <button type="button" class="size-row__remove">Retirer</button>
        `;
        row.querySelector('.size-row__remove').addEventListener('click', () => row.remove());
        list.appendChild(row);
        sizeIndex++;
    });
});
</script>
@endsection