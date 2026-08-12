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
            <select name="category_id" id="category_id" required>
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

        <label>Photo du produit
            <input type="file" name="image" accept="image/*" required>
        </label>

        <label>Tailles / Pointures disponibles et stock</label>
        <div class="sizes-grid" id="sizes-grid"></div>

        @if($errors->any())
            <div class="admin-form__errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="submit" class="admin-btn">Enregistrer le produit</button>
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
    width:100%;
    box-sizing:border-box;
}

.admin-form select option{
    background:var(--bg);
    color:var(--text);
}

.sizes-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));
    gap:.8rem;
    border:1px solid var(--border);
    padding:1rem;
}

.size-toggle{
    display:flex;
    flex-direction:column;
    gap:.5rem;
    border:1px solid var(--border);
    padding:.8rem;
}

.size-toggle__checkbox{
    flex-direction:row !important;
    align-items:center;
    gap:.5rem !important;
    text-transform:none !important;
    font-size:.9rem !important;
    font-weight:700 !important;
    opacity:1 !important;
    cursor:pointer;
}

.size-toggle__checkbox input[type="checkbox"]{
    width:auto;
}

.size-stock-input{
    padding:.5rem !important;
    font-size:.85rem !important;
}

.size-stock-input:disabled{
    opacity:.3;
    cursor:not-allowed;
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
    const clothingSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    const shoeSizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46'];
    const shoeCategoryNames = ['chaussure', 'running'];

    const categorySelect = document.getElementById('category_id');
    const grid = document.getElementById('sizes-grid');

    function renderSizes() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const categoryName = (selectedOption?.textContent || '').trim().toLowerCase();
        const isShoe = shoeCategoryNames.some(name => categoryName.includes(name));
        const sizes = isShoe ? shoeSizes : clothingSizes;

        grid.innerHTML = '';

        sizes.forEach(size => {
            const div = document.createElement('div');
            div.className = 'size-toggle';
            div.innerHTML = `
                <label class="size-toggle__checkbox">
                    <input type="checkbox" class="size-checkbox" data-size="${size}">
                    ${size}
                </label>
                <input type="number"
                       name="sizes[${size}][stock]"
                       class="size-stock-input"
                       data-size="${size}"
                       min="0"
                       value="1"
                       placeholder="Stock"
                       disabled>
            `;
            grid.appendChild(div);
        });

        attachCheckboxListeners();
    }

    function attachCheckboxListeners() {
        document.querySelectorAll('.size-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const size = checkbox.dataset.size;
                const stockInput = document.querySelector(`.size-stock-input[data-size="${size}"]`);
                stockInput.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    stockInput.value = '';
                } else if (!stockInput.value) {
                    stockInput.value = 1;
                }
            });
        });
    }

    categorySelect.addEventListener('change', renderSizes);
    renderSizes();

    document.querySelector('.admin-form').addEventListener('submit', () => {
        document.querySelectorAll('.size-stock-input:disabled').forEach(input => {
            input.removeAttribute('name');
        });
    });
});
</script>
@endsection