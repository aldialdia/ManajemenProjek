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
     * BLOCKED when project is on_hold.
     */
    public function update(User $user, Task $task): bool
    {
        // BLOCK all updates when project is on_hold
        if ($task->project->isOnHold()) {
            return false;
        }

        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Only Manager or Admin can update tasks
        return $user->isManagerInProject($task->project);
    }

    /**
     * Determine whether the user can update task status.
     * BLOCKED when project is on_hold (only managers can change project status to resume).
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // BLOCK all status updates when project is on_hold
        if ($task->project->isOnHold()) {
            return false;
        }

        // Must be project member first (or super admin)
        if (!$user->isSuperAdmin() && !$user->isMemberOfProject($task->project)) {
            return false;
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
     * BLOCKED when project is on_hold.
     */
    public function delete(User $user, Task $task): bool
    {
        // BLOCK deletion when project is on_hold
        if ($task->project->isOnHold()) {
            return false;
        }

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
     * Only Manager or Admin in the project can approve.
     * Super Admin cannot approve (they are not project members).
     */
    public function approve(User $user, Task $task): bool
    {
        // Super Admin cannot approve tasks
        if ($user->isSuperAdmin()) {
            return false;
        }

        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Only Manager or Admin in the project can approve
        $role = $user->getRoleInProject($task->project);
        return in_array($role, ['manager', 'admin']);
    }

    /**
     * Determine whether the user can upload attachments.
     * BLOCKED when project is on_hold.
     */
    public function uploadAttachment(User $user, Task $task): bool
    {
        // BLOCK uploads when project is on_hold
        if ($task->project->isOnHold()) {
            return false;
        }

        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Manager or Assignee can upload
        return $user->isManagerInProject($task->project) || $task->assignees()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can add comments.
     * BLOCKED when project is on_hold.
     */
    public function addComment(User $user, Task $task): bool
    {
        // BLOCK comments when project is on_hold
        if ($task->project->isOnHold()) {
            return false;
        }

        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // All project members can comment
        return true;
    }
}
