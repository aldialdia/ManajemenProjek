<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {
    }

    /**
     * Display a listing of tasks.
     * Shows tasks from a specific project or all user's projects.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $project = null;

        // Only get parent tasks (not subtasks), subtasks will be loaded via relation
        $query = Task::with(['assignees', 'subtasks.assignees'])
            ->whereNull('parent_task_id'); // Only parent tasks

        // If project_id is provided, filter by that project
        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->project_id);

            // Check if user is member of this project
            if (!$user->isMemberOfProject($project)) {
                abort(403, 'Anda bukan anggota project ini.');
            }

            $query->where('project_id', $project->id);
        } else {
            // If no project specified, show tasks from all user's projects
            $userProjectIds = $user->projects()->pluck('projects.id');
            $query->whereIn('project_id', $userProjectIds);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->paginate(15);

        return view('tasks.index', compact('tasks', 'project'));
    }

    /**
     * Display Kanban board view.
     */
    public function kanban(Request $request): View
    {
        $user = auth()->user();
        $project = null;
        $showSubtasks = $request->boolean('show_subtasks', false);

        // Build query for tasks
        $query = Task::with(['project', 'assignees', 'subtasks.assignees', 'parent']);

        // Only show parent tasks unless show_subtasks is enabled
        if (!$showSubtasks) {
            $query->whereNull('parent_task_id');
        }

        // If project_id is provided, filter by that project
        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->project_id);

            // Check if user is member of this project
            if (!$user->isMemberOfProject($project)) {
                abort(403, 'Anda bukan anggota project ini.');
            }

            $query->where('project_id', $project->id);
        } else {
            // If no project specified, show tasks from all user's projects
            $userProjectIds = $user->projects()->pluck('projects.id');
            $query->whereIn('project_id', $userProjectIds);
        }

        $tasks = $query->get();

        // Add permission info for each task (for frontend validation)
        $tasks->each(function ($task) use ($user) {
            $isManager = $user->isManagerInProject($task->project);
            $isAssignee = $task->isAssignedTo($user);
            $projectOnHold = $task->project->isOnHold();

            // Pass assignee status to frontend (for done column protection)
            $task->is_assignee = $isAssignee;

            // If project is on_hold, only manager can change status
            if ($projectOnHold) {
                $task->can_update_status = $isManager;
            }
            // For review and done tasks, only manager can change status (approve/reopen)
            elseif (in_array($task->status->value, ['review', 'done'])) {
                $task->can_update_status = $isManager;
            } else {
                $task->can_update_status = $isManager || $isAssignee;
            }
        });

        // Get recent activity logs for tasks
        $taskIds = $tasks->pluck('id');
        $statusLogs = \App\Models\TaskStatusLog::whereIn('task_id', $taskIds)
            ->with(['task', 'changedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('tasks.kanban', compact('tasks', 'project', 'showSubtasks', 'statusLogs'));
    }

    /**
     * Display Calendar view.
     */
    public function calendar(Request $request): View
    {
        $user = auth()->user();
        $project = null;

        $query = Task::with(['project', 'assignees', 'parent'])
            ->whereNotNull('due_date');

        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->project_id);
            if (!$user->isMemberOfProject($project)) {
                abort(403);
            }
            $query->where('project_id', $project->id);
        } else {
            $query->whereIn('project_id', $user->projects()->pluck('projects.id'));
        }

        $calendarTasks = $query->get()->map(function ($task) {
            $hexColor = $task->status->hexColor();

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->due_date->format('Y-m-d'),
                'end' => $task->due_date->addDay()->format('Y-m-d'), // Single day event on due date
                'url' => route('tasks.show', $task),
                'backgroundColor' => $hexColor,
                'borderColor' => $hexColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'priority' => $task->priority->label(),
                    'status' => $task->status->label(),
                    'assignee' => $task->assignee?->name ?? 'Unassigned',
                    'description' => Str::limit($task->description, 50),
                    'parent_task_id' => $task->parent_task_id,
                    'parent_due_date' => $task->parent?->due_date?->format('Y-m-d'),
                ]
            ];
        });

        // Gantt Data - All tasks with actual dates
        // Cap end date at project end date if it exceeds
        $projectEnd = $project?->end_date;
        $ganttTasks = $query->get()->map(function ($task) use ($projectEnd) {
            $startDate = $task->start_date ?? now();
            $endDate = $task->due_date ?? now()->addDay();

            // Cap end date at project end date
            if ($projectEnd && $endDate->gt($projectEnd)) {
                $endDate = $projectEnd;
            }

            // Cap subtask end date at parent task's due date
            if ($task->parent_task_id && $task->parentTask && $task->parentTask->due_date) {
                $parentDueDate = $task->parentTask->due_date;
                if ($endDate->gt($parentDueDate)) {
                    $endDate = $parentDueDate;
                }
            }

            // Ensure end date is not before start date
            if ($endDate->lt($startDate)) {
                $endDate = $startDate->copy()->addDay();
            }

            // Add subtask indicator
            $name = $task->parent_task_id ? '↳ ' . $task->title : $task->title;
            $customClass = 'bar-' . $task->status->value;
            if ($task->parent_task_id) {
                $customClass .= ' subtask-bar';
            }

            return [
                'id' => (string) $task->id,
                'name' => $name,
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'progress' => $task->status === \App\Enums\TaskStatus::DONE ? 100 : ($task->status === \App\Enums\TaskStatus::IN_PROGRESS ? 50 : 0),
                'custom_class' => $customClass,
                'parent_task_id' => $task->parent_task_id,
                'parent_due_date' => $task->parent?->due_date?->format('Y-m-d'),
            ];
        });

        // Project end date for marking on calendar/gantt
        $projectEndDate = $project?->end_date?->format('Y-m-d');

        // Check if current user is manager/admin for this project (can edit project deadline)
        $isManager = $project ? auth()->user()->isManagerInProject($project) : false;

        // Check if project is on hold (disable all editing)
        $projectOnHold = $project ? $project->isOnHold() : false;

        return view('tasks.calendar', compact('calendarTasks', 'ganttTasks', 'project', 'projectEndDate', 'isManager', 'projectOnHold'));
    }

    /**
     * Update task dates via AJAX (Drag & Drop).
     */
    public function updateDates(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'due_date' => 'required|date|after_or_equal:start_date',
        ]);

        $newStartDate = $validated['start_date'] ?? null;
        $newDueDate = $validated['due_date'];
        $oldDueDate = $task->due_date?->format('Y-m-d');
        $wasCapped = false;

        // If this is a subtask, cap due_date at parent task's due_date
        if ($task->parent_task_id && $task->parentTask && $task->parentTask->due_date) {
            $parentDueDate = $task->parentTask->due_date;
            if (\Carbon\Carbon::parse($newDueDate)->gt($parentDueDate)) {
                $validated['due_date'] = $parentDueDate->format('Y-m-d');
                $newDueDate = $validated['due_date'];
                $wasCapped = true;
            }
        }

        $task->update($validated);

        // Notify assignee if due_date was changed/capped
        if ($task->assignee && $oldDueDate && $oldDueDate !== $newDueDate) {
            $reason = $wasCapped ? 'melebihi deadline tugas utama' : 'perubahan jadwal';
            $task->assignee->notify(new \App\Notifications\TaskDeadlineAdjusted(
                $task,
                $oldDueDate,
                $newDueDate,
                $reason
            ));
        }

        // Early Warning: If new due_date is tomorrow (H-1), send warning notification
        if ($task->assignee && $task->status !== \App\Enums\TaskStatus::DONE) {
            $dueDateCarbon = \Carbon\Carbon::parse($newDueDate);
            $tomorrow = now()->addDay()->startOfDay();
            $today = now()->startOfDay();

            // Check if due_date is between today and tomorrow (H-1)
            if ($dueDateCarbon->gte($today) && $dueDateCarbon->lte($tomorrow)) {
                $cacheKey = 'task_deadline_notified_' . $task->id;
                if (!cache()->has($cacheKey)) {
                    $task->refresh(); // Refresh to get updated due_date as Carbon
                    $task->assignee->notify(new \App\Notifications\TaskDeadlineWarning($task));
                    cache()->put($cacheKey, true, now()->addDay());
                }
            }
        }

        // If this is a parent task, adjust subtasks as needed
        if ($task->subtasks()->count() > 0) {
            // Cap subtasks with start_date earlier than new parent start_date
            if ($newStartDate) {
                $task->subtasks()
                    ->whereNotNull('start_date')
                    ->where('start_date', '<', $newStartDate)
                    ->each(function ($subtask) use ($newStartDate) {
                        $subtask->update(['start_date' => $newStartDate]);
                    });
            }

            // Cap subtasks with due_date exceeding new parent due_date
            $task->subtasks()
                ->whereNotNull('due_date')
                ->where('due_date', '>', $newDueDate)
                ->each(function ($subtask) use ($newDueDate) {
                    $oldDeadline = $subtask->due_date->format('Y-m-d');
                    $subtask->update(['due_date' => $newDueDate]);

                    // Notify assignee if exists
                    if ($subtask->assignee) {
                        $subtask->assignee->notify(new \App\Notifications\TaskDeadlineAdjusted(
                            $subtask,
                            $oldDeadline,
                            $newDueDate,
                            'penyesuaian deadline tugas utama'
                        ));
                    }
                });
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanggal task berhasil diperbarui.',
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Redirect to projects if no project_id provided
        if (!$request->filled('project_id')) {
            return redirect()
                ->route('projects.index')
                ->with('warning', 'Silakan pilih project terlebih dahulu untuk membuat task.');
        }

        $project = Project::with('users')->findOrFail($request->project_id);

        // BLOCK task creation when project is on_hold
        if ($project->isOnHold()) {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Project sedang ditunda. Tidak dapat membuat task baru.');
        }

        // Only Manager or Admin can create tasks
        if (!auth()->user()->isManagerInProject($project)) {
            abort(403, 'Hanya Manager atau Admin yang dapat membuat task.');
        }

        // Only get users that are members of this project
        $users = $project->users()->orderBy('name')->get();

        // Get existing parent tasks for subtask dropdown (only top-level tasks)
        $parentTasks = Task::where('project_id', $project->id)
            ->whereNull('parent_task_id')
            ->orderBy('title')
            ->get();

        return view('tasks.create', compact('project', 'users', 'parentTasks'));
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $project = Project::findOrFail($request->project_id);

        // BLOCK task creation when project is on_hold
        if ($project->isOnHold()) {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Project sedang ditunda. Tidak dapat membuat task baru.');
        }

        // Only Manager or Admin can create tasks
        if (!auth()->user()->isManagerInProject($project)) {
            abort(403, 'Hanya Manager atau Admin yang dapat membuat task.');
        }

        // Add created_by to the validated data
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        // If this is a subtask, cap due_date at parent task's due_date
        if (!empty($data['parent_task_id'])) {
            $parentTask = Task::find($data['parent_task_id']);
            if ($parentTask && $parentTask->due_date && !empty($data['due_date'])) {
                if (\Carbon\Carbon::parse($data['due_date'])->gt($parentTask->due_date)) {
                    $data['due_date'] = $parentTask->due_date->format('Y-m-d');
                }
            }
        }

        $task = $this->taskService->create($data);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project', 'assignees', 'creator', 'parent', 'subtasks.assignees', 'comments.user', 'attachments']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        // Only get users from this project
        $users = $task->project->users()->orderBy('name')->get();

        // Get existing parent tasks (exclude current task and its subtasks)
        $parentTasks = Task::where('project_id', $task->project_id)
            ->whereNull('parent_task_id')
            ->where('id', '!=', $task->id)
            ->orderBy('title')
            ->get();

        return view('tasks.edit', compact('task', 'users', 'parentTasks'));
    }

    /**
     * Update the specified task.
     */
    public function update(StoreTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        // If this is a subtask, cap due_date at parent task's due_date
        if ($task->parent_task_id) {
            $parentTask = $task->parentTask;
            if ($parentTask && $parentTask->due_date && !empty($data['due_date'])) {
                if (\Carbon\Carbon::parse($data['due_date'])->gt($parentTask->due_date)) {
                    $data['due_date'] = $parentTask->due_date->format('Y-m-d');
                }
            }
        }

        $this->taskService->update($task, $data);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Update task status (for Kanban drag & drop and form submissions).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->authorize('updateStatus', $task);

        $user = auth()->user();
        $isManager = $user->isManagerInProject($task->project);
        $isAssignee = $task->assignees()->where('users.id', $user->id)->exists();
        $newStatus = $request->validated('status');

        // Only assignee can mark task as done (managers can set to review for approval)
        if (!$isAssignee && $newStatus === 'done') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya assignee yang dapat menandai task sebagai selesai.',
                ], 403);
            }
            return redirect()
                ->route('tasks.show', $task)
                ->with('error', 'Hanya assignee yang dapat menandai task sebagai selesai.');
        }

        // Only Manager/Admin can reopen a done task
        if ($task->status->value === 'done' && !$isManager && !$user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Manager atau Admin yang dapat membuka kembali task yang sudah selesai.',
                ], 403);
            }
            return redirect()
                ->route('tasks.show', $task)
                ->with('error', 'Hanya Manager atau Admin yang dapat membuka kembali task yang sudah selesai.');
        }

        $this->taskService->updateStatus($task, $newStatus);

        // Return JSON for AJAX requests (Kanban), redirect for form submissions
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated.',
                'task' => $task->fresh(),
            ]);
        }

        $statusLabel = $task->fresh()->status->value === 'done' ? 'selesai' : 'dibuka kembali';
        return redirect()
            ->route('tasks.show', $task)
            ->with('success', "Task berhasil ditandai $statusLabel.");
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $projectId = $task->project_id;
        $task->delete();

        return redirect()
            ->route('tasks.index', ['project_id' => $projectId])
            ->with('success', 'Task berhasil dihapus.');
    }

    /**
     * Approve a task (change from review to done).
     * Only Manager or Admin can approve.
     */
    public function approve(Task $task): RedirectResponse
    {
        $this->authorize('approve', $task);

        // Only approve if task is in 'review' status (pending approval)
        if ($task->status !== \App\Enums\TaskStatus::REVIEW) {
            return redirect()
                ->route('tasks.show', $task)
                ->with('error', 'Task hanya bisa di-approve jika statusnya Pending Approval.');
        }

        $task->update(['status' => 'done']);

        // Check if all tasks are done and update project status
        $task->project->checkAndUpdateStatusBasedOnTasks();

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task berhasil di-approve.');
    }
}
