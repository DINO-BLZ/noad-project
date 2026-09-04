<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use LogicException;

class CancelOrderAction
{
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $currentStatus = $lockedOrder->status;

            if (! in_array($currentStatus, [OrderStatus::Pending, OrderStatus::Paid], true)) {
                throw new LogicException(sprintf(
                    'Annulation de commande interdite depuis le statut %s.',
                    $currentStatus instanceof OrderStatus ? $currentStatus->value : (string) $currentStatus
                ));
            }

            $items = $lockedOrder->items()->get();
            $variantIds = $items->pluck('variant_id')->filter()->unique()->sort()->values();
            $variants = Variant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                if ($item->variant_id && $variants->has($item->variant_id)) {
                    $variants[$item->variant_id]->increment('stock', $item->quantity);
                }
            }

            $attributes = ['status' => OrderStatus::Cancelled];

            // Si l'argent avait déjà été encaissé (ex: évolution future
            // permettant d'annuler après livraison), il doit être marqué
            // comme remboursé plutôt que de rester "payé".
            if ($lockedOrder->payment_status === PaymentStatus::Paid) {
                $attributes['payment_status'] = PaymentStatus::Refunded;
            }

            $lockedOrder->update($attributes);

            return $lockedOrder->refresh();
        });
    }
}