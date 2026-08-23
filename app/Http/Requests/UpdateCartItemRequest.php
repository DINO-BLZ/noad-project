<?php

namespace App\Http\Requests;

use App\Models\Variant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = Variant::findOrFail($this->route('variantId'));

        return [
            'quantity' => 'required|integer|min:1|max:' . $variant->stock,
        ];
    }
}
