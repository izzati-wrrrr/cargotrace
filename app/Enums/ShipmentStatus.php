<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Created = 'CREATED';
    case Booked = 'BOOKED';
    case PickedUp = 'PICKED_UP';
    case InTransit = 'IN_TRANSIT';
    case Delivered = 'DELIVERED';
    case Cancelled = 'CANCELLED';

    /** Status berikutnya yang valid dari status ini. */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Created => [self::Booked, self::Cancelled],
            self::Booked => [self::PickedUp, self::Cancelled],
            self::PickedUp => [self::InTransit],
            self::InTransit => [self::Delivered],
            self::Delivered, self::Cancelled => [], // status terminal
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }

    /** Data shipment masih boleh diedit sebelum barang diambil kurir. */
    public function isEditable(): bool
    {
        return in_array($this, [self::Created, self::Booked], true);
    }
}