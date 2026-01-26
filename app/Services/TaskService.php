<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskCompleted;
use Illuminate\Support\Facades\DB;

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

        // Set primary assignee (first one) for backward compatibility
        if (!empty($assignees)) {
            $data['assigned_to'] = $assignees[0];
        }

        $task = Task::create($data);

        // Sync multiple assignees
        if (!empty($assignees)) {
            $task->assignees()->sync($assignees);
            $this->notifyTaskAssigned($task, $assignees);
        }

        return $task;
    }

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data): Task
    {
        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();

        // Extract assignees before updating task
        $assignees = $data['assignees'] ?? null;
        unset($data['assignees']);

        // Set primary assignee (first one) for backward compatibility
        if ($assignees !== null && !empty($assignees)) {
            $data['assigned_to'] = $assignees[0];
        }

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
        $task->update(['status' => $status]);

        // Send notification if task is completed
        if ($status === TaskStatus::DONE && $oldStatus !== TaskStatus::DONE) {
            $this->notifyTaskCompleted($task);
        }

        // Auto-update project status from 'new' to 'in_progress' when work starts
        if ($status === TaskStatus::IN_PROGRESS && $task->project) {
            $task->project->startIfNew();
        }

        // Check if all tasks are done and update project status accordingly
        if ($task->project) {
            $task->project->checkAndUpdateStatusBasedOnTasks();
        }

        return $task->fresh();
    }

    /**
     * Assign task to a user.
     */
    public function assignTo(Task $task, ?int $userId): Task
    {
        $task->update(['assigned_to' => $userId]);

        if ($userId) {
            $task->refresh();
            $this->notifyTaskAssigned($task);
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
            ->with('assignee')
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
            ->with(['project', 'assignee']);

        if ($userId) {
            $query->where('assigned_to', $userId);
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
