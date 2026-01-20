<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';
    case DONE_APPROVED = 'done_approved';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'Review',
            self::DONE => 'Done (Pending Approval)',
            self::DONE_APPROVED => 'Done (Approved)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::REVIEW => 'warning',
            self::DONE => 'info',
            self::DONE_APPROVED => 'success',
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
            self::DONE => '#06b6d4',
            self::DONE_APPROVED => '#10b981',
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
            self::DONE => ['bar' => '#67e8f9', 'progress' => '#06b6d4'],
            self::DONE_APPROVED => ['bar' => '#6ee7b7', 'progress' => '#10b981'],
        };
    }

    /**
     * Check if task is considered complete (done or approved)
     */
    public function isCompleted(): bool
    {
        return in_array($this, [self::DONE, self::DONE_APPROVED]);
    }
}
