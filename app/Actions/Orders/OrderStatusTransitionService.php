<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
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

            $lockedOrder->update(['status' => $newStatus]);

            return $lockedOrder->refresh();
        });
    }
}
