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
        // Admin can view all projects
        if ($user->isAdmin()) {
            return true;
        }

        // User must be a member of the project
        return $project->users->contains($user->id);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        // Admin and managers can create projects
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin can update all projects
        if ($user->isAdmin()) {
            return true;
        }

        // Project manager can update
        return $project->managers->contains($user->id);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only admin can delete projects
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageTeam(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $project->managers->contains($user->id);
    }
}
