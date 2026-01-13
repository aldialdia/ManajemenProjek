<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NEW = 'new';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::ACTIVE => 'Sedang Berjalan',
            self::ON_HOLD => 'Ditunda',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'primary',
            self::ACTIVE => 'success',
            self::ON_HOLD => 'warning',
            self::COMPLETED => 'info',
            self::CANCELLED => 'danger',
        };
    }
}

