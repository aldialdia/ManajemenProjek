<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TaskStatus;

class TaskStatusLog extends Model
{
    protected $fillable = [
        'task_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
    ];

    protected $casts = [
        'from_status' => TaskStatus::class,
        'to_status' => TaskStatus::class,
    ];

    /**
     * Get the task that owns this log.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who made this change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted status change description.
     */
    public function getChangeDescriptionAttribute(): string
    {
        $from = $this->from_status?->label() ?? 'New';
        $to = $this->to_status->label();
        return "{$from} → {$to}";
    }
}
