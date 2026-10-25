<?php

namespace App\Enums;

enum MovementType: string
{
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Reservation = 'reservation';
    case ReservationRelease = 'reservation_release';

    public function isPhysicalIn(): bool { return $this === self::StockIn; }
    public function isPhysicalOut(): bool { return $this === self::StockOut; }
}
