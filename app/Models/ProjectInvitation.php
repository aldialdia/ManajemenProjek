<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectInvitation extends Model
{
    protected $fillable = [
        'project_id',
        'invited_by',
        'user_id',
        'email',
        'role',
        'status',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Get the invited user (if registered).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Accept the invitation.
     */
    public function accept(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update(['status' => 'accepted']);

        // Add user to project
        $this->project->users()->attach($this->user_id, ['role' => $this->role]);

        return true;
    }

    /**
     * Decline the invitation.
     */
    public function decline(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update(['status' => 'declined']);

        return true;
    }

    /**
     * Scope pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }
}
