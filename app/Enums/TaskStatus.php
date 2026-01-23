<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';  // Pending approval (after assignee marks as done)
    case DONE = 'done';      // Final state (after manager/admin approves)

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'In Review',
            self::DONE => 'Done',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => 'todo',
            self::IN_PROGRESS => 'inprogress',
            self::REVIEW => 'review',
            self::DONE => 'done',
        };
    }

    /**
     * Hex color for Calendar events
     */
    public function hexColor(): string
    {
        return match ($this) {
            self::TODO => '#94a3b8',      // Gray (Kanban To Do)
            self::IN_PROGRESS => '#3b82f6', // Blue (Kanban In Progress)
            self::REVIEW => '#f97316',     // Orange (Kanban Review)
            self::DONE => '#10b981',       // Teal Green (Kanban Done)
        };
    }

    /**
     * Gantt bar colors (main and progress)
     */
    public function ganttColors(): array
    {
        return match ($this) {
            self::TODO => ['bar' => '#cbd5e1', 'progress' => '#94a3b8'],         // Gray
            self::IN_PROGRESS => ['bar' => '#93c5fd', 'progress' => '#3b82f6'], // Blue
            self::REVIEW => ['bar' => '#fdba74', 'progress' => '#f97316'],      // Orange
            self::DONE => ['bar' => '#6ee7b7', 'progress' => '#10b981'],        // Teal Green
        };
    }

    /**
     * Check if task is considered complete
     */
    public function isCompleted(): bool
    {
        return $this === self::DONE;
    }
}
