<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_paused',
        'paused_duration_seconds',
        'paused_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'paused_at' => 'datetime',
            'is_running' => 'boolean',
            'is_paused' => 'boolean',
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
     * Get logs for this time entry.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TimeTrackingLog::class);
    }

    /**
     * Get current elapsed seconds (including paused time).
     */
    public function getCurrentElapsedSecondsAttribute(): int
    {
        if ($this->is_paused) {
            // Jika paused, hitung dari started_at sampai paused_at minus waktu pause sebelumnya
            return $this->started_at->diffInSeconds($this->paused_at) - $this->paused_duration_seconds;
        }

        if ($this->is_running) {
            // Jika running, hitung dari started_at sampai sekarang minus waktu pause
            return $this->started_at->diffInSeconds(now()) - $this->paused_duration_seconds;
        }

        // Jika stopped, return durasi final
        return $this->duration_seconds;
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
     * Scope for filtering paused entries.
     */
    public function scopePaused($query)
    {
        return $query->where('is_paused', true);
    }

    /**
     * Scope for filtering active entries (running).
     */
    public function scopeActive($query)
    {
        return $query->where('is_running', true);
    }

    /**
     * Scope for filtering completed entries.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_running', false)->whereNotNull('ended_at');
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
     * Pause the timer.
     */
    public function pause(): void
    {
        if (!$this->is_running || $this->is_paused) {
            return;
        }

        $this->is_paused = true;
        $this->is_running = false;
        $this->paused_at = now();
        $this->save();

        // Create log
        TimeTrackingLog::create([
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'time_entry_id' => $this->id,
            'action' => 'paused',
            'duration_at_action' => $this->current_elapsed_seconds,
        ]);
    }

    /**
     * Resume a paused timer.
     */
    public function resume(): void
    {
        if (!$this->is_paused) {
            return;
        }

        // Hitung durasi pause
        $pauseDuration = $this->paused_at->diffInSeconds(now());
        $this->paused_duration_seconds += $pauseDuration;
        $this->is_paused = false;
        $this->is_running = true;
        $this->paused_at = null;
        $this->save();

        // Create log
        TimeTrackingLog::create([
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'time_entry_id' => $this->id,
            'action' => 'resumed',
            'duration_at_action' => $this->current_elapsed_seconds,
        ]);
    }

    /**
     * Calculate and update duration when stopping timer.
     */
    public function stop(): void
    {
        $this->ended_at = now();
        $this->is_running = false;
        $this->is_paused = false;

        // Hitung durasi total dari started_at ke ended_at
        $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);

        $this->save();

        // Create log
        TimeTrackingLog::create([
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'time_entry_id' => $this->id,
            'action' => 'stopped',
            'duration_at_action' => $this->duration_seconds,
        ]);
    }

    /**
     * Complete the timer (stop + mark task as done).
     */
    public function complete(): void
    {
        $this->ended_at = now();
        $this->is_running = false;
        $this->is_paused = false;

        // Hitung durasi total dari started_at ke ended_at
        $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);

        $this->save();

        // Create log
        TimeTrackingLog::create([
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'time_entry_id' => $this->id,
            'action' => 'completed',
            'duration_at_action' => $this->duration_seconds,
        ]);
    }
}

