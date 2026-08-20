<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,active,ended',
            'products' => 'array',
            'max_whitelist_slots' => 'nullable|integer|min:0',
            'products.*' => 'exists:products,id',
            'new_products' => 'array',
            'new_products.*.name' => 'nullable|string|max:255',
            'new_products.*.price' => 'nullable|numeric|min:0',
            'new_products.*.image' => 'nullable|image|max:4096',
            'new_products.*.category_id' => 'nullable|exists:categories,id',
            'new_products.*.sizes' => 'nullable|array',
            'new_products.*.sizes.*.size' => 'nullable|string|max:10',
            'new_products.*.sizes.*.stock' => 'nullable|integer|min:0',
            'new_products.*.sizes.*.sku' => 'nullable|string|max:50',
            'new_products.*.sizes.*.color' => 'nullable|string|max:50',
        ];
    }
}
