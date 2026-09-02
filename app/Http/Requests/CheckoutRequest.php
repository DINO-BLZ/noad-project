<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkout_token' => 'nullable|uuid',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'wilaya' => 'required|string|max:100',
            // Paiement par carte CIB/Edahabia temporairement désactivé :
            // l'intégration SATIM (certification + coûts) n'est pas encore
            // budgétée. La colonne DB et les vues gardent volontairement
            // le support de 'cib' pour éviter de tout refaire une fois
            // le paiement carte activé.
            'payment_method' => 'required|in:cod',
        ];
    }
}
