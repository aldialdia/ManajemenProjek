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
        $query = Task::with(['assignee', 'subtasks.assignee'])
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
        $query = Task::with(['project', 'assignee', 'subtasks.assignee', 'parent']);

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
            $isAssignee = $task->assigned_to === $user->id;

            // For review and done tasks, only manager can change status (approve/reopen)
            if (in_array($task->status->value, ['review', 'done'])) {
                $task->can_update_status = $isManager;
            } else {
                $task->can_update_status = $isManager || $isAssignee;
            }
        });

        return view('tasks.kanban', compact('tasks', 'project', 'showSubtasks'));
    }

    /**
     * Display Calendar view.
     */
    public function calendar(Request $request): View
    {
        $user = auth()->user();
        $project = null;

        $query = Task::with(['project', 'assignee'])
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
                ]
            ];
        });

        // Gantt Data - All tasks with actual dates
        $ganttTasks = $query->get()->map(function ($task) {
            return [
                'id' => (string) $task->id,
                'name' => $task->title,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : now()->format('Y-m-d'),
                'end' => $task->due_date ? $task->due_date->format('Y-m-d') : now()->addDay()->format('Y-m-d'),
                'progress' => $task->status === \App\Enums\TaskStatus::DONE ? 100 : ($task->status === \App\Enums\TaskStatus::IN_PROGRESS ? 50 : 0),
                'dependencies' => $task->parent_task_id ? (string) $task->parent_task_id : null,
                'custom_class' => 'bar-' . $task->status->value,
            ];
        });

        // Project end date for marking on calendar/gantt
        $projectEndDate = $project?->end_date?->format('Y-m-d');

        return view('tasks.calendar', compact('calendarTasks', 'ganttTasks', 'project', 'projectEndDate'));
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

        $task->update($validated);

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

        // Only Manager or Admin can create tasks
        if (!auth()->user()->isManagerInProject($project)) {
            abort(403, 'Hanya Manager atau Admin yang dapat membuat task.');
        }

        // Add created_by to the validated data
        $data = $request->validated();
        $data['created_by'] = auth()->id();

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

        $task->load(['project', 'assignee', 'creator', 'parent', 'subtasks.assignee', 'comments.user', 'attachments']);

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

        $this->taskService->update($task, $request->validated());

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

        // Only Manager/Admin can reopen a done task
        if ($task->status->value === 'done' && !$isManager) {
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

        $this->taskService->updateStatus($task, $request->validated('status'));

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

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task berhasil di-approve.');
    }
}
