<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case USER = 'user';

    /**
     * Get all possible values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::USER => 'User',
        };
    }

    /**
     * Get color for badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => '#dc2626', // red-600
            self::USER => '#6b7280', // gray-500
        };
    }
}
