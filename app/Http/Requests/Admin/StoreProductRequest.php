<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && (bool) auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'sizes' => [
                'required',
                'array',
                'min:1',
            ],

            'sizes.*' => [
                'required',
                'array',
            ],

            'sizes.*.stock' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Le nom du produit est obligatoire.',

            'name.min' =>
                'Le nom doit contenir au moins 2 caractères.',

            'category_id.required' =>
                'Veuillez sélectionner une catégorie.',

            'category_id.exists' =>
                'La catégorie sélectionnée est invalide.',

            'price.required' =>
                'Le prix est obligatoire.',

            'price.numeric' =>
                'Le prix doit être un nombre.',

            'price.min' =>
                'Le prix ne peut pas être négatif.',

            'image.required' =>
                'Une image est obligatoire.',

            'image.image' =>
                'Le fichier doit être une image.',

            'image.mimes' =>
                'L’image doit être au format JPG, JPEG, PNG ou WebP.',

            'image.max' =>
                'L’image ne doit pas dépasser 4 Mo.',

            'sizes.required' =>
                'Sélectionnez au moins une taille.',

            'sizes.min' =>
                'Sélectionnez au moins une taille.',

            'sizes.*.stock.required' =>
                'Le stock est obligatoire.',

            'sizes.*.stock.integer' =>
                'Le stock doit être un nombre entier.',

            'sizes.*.stock.min' =>
                'Le stock ne peut pas être négatif.',
        ];
    }
}