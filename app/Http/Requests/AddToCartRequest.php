<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'intent' => ['nullable', 'in:cart,buy_now'],
        ];
    }

    public function quantity(): int
    {
        return (int) ($this->validated()['quantity'] ?? 1);
    }

    public function intent(): string
    {
        return $this->validated()['intent'] ?? 'cart';
    }
}