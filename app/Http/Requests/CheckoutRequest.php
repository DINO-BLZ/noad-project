<?php

namespace App\Http\Requests;

use App\Services\AlgeriaShippingService;
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
            'commune' => 'nullable|string|max:100',
            'delivery_type' => 'nullable|string|in:home,desk',
            // Paiement par carte CIB/Edahabia temporairement désactivé :
            // l'intégration SATIM (certification + coûts) n'est pas encore
            // budgétée. La colonne DB et les vues gardent volontairement
            // le support de 'cib' pour éviter de tout refaire une fois
            // le paiement carte activé.
            'payment_method' => 'required|in:cod',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $shippingService = new AlgeriaShippingService;
            $wilaya = $this->input('wilaya');

            if ($wilaya !== null && $wilaya !== '') {
                $found = $shippingService->findWilaya($wilaya);
                if (! $found) {
                    $validator->errors()->add('wilaya', 'La wilaya sélectionnée est inconnue.');

                    return;
                }

                $commune = $this->input('commune');
                if ($commune !== null && $commune !== '') {
                    $validCommunes = $shippingService->getCommunesForWilaya($wilaya);
                    if (! empty($validCommunes)) {
                        $match = collect($validCommunes)->first(
                            fn ($c) => mb_strtolower($c) === mb_strtolower($commune)
                        );
                        if (! $match) {
                            $validator->errors()->add('commune', 'La commune ne correspond pas à la wilaya sélectionnée.');
                        }
                    }
                }
            }
        });
    }
}
