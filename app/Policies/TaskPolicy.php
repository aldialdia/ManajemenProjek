<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

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
     * Only project members can view tasks.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isMemberOfProject($task->project);
    }

    /**
     * Determine whether the user can create tasks.
     * Only Manager or Admin can create tasks.
     */
    public function create(User $user): bool
    {
        return true; // Will be checked at controller level with project context
    }

    /**
     * Determine whether the user can update the task.
     * Only Manager or Admin can update.
     * If project is on_hold, only Manager can update.
     */
    public function update(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Only Manager or Admin can update tasks
        if (!$user->isManagerInProject($task->project)) {
            return false;
        }

        // If project is on_hold, still allow manager to update
        return true;
    }

    /**
     * Determine whether the user can update task status.
     * Only Manager, Admin, or Assignee can update status.
     * If project is on_hold, only Manager can update status.
     * Super Admin can update status but cannot directly mark as done (must use approve).
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // Must be project member first (or super admin)
        if (!$user->isSuperAdmin() && !$user->isMemberOfProject($task->project)) {
            return false;
        }

        // If project is on_hold, only Manager or Super Admin can update status
        if ($task->project->isOnHold()) {
            return $user->isSuperAdmin() || $user->isManagerInProject($task->project);
        }

        // Manager or Admin can update status
        if ($user->isManagerInProject($task->project)) {
            return true;
        }

        // Super admin can update status (but controller will prevent marking as done)
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Assignee can update their assigned task status
        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the task.
     * Only Manager or Admin can delete.
     */
    public function delete(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Only Manager or Admin can delete tasks
        return $user->isManagerInProject($task->project);
    }

    /**
     * Determine whether the user can assign the task.
     * Only Manager or Admin can assign.
     */
    public function assign(User $user, Task $task): bool
    {
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        return $user->isManagerInProject($task->project);
    }

    /**
     * Determine whether the user can approve the task.
     * Only Manager or Admin can approve.
     */
    public function approve(User $user, Task $task): bool
    {
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        return $user->isManagerInProject($task->project);
    }

    /**
     * Determine whether the user can upload attachments.
     * If project is on_hold, only Manager can upload.
     */
    public function uploadAttachment(User $user, Task $task): bool
    {
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // If project is on_hold, only Manager can upload
        if ($task->project->isOnHold()) {
            return $user->isManagerInProject($task->project);
        }

        // Manager or Assignee can upload
        return $user->isManagerInProject($task->project) || $task->assignees()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can add comments.
     * If project is on_hold, only Manager can comment.
     */
    public function addComment(User $user, Task $task): bool
    {
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // If project is on_hold, only Manager can comment
        if ($task->project->isOnHold()) {
            return $user->isManagerInProject($task->project);
        }

        // All project members can comment
        return true;
    }
}
