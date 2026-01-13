<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin can view all tasks
        if ($user->isAdmin()) {
            return true;
        }

        // User must be a member of the project
        return $task->project->users->contains($user->id);
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin can update all tasks
        if ($user->isAdmin()) {
            return true;
        }

        // Task assignee can update
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Project manager can update
        return $task->project->managers->contains($user->id);
    }

    /**
     * Determine whether the user can update task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // Admin can update status
        if ($user->isAdmin()) {
            return true;
        }

        // Task assignee can update status
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Project members can update status
        return $task->project->users->contains($user->id);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admin can delete all tasks
        if ($user->isAdmin()) {
            return true;
        }

        // Project manager can delete
        return $task->project->managers->contains($user->id);
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->managers->contains($user->id);
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $task->project->managers->contains($user->id);
    }
}
