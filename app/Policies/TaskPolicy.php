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
     */
    public function update(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Only Manager or Admin can update tasks
        return $user->isManagerInProject($task->project);
    }

    /**
     * Determine whether the user can update task status.
     * Only Manager, Admin, or Assignee can update status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Manager or Admin can update status
        if ($user->isManagerInProject($task->project)) {
            return true;
        }

        // Assignee can update their assigned task status
        return $task->assigned_to === $user->id;
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
}
