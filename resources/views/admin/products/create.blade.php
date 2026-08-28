@extends('layouts.admin')

@section('content')

<div class="admin-form-page">

    <div class="admin-form-header">
        <div>
            <span class="admin-form-eyebrow">Administration</span>
            <h1>Créer un produit</h1>
            <p>
                Ajoutez un produit, ses informations et ses variantes.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="admin-form__errors">
            <strong>
                {{ $errors->count() }}
                erreur(s) à corriger
            </strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="admin-form"
    >

        @csrf

        {{-- Informations générales --}}
        <section class="form-section">

            <div class="form-section__header">
                <h2>Informations générales</h2>
                <span>01</span>
            </div>

            <div class="form-grid">

                <label class="form-field">
                    <span>Nom du produit</span>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Ex : Nike Air Max"
                        required
                    >
                </label>

                <label class="form-field">
                    <span>Catégorie</span>

                    <select
                        name="category_id"
                        id="category_id"
                        required
                    >
                        <option value="">
                            Sélectionner une catégorie
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                data-type="{{ $category->type ?? 'clothing' }}"
                                @selected(
                                    old('category_id') == $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

            </div>

            <label class="form-field">
                <span>Prix (DA)</span>

                <input
                    type="number"
                    name="price"
                    value="{{ old('price') }}"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    required
                >
            </label>

            <label class="form-field">
                <span>Description</span>

                <textarea
                    name="description"
                    rows="5"
                    placeholder="Description du produit..."
                >{{ old('description') }}</textarea>
            </label>

        </section>

        {{-- Image --}}
        <section class="form-section">

            <div class="form-section__header">
                <h2>Image principale</h2>
                <span>02</span>
            </div>

            <label class="upload-zone">

                <input
                    type="file"
                    name="image"
                    id="image"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >

                <div>
                    <strong>Choisir une image</strong>
                    <small>
                        JPG, PNG ou WebP — maximum 5 Mo
                    </small>
                </div>

            </label>

            <div id="image-preview"></div>

        </section>

        {{-- Variantes --}}
        <section class="form-section">

            <div class="form-section__header">
                <div>
                    <h2>Variantes & stock</h2>
                    <p>
                        Sélectionnez les tailles disponibles.
                    </p>
                </div>

                <span>03</span>
            </div>

            <div
                id="sizes-grid"
                class="sizes-grid"
            ></div>

        </section>

        <div class="form-actions">

            <a
                href="{{ route('admin.products.index') }}"
                class="admin-btn admin-btn--secondary"
            >
                Annuler
            </a>

            <button
                type="submit"
                class="admin-btn"
            >
                Créer le produit
            </button>

        </div>

    </form>

</div>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    const categorySelect =
        document.getElementById('category_id');

    const grid =
        document.getElementById('sizes-grid');

    const imageInput =
        document.getElementById('image');

    const imagePreview =
        document.getElementById('image-preview');

   const oldSizes = {{ Js::from(old('sizes', [])) }};

    const sizeSets = {
        clothing: [
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL'
        ],

        shoes: [
            '36',
            '37',
            '38',
            '39',
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46'
        ],

        accessories: []
    };

    function renderSizes() {

        const option =
            categorySelect.options[
                categorySelect.selectedIndex
            ];

        const type =
            option?.dataset.type ?? 'clothing';

        const sizes =
            sizeSets[type] ?? sizeSets.clothing;

        grid.innerHTML = '';

        if (!sizes.length) {

            grid.innerHTML = `
                <div class="sizes-empty">
                    Ce produit ne possède pas de tailles.
                </div>
            `;

            return;
        }

        sizes.forEach(size => {

            const oldVariant =
                oldSizes[size] ?? null;

            const checked =
                oldVariant !== null;

            const stock =
                oldVariant?.stock ?? '';

            const wrapper =
                document.createElement('div');

            wrapper.className = 'size-toggle';

            wrapper.innerHTML = `
                <label class="size-toggle__checkbox">

                    <input
                        type="checkbox"
                        class="size-checkbox"
                        data-size="${size}"
                        ${checked ? 'checked' : ''}
                    >

                    <span>${size}</span>

                </label>

                <input
                    type="number"
                    name="sizes[${size}][stock]"
                    class="size-stock-input"
                    data-size="${size}"
                    min="0"
                    max="1000000"
                    value="${stock}"
                    placeholder="Stock"
                    ${checked ? '' : 'disabled'}
                >
            `;

            grid.appendChild(wrapper);
        });

        bindCheckboxes();
    }

    function bindCheckboxes() {

        document
            .querySelectorAll('.size-checkbox')
            .forEach(checkbox => {

                checkbox.addEventListener(
                    'change',
                    () => {

                        const size =
                            checkbox.dataset.size;

                        const input =
                            document.querySelector(
                                `.size-stock-input[data-size="${size}"]`
                            );

                        input.disabled =
                            !checkbox.checked;

                        if (checkbox.checked) {

                            if (!input.value) {
                                input.value = 1;
                            }

                        } else {

                            input.value = '';
                        }
                    }
                );
            });
    }

    categorySelect.addEventListener(
        'change',
        renderSizes
    );

    imageInput.addEventListener(
        'change',
        () => {

            const file =
                imageInput.files[0];

            imagePreview.innerHTML = '';

            if (!file) {
                return;
            }

            const image =
                document.createElement('img');

            image.src =
                URL.createObjectURL(file);

            image.alt =
                'Aperçu du produit';

            image.className =
                'image-preview';

            imagePreview.appendChild(image);
        }
    );

    document
        .querySelector('.admin-form')
        .addEventListener('submit', () => {

            document
                .querySelectorAll(
                    '.size-stock-input:disabled'
                )
                .forEach(input => {

                    input.removeAttribute('name');

                });
        });

    renderSizes();

});
</script>

@endpush