<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case ON_HOLD = 'on_hold';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::IN_PROGRESS => 'Sedang Berjalan',
            self::ON_HOLD => 'Ditunda',
            self::DONE => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::ON_HOLD => 'warning',
            self::DONE => 'success',
        };
    }

    /**
     * Hex color for visual representation
     */
    public function hexColor(): string
    {
        return match ($this) {
            self::NEW => '#94a3b8',      // Gray
            self::IN_PROGRESS => '#3b82f6', // Blue
            self::ON_HOLD => '#f59e0b',    // Orange/Amber
            self::DONE => '#10b981',     // Green
        };
    }
}


