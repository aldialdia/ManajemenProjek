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
     * Manager, Admin, Creator, or Assignee can update.
     */
    public function update(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Manager or Admin can update any task
        if ($user->isManagerInProject($task->project)) {
            return true;
        }

        // Creator can update their own task
        if ($task->created_by === $user->id) {
            return true;
        }

        // Assignee can update their assigned task
        if ($task->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update task status.
     * All project members can update status (for Kanban).
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->isMemberOfProject($task->project);
    }

    /**
     * Determine whether the user can delete the task.
     * Only Manager, Admin, or Creator can delete.
     */
    public function delete(User $user, Task $task): bool
    {
        // Must be project member first
        if (!$user->isMemberOfProject($task->project)) {
            return false;
        }

        // Manager or Admin can delete any task
        if ($user->isManagerInProject($task->project)) {
            return true;
        }

        // Creator can delete their own task
        if ($task->created_by === $user->id) {
            return true;
        }

        return false;
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
