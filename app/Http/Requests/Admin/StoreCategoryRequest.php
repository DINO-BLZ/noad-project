<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && (bool) auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $slug = Str::slug($this->input('name'));

            if (Category::where('slug', $slug)->exists()) {
                $validator->errors()->add(
                    'name',
                    'Ce nom donne un identifiant (slug) déjà utilisé par une autre catégorie.'
                );
            }
        });
    }
}
