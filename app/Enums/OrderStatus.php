<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Paid, self::Cancelled], true),
            self::Paid => in_array($newStatus, [self::Shipped, self::Cancelled], true),
            self::Shipped => $newStatus === self::Delivered,
            self::Delivered, self::Cancelled => false,
        };
    }
}