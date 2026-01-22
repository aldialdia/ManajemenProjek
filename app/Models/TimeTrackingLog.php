<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeTrackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'time_entry_id',
        'action',
        'duration_at_action',
        'note',
    ];

    /**
     * Get the task this log belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the time entry associated with this log.
     */
    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class);
    }

    /**
     * Get formatted duration at action.
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration_at_action;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dj %dm %ds', $hours, $minutes, $secs);
    }

    /**
     * Get action label in Indonesian.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'started' => 'Memulai pengerjaan',
            'paused' => 'Menjeda pengerjaan',
            'resumed' => 'Melanjutkan pengerjaan',
            'stopped' => 'Menghentikan pengerjaan',
            'completed' => 'Menyelesaikan tugas',
            default => $this->action,
        };
    }

    /**
     * Get action icon class.
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'started' => 'fa-play',
            'paused' => 'fa-pause',
            'resumed' => 'fa-play-circle',
            'stopped' => 'fa-stop',
            'completed' => 'fa-check-circle',
            default => 'fa-clock',
        };
    }

    /**
     * Get action color class.
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'started' => 'success',
            'paused' => 'warning',
            'resumed' => 'info',
            'stopped' => 'secondary',
            'completed' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Scope for filtering by task.
     */
    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
