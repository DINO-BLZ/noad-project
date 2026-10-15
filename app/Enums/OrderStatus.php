<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Libellé lisible en français, utilisé dans les emails et les vues.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'En attente',
            self::Paid      => 'Payée',
            self::Shipped   => 'Expédiée',
            self::Delivered => 'Livrée',
            self::Cancelled => 'Annulée',
        };
    }

    /**
     * Transitions autorisées entre statuts.
     *
     * Cycle COD :
     *   Pending → Paid (confirmation paiement) ou Cancelled (abandon)
     *   Paid    → Shipped (expédition) ou Cancelled (avant expédition)
     *   Shipped → Delivered (livraison réussie) ou Cancelled (colis refusé/retour)
     *
     * Note : CancelOrderAction restitue le stock lorsqu'on annule
     * depuis Pending ou Paid. L'annulation depuis Shipped (colis refusé)
     * est autorisée ici mais CancelOrderAction doit également la gérer
     * (voir CancelOrderAction : statuts acceptés = [Pending, Paid, Shipped]).
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending   => in_array($newStatus, [self::Paid, self::Cancelled], true),
            self::Paid      => in_array($newStatus, [self::Shipped, self::Cancelled], true),
            self::Shipped   => in_array($newStatus, [self::Delivered, self::Cancelled], true),
            self::Delivered, self::Cancelled => false,
        };
    }
}
