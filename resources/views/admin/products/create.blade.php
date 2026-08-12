@extends('layouts.app')

@section('content')
<div class="admin-form-page">

    <h1>Créer un produit</h1>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf

        <label>Nom du produit
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>

        <label>Catégorie
            <select name="category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>Prix (DA)
            <input type="number" name="price" step="0.01" value="{{ old('price') }}" required>
        </label>

        <label>Description
            <textarea name="description" rows="4">{{ old('description') }}</textarea>
        </label>

        <label>Photo principale (optionnel)
            <input type="file" name="image" accept="image/*">
        </label>

        <label>Variantes (taille, couleur, SKU, stock)</label>
        <div id="variants-list"></div>
        <button type="button" id="add-variant" class="admin-btn-secondary">+ Ajouter une variante</button>

        <label>Images supplémentaires
            <input type="file" name="images[]" accept="image/*" multiple>
        </label>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Créer</button>
    </form>

</div>

<style>
/* reuse existing styles from edit form */
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let idx = 0;
    const list = document.getElementById('variants-list');
    document.getElementById('add-variant').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'size-row';
        row.innerHTML = `
            <label>Taille
                <input type="text" name="variants[${idx}][size]" placeholder="S, M, L, 42..." required>
            </label>
            <label>Couleur
                <input type="text" name="variants[${idx}][color]" placeholder="Noir, Blanc...">
            </label>
            <label>SKU
                <input type="text" name="variants[${idx}][sku]" placeholder="SKU123">
            </label>
            <label>Stock
                <input type="number" name="variants[${idx}][stock]" min="0" value="1" required>
            </label>
            <button type="button" class="size-row__remove">Retirer</button>
        `;
        row.querySelector('.size-row__remove').addEventListener('click', () => row.remove());
        list.appendChild(row);
        idx++;
    });
});
</script>
@endsection
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

.checkbox-list{
    display:flex;
    flex-direction:column;
    gap:.5rem;
    border:1px solid var(--border);
    padding:1rem;
    max-height:200px;
    overflow-y:auto;
}

.checkbox-item{
    flex-direction:row !important;
    align-items:center;
    gap:.6rem !important;
    text-transform:none !important;
    font-size:.85rem !important;
    opacity:1 !important;
}

.new-product-row{
    border:1px solid var(--border);
    padding:1rem;
    display:flex;
    flex-direction:column;
    gap:.8rem;
    margin-bottom:1rem;
    position:relative;
}

.new-product-row__remove{
    align-self:flex-end;
    background:transparent;
    border:1px solid var(--accent);
    color:var(--accent);
    font-size:.7rem;
    text-transform:uppercase;
    padding:.3rem .7rem;
    cursor:pointer;
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
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');

    function syncMinEndDate() {
        if (startInput.value) {
            endInput.min = startInput.value;
            if (endInput.value && endInput.value <= startInput.value) {
                endInput.value = '';
            }
        }
    }

    startInput.addEventListener('change', syncMinEndDate);
    syncMinEndDate();

    document.getElementById('drop-form').addEventListener('submit', (e) => {
        if (startInput.value && endInput.value && endInput.value <= startInput.value) {
            e.preventDefault();
            alert('La date de fin doit être après la date de début.');
        }
    });

    let productIndex = 0;
    const list = document.getElementById('new-products-list');

    document.getElementById('add-product-row').addEventListener('click', () => {
        const currentIndex = productIndex;
        const row = document.createElement('div');
        row.className = 'new-product-row';
        row.innerHTML = `
            <button type="button" class="new-product-row__remove">Retirer le produit</button>
            <label>Nom du produit
                <input type="text" name="new_products[${currentIndex}][name]">
            </label>
            <label>Prix (DA)
                <input type="number" step="0.01" name="new_products[${currentIndex}][price]">
            </label>
            <label>Photo
                <input type="file" name="new_products[${currentIndex}][image]" accept="image/*">
            </label>
            <label>Tailles disponibles et stock</label>
            <div class="sizes-list-inner"></div>
            <button type="button" class="add-size-row-inner admin-btn-secondary">+ Ajouter une taille</button>
        `;

        row.querySelector('.new-product-row__remove').addEventListener('click', () => row.remove());

        let sizeIndex = 0;
        const sizesList = row.querySelector('.sizes-list-inner');

        function addSizeRow() {
            const sizeRow = document.createElement('div');
            sizeRow.className = 'size-row';
            sizeRow.innerHTML = `
                <label>Taille
                    <input type="text" name="new_products[${currentIndex}][sizes][${sizeIndex}][size]" placeholder="S, M, L, 42...">
                </label>
                <label>Stock
                    <input type="number" name="new_products[${currentIndex}][sizes][${sizeIndex}][stock]" min="0" value="1">
                </label>
                <button type="button" class="size-row__remove">Retirer</button>
            `;
            sizeRow.querySelector('.size-row__remove').addEventListener('click', () => sizeRow.remove());
            sizesList.appendChild(sizeRow);
            sizeIndex++;
        }

        row.querySelector('.add-size-row-inner').addEventListener('click', addSizeRow);
        addSizeRow();

        list.appendChild(row);
        productIndex++;
    });
});
</script>
@endsection