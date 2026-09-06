<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && (bool) auth()->user()->is_admin;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $category = $this->route('category');
            $slug = Str::slug($this->input('name'));

            $slugTaken = Category::where('slug', $slug)
                ->where('id', '!=', $category?->id)
                ->exists();

            if ($slugTaken) {
                $validator->errors()->add(
                    'name',
                    'Ce nom donne un identifiant (slug) déjà utilisé par une autre catégorie.'
                );
            }
        });
    }
}
