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

        return view('tasks.kanban', compact('tasks', 'project', 'showSubtasks'));
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
}
