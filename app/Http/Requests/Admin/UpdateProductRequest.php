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
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.size' => 'required_with:variants|string|max:50',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.sku' => 'nullable|string',
            'variants.*.color' => 'nullable|string|max:50',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'primary_image' => 'nullable|string',
        ];
    }
}
