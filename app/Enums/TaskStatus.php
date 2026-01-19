<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'Review',
            self::DONE => 'Done',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::REVIEW => 'warning',
            self::DONE => 'success',
        };
    }

    /**
     * Hex color for Calendar events
     */
    public function hexColor(): string
    {
        return match ($this) {
            self::TODO => '#6b7280',
            self::IN_PROGRESS => '#6366f1',
            self::REVIEW => '#f59e0b',
            self::DONE => '#10b981',
        };
    }

    /**
     * Gantt bar colors (main and progress)
     */
    public function ganttColors(): array
    {
        return match ($this) {
            self::TODO => ['bar' => '#d1d5db', 'progress' => '#9ca3af'],
            self::IN_PROGRESS => ['bar' => '#a5b4fc', 'progress' => '#6366f1'],
            self::REVIEW => ['bar' => '#fcd34d', 'progress' => '#f59e0b'],
            self::DONE => ['bar' => '#6ee7b7', 'progress' => '#10b981'],
        };
    }
}
