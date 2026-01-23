<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // User must be a member of the project (manager or member)
        return $project->users->contains($user->id);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        // Siapapun yang login bisa buat project (nanti otomatis jadi manager)
        return true;
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Manager or Admin can update project
        $member = $project->users()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['manager', 'admin']);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only project manager can delete
        $member = $project->users()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        $member = $project->users()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        $member = $project->users()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageTeam(User $user, Project $project): bool
    {
        $member = $project->users()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }
}
