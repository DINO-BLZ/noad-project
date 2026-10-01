<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && (bool) auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999999.99',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.size' => 'required_with:variants|string|max:50',
            'variants.*.stock' => 'required_with:variants|integer|min:0|max:1000000',
            'variants.*.sku' => 'nullable|string|max:50',
            'variants.*.color' => 'nullable|string|max:50',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'primary_image' => 'nullable|string',
        ];
    }
}
