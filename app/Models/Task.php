<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'assigned_to',
        'created_by',
        'parent_task_id',
        'priority',
        'status',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
        ];
        }

    /**
     * Get the project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all comments for this task.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all attachments for this task.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get the parent task (if this is a subtask).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Get all subtasks of this task.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Get all time entries for this task.
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Check if this is a subtask.
     */
    public function isSubtask(): bool
    {
        return $this->parent_task_id !== null;
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && $this->status !== TaskStatus::DONE;
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::DONE;
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, TaskStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by priority.
     */
    public function scopePriority($query, TaskPriority $priority)
    {
        return $query->where('priority', $priority);
    }
}
