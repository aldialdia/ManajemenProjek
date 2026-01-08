<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ON_HOLD => 'On Hold',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ON_HOLD => 'warning',
            self::COMPLETED => 'info',
            self::CANCELLED => 'danger',
        };
    }
}
