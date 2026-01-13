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
}
