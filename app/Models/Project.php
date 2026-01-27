<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Log status changes
        static::updating(function ($project) {
            if ($project->isDirty('status')) {
                $originalStatus = $project->getOriginal('status');
                $fromStatusValue = $originalStatus instanceof ProjectStatus
                    ? $originalStatus->value
                    : $originalStatus;

                $project->logStatusChange(
                    $fromStatusValue,
                    $project->status
                );
            }
        });

        // Log initial status on create
        static::created(function ($project) {
            $project->logStatusChange(null, $project->status);
        });
    }

    protected $fillable = [
        'name',
        'description',
        'goals',
        'status',
        'client_id',
        'start_date',
        'end_date',
        'budget',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    /**
     * Get the client that owns the project.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all team members assigned to this project.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all tasks for this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all documents for this project (Module 8).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all attachments for this project.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all comments for this project.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all status change logs for this project.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProjectStatusLog::class)->orderByDesc('created_at');
    }

    /**
     * Get the latest status change log.
     */
    public function latestStatusLog()
    {
        return $this->hasOne(ProjectStatusLog::class)->latestOfMany();
    }

    /**
     * Log a status change.
     */
    public function logStatusChange(?string $fromStatus, $toStatus, ?string $notes = null): void
    {
        $toStatusValue = $toStatus instanceof ProjectStatus ? $toStatus->value : $toStatus;

        $this->statusLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatusValue,
            'changed_by' => auth()->id(),
            'notes' => $notes,
        ]);
    }

    /**
     * Get the date when project entered current status.
     */
    public function getCurrentStatusDateAttribute(): ?\Carbon\Carbon
    {
        $log = $this->statusLogs()->where('to_status', $this->status)->first();
        return $log?->created_at;
    }

    /**
     * Get project managers.
     */
    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    /**
     * Get project members.
     */
    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    /**
     * Check if project is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === ProjectStatus::IN_PROGRESS;
    }

    /**
     * Check if project is new.
     */
    public function isNew(): bool
    {
        return $this->status === ProjectStatus::NEW;
    }

    /**
     * Check if project is done.
     */
    public function isDone(): bool
    {
        return $this->status === ProjectStatus::DONE;
    }

    /**
     * Check if project is on hold (ditunda).
     */
    public function isOnHold(): bool
    {
        return $this->status === ProjectStatus::ON_HOLD;
    }

    /**
     * Get progress percentage based on completed tasks.
     */
    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->tasks()->where('status', 'done')->count();
        return (int) round(($completed / $total) * 100);
    }

    /**
     * Check task statuses and update project status accordingly.
     * Logic:
     * - DONE: All tasks are done (progress 100%)
     * - IN_PROGRESS: Has any task that is in_progress or review
     * - NEW: No tasks or all tasks are 'todo'
     * - ON_HOLD: Not auto-updated, only changed manually via button
     */
    public function checkAndUpdateStatusBasedOnTasks(): void
    {
        // Don't auto-update if project is on hold - only manual toggle can change this
        if ($this->status === ProjectStatus::ON_HOLD) {
            return;
        }

        $totalTasks = $this->tasks()->count();

        // If no tasks, keep as NEW
        if ($totalTasks === 0) {
            if ($this->status !== ProjectStatus::NEW) {
                $this->update(['status' => ProjectStatus::NEW]);
            }
            return;
        }

        $doneTasks = $this->tasks()->where('status', 'done')->count();
        $todoTasks = $this->tasks()->where('status', 'todo')->count();
        $inProgressTasks = $this->tasks()->whereIn('status', ['in_progress', 'review'])->count();

        $newStatus = null;

        // All tasks done (progress 100%) -> Project DONE
        if ($doneTasks === $totalTasks) {
            $newStatus = ProjectStatus::DONE;
        }
        // Has any task in progress or review -> IN_PROGRESS
        elseif ($inProgressTasks > 0) {
            $newStatus = ProjectStatus::IN_PROGRESS;
        }
        // All tasks are todo -> Project NEW
        elseif ($todoTasks === $totalTasks) {
            $newStatus = ProjectStatus::NEW;
        }
        // Has some done but not all, and no in_progress -> IN_PROGRESS
        elseif ($doneTasks > 0 && $doneTasks < $totalTasks) {
            $newStatus = ProjectStatus::IN_PROGRESS;
        }

        // Update only if status changed
        if ($newStatus !== null && $this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Set project to in_progress if it's currently new.
     */
    public function startIfNew(): void
    {
        if ($this->status === ProjectStatus::NEW) {
            $this->update(['status' => ProjectStatus::IN_PROGRESS]);
        }
    }
}
