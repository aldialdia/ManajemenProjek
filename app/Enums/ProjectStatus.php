<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::IN_PROGRESS => 'Sedang Berjalan',
            self::DONE => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'secondary',
            self::IN_PROGRESS => 'primary',
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
            self::DONE => '#10b981',     // Green
        };
    }
}

