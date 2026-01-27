<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that are guarded from mass assignment.
     * Status hanya bisa diubah secara eksplisit oleh admin.
     */
    protected $guarded = ['status'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all projects this user is assigned to.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all tasks assigned to this user (via pivot table).
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Alias for tasks - all tasks assigned to this user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Get all comments by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all attachments uploaded by this user.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * Get all time entries for this user.
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Get user's role in a specific project.
     */
    public function getRoleInProject(Project $project): ?string
    {
        $pivotData = $this->projects()->where('project_id', $project->id)->first();
        return $pivotData?->pivot?->role;
    }

    /**
     * Check if user is manager or admin in a specific project.
     */
    public function isManagerInProject(Project $project): bool
    {
        $role = $this->getRoleInProject($project);
        return in_array($role, ['manager', 'admin']);
    }

    /**
     * Check if user is admin in a specific project.
     */
    public function isAdminInProject(Project $project): bool
    {
        return $this->getRoleInProject($project) === 'admin';
    }

    /**
     * Check if user is member of a specific project.
     */
    public function isMemberOfProject(Project $project): bool
    {
        return $this->projects()->where('project_id', $project->id)->exists();
    }

    /**
     * Get user initials for avatar.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials;
    }

    /**
     * Check if user is a system admin.
     * Admin bisa melihat semua project.
     */
    public function isAdmin(): bool
    {
        return $this->status === 'admin';
    }
}
