<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Create a new task.
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
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

        // You can add notification logic here
        // $this->notifyStatusChange($task, $oldStatus, $status);

        return $task->fresh();
    }

    /**
     * Assign task to a user.
     */
    public function assignTo(Task $task, ?int $userId): Task
    {
        $task->update(['assigned_to' => $userId]);

        // You can add notification logic here
        // if ($userId) {
        //     $this->notifyAssignment($task);
        // }

        return $task->fresh();
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
