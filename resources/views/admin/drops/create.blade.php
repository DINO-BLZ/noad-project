@extends('layouts.app')

@section('content')
<div class="admin-form-page">

    <h1>Créer un drop</h1>

    <form action="{{ route('admin.drops.store') }}" method="POST" enctype="multipart/form-data" class="admin-form" id="drop-form">
        @csrf

        <label>Nom du drop
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>

        <label>Description
            <textarea name="description" rows="3">{{ old('description') }}</textarea>
        </label>

        <label>Date de début
            <input type="datetime-local" name="start_date" id="start_date" value="{{ old('start_date') }}" required>
        </label>

        <label>Date de fin
            <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}" required>
        </label>

        <label>Statut
            <select name="status" required>
                <option value="upcoming">À venir</option>
                <option value="active">En cours</option>
                <option value="ended">Terminé</option>
            </select>
        </label>

        <label>Produits existants (optionnel)
            <div class="checkbox-list">
                @foreach($products as $product)
                    <label class="checkbox-item">
                        <input type="checkbox" name="products[]" value="{{ $product->id }}">
                        {{ $product->name }}
                    </label>
                @endforeach
            </div>
        </label>

        <label>Nouveaux produits pour ce drop</label>
        <div id="new-products-list"></div>
        <button type="button" id="add-product-row" class="admin-btn-secondary">+ Ajouter un produit</button>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Créer le drop</button>
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
        const row = document.createElement('div');
        row.className = 'new-product-row';
        row.innerHTML = `
            <button type="button" class="new-product-row__remove">Retirer</button>
            <label>Nom du produit
                <input type="text" name="new_products[${productIndex}][name]">
            </label>
            <label>Prix (DA)
                <input type="number" step="0.01" name="new_products[${productIndex}][price]">
            </label>
            <label>Photo
                <input type="file" name="new_products[${productIndex}][image]" accept="image/*">
            </label>
        `;
        row.querySelector('.new-product-row__remove').addEventListener('click', () => row.remove());
        list.appendChild(row);
        productIndex++;
    });
});
</script>
@endsection