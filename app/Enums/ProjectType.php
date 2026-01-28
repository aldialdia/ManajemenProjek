<?php

namespace App\Enums;

enum ProjectType: string
{
    case RBB = 'rbb';
    case NON_RBB = 'non_rbb';

    public function label(): string
    {
        return match ($this) {
            self::RBB => 'RBB',
            self::NON_RBB => 'Non-RBB',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RBB => 'primary',
            self::NON_RBB => 'secondary',
        };
    }

    /**
     * Hex color for visual representation
     */
    public function hexColor(): string
    {
        return match ($this) {
            self::RBB => '#6366f1',      // Indigo
            self::NON_RBB => '#64748b',  // Gray
        };
    }
}
