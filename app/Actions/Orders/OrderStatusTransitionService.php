<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use LogicException;

class OrderStatusTransitionService
{
    public function transition(Order $order, OrderStatus $newStatus): Order
    {
        if ($newStatus === OrderStatus::Cancelled) {
            throw new LogicException('Les annulations doivent passer par CancelOrderAction.');
        }

        return DB::transaction(function () use ($order, $newStatus) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $currentStatus = $lockedOrder->status;

            if (! $currentStatus instanceof OrderStatus || ! $currentStatus->canTransitionTo($newStatus)) {
                throw new LogicException(sprintf(
                    'Transition de commande interdite : %s -> %s.',
                    $currentStatus instanceof OrderStatus ? $currentStatus->value : (string) $currentStatus,
                    $newStatus->value
                ));
            }

            $attributes = ['status' => $newStatus];

            // Paiement à la livraison (COD) : l'argent n'est encaissé
            // qu'au moment où le livreur remet la commande.
            if ($newStatus === OrderStatus::Delivered) {
                $attributes['payment_status'] = PaymentStatus::Paid;
            }

            $lockedOrder->update($attributes);

            return $lockedOrder->refresh();
        });
    }
}
