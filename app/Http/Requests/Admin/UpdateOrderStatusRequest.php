<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(OrderStatus::class),

                function ($attribute, $value, $fail) {
                    $order = $this->route('order');

                    if (! $order instanceof Order) {
                        $fail('La commande est introuvable.');

                        return;
                    }

                    $currentStatus = $order->status;

                    if (! $currentStatus instanceof OrderStatus) {
                        $fail('Le statut actuel de la commande est invalide.');

                        return;
                    }

                    $newStatus = OrderStatus::from($value);

                    if (! $currentStatus->canTransitionTo($newStatus)) {
                        $fail(
                            'La commande ne peut pas passer du statut '
                            .$currentStatus->value
                            .' au statut '
                            .$newStatus->value
                            .'.'
                        );
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Veuillez sélectionner un statut.',
            'status.enum' => 'Le statut sélectionné est invalide.',
        ];
    }
}
