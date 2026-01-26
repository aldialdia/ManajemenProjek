<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ProjectStatus;

class ProjectStatusLog extends Model
{
    protected $fillable = [
        'project_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
    ];

    protected $casts = [
        'from_status' => ProjectStatus::class,
        'to_status' => ProjectStatus::class,
    ];

    /**
     * Get the project that owns this log.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who made this change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted status change label.
     */
    public function getChangeDescriptionAttribute(): string
    {
        $from = $this->from_status?->label() ?? 'New';
        $to = $this->to_status->label();
        return "{$from} → {$to}";
    }
}
