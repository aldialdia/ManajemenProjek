<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskDeadlineWarning;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    /**
     * Create a new task.
     */
    public function create(array $data): Task
    {
        // Extract assignees before creating task
        $assignees = $data['assignees'] ?? [];
        unset($data['assignees']);

        $task = Task::create($data);

        // Sync multiple assignees
        if (!empty($assignees)) {
            $task->assignees()->sync($assignees);
            $this->notifyTaskAssigned($task, $assignees);
        }

        // Update project status based on new task
        if ($task->project) {
            $task->project->checkAndUpdateStatusBasedOnTasks();
        }

        // Check and send deadline warning immediately if due_date is H-1 or less
        $this->checkAndNotifyDeadlineWarning($task);

        return $task;
    }

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data): Task
    {
        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();
        $oldStatus = $task->status;

        // Extract assignees before updating task
        $assignees = $data['assignees'] ?? null;
        unset($data['assignees']);

        $task->update($data);

        // Sync multiple assignees if provided
        if ($assignees !== null) {
            $task->assignees()->sync($assignees);

            // Notify new assignees only
            $newAssignees = array_diff($assignees, $oldAssigneeIds);
            if (!empty($newAssignees)) {
                $this->notifyTaskAssigned($task, $newAssignees);
            }
        }

        // Update project status if task status changed
        if (isset($data['status']) && $oldStatus->value !== $data['status']) {
            $task->project->checkAndUpdateStatusBasedOnTasks();
        }

        // Check and send deadline warning immediately if due_date is H-1 or less
        $this->checkAndNotifyDeadlineWarning($task->fresh());

        return $task->fresh();
    }

    /**
     * Update task status (for Kanban board).
     */
    public function updateStatus(Task $task, string|TaskStatus $status): Task
    {
        if (is_string($status)) {
            $status = TaskStatus::from($status);
        }

        $oldStatus = $task->status;

        // Only log if status actually changes
        if ($oldStatus !== $status) {
            $task->update(['status' => $status]);

            // Log the status change
            $task->logStatusChange($oldStatus, $status);

            // Send notification if task is completed
            if ($status === TaskStatus::DONE && $oldStatus !== TaskStatus::DONE) {
                $this->notifyTaskCompleted($task);
            }

            // Update project status based on task status changes
            if ($task->project) {
                $task->project->checkAndUpdateStatusBasedOnTasks();
            }
        }

        return $task->fresh();
    }

    /**
     * Assign task to a user (add to assignees).
     */
    public function assignTo(Task $task, ?int $userId): Task
    {
        if ($userId) {
            // Add user to assignees if not already assigned
            if (!$task->assignees()->where('user_id', $userId)->exists()) {
                $task->assignees()->attach($userId, ['assigned_at' => now()]);
            }
            $task->refresh();
            $this->notifyTaskAssigned($task, [$userId]);
        }

        return $task->fresh();
    }

    /**
     * Notify users when task is assigned to them.
     */
    protected function notifyTaskAssigned(Task $task, array $userIds = []): void
    {
        $task->load('project');
        $assignedBy = auth()->user() ?? User::first(); // Fallback for seeding

        foreach ($userIds as $userId) {
            // Don't notify if user assigns task to themselves
            if ($userId === auth()->id()) {
                continue;
            }

            $user = User::find($userId);
            if ($user) {
                $user->notify(new TaskAssigned($task, $assignedBy));
            }
        }
    }

    /**
     * Notify project managers when task is completed.
     */
    protected function notifyTaskCompleted(Task $task): void
    {
        $task->load(['project.users', 'assignee']);

        // Notify project managers
        $managers = $task->project->users()
            ->wherePivot('role', 'manager')
            ->get();

        foreach ($managers as $manager) {
            // Don't notify if the manager completed it themselves
            if ($manager->id !== auth()->id()) {
                $manager->notify(new TaskCompleted($task));
            }
        }
    }

    /**
     * Check and send deadline warning notification immediately.
     * Sends notification when task due_date is tomorrow (H-1) or earlier but not yet overdue.
     */
    protected function checkAndNotifyDeadlineWarning(Task $task): void
    {
        // Skip if task has no due date, is already done, or has no assignees
        if (!$task->due_date || $task->status === TaskStatus::DONE) {
            return;
        }

        // Skip if project is on hold
        if ($task->project && $task->project->isOnHold()) {
            return;
        }

        $task->load('assignees');
        
        if ($task->assignees->isEmpty()) {
            return;
        }

        $tomorrow = now()->addDay()->endOfDay();
        $today = now()->startOfDay();

        // Only notify if due_date is between today and tomorrow (H-1)
        if ($task->due_date->gte($today) && $task->due_date->lte($tomorrow)) {
            foreach ($task->assignees as $assignee) {
                // Use cache to prevent duplicate notifications per task per user per day
                $cacheKey = 'task_deadline_notified_' . $task->id . '_' . $assignee->id . '_' . now()->format('Y-m-d');
                
                if (!Cache::has($cacheKey)) {
                    $assignee->notify(new TaskDeadlineWarning($task));
                    Cache::put($cacheKey, true, now()->addDay());
                }
            }
        }
    }

    /**
     * Bulk update task status.
     */
    public function bulkUpdateStatus(array $taskIds, TaskStatus $status): int
    {
        return Task::whereIn('id', $taskIds)->update(['status' => $status->value]);
    }

    /**
     * Get tasks grouped by status for Kanban board.
     */
    public function getKanbanBoard(int $projectId): array
    {
        $tasks = Task::where('project_id', $projectId)
            ->with('assignees')
            ->orderBy('priority', 'desc')
            ->get();

        return [
            'todo' => $tasks->where('status', TaskStatus::TODO)->values(),
            'in_progress' => $tasks->where('status', TaskStatus::IN_PROGRESS)->values(),
            'review' => $tasks->where('status', TaskStatus::REVIEW)->values(),
            'done' => $tasks->where('status', TaskStatus::DONE)->values(),
        ];
    }

    /**
     * Get overdue tasks.
     */
    public function getOverdueTasks(?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Task::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNot('status', TaskStatus::DONE)
            ->with(['project', 'assignees']);

        if ($userId) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $userId));
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Move task to different project.
     */
    public function moveToProject(Task $task, int $projectId): Task
    {
        $task->update(['project_id' => $projectId]);
        return $task->fresh();
    }

    /**
     * Duplicate a task.
     */
    public function duplicate(Task $task): Task
    {
        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (Copy)';
        $newTask->status = TaskStatus::TODO;
        $newTask->save();

        return $newTask;
    }
}
