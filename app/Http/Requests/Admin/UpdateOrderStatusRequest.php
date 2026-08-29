<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && (bool) auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,paid,shipped,cancelled',
        ];
    }
}