<?php

namespace App\Enums;

enum OrderStatus : string 
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function getStatuses()
    {
        return [
            self::Unpaid,
            self::Paid,
            self::Cancelled,
            self::Shipped,
            self::Completed
        ];
    }
}