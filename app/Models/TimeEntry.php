<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'description',
        'is_running',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_running' => 'boolean',
        ];
    }

    /**
     * Get the user who created this time entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task this time entry belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get formatted duration in hours, minutes, seconds.
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dj %dm %ds', $hours, $minutes, $secs);
    }

    /**
     * Get formatted duration in decimal hours.
     */
    public function getDecimalHoursAttribute(): float
    {
        return round($this->duration_seconds / 3600, 2);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by task.
     */
    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for filtering by project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->whereHas('task', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    /**
     * Scope for filtering running entries.
     */
    public function scopeRunning($query)
    {
        return $query->where('is_running', true);
    }

    /**
     * Scope for filtering completed entries.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_running', false);
    }

    /**
     * Scope for filtering today's entries.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    /**
     * Scope for filtering this week's entries.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Calculate and update duration when stopping timer.
     */
    public function stop(): void
    {
        $this->ended_at = now();
        $this->is_running = false;
        $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);
        $this->save();
    }
}
