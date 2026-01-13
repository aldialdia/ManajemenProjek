<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
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
     */
    public function index(Request $request): View
    {
        $query = Task::with(['project', 'assignee']);

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->paginate(15);
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'projects', 'users'));
    }

    /**
     * Display Kanban board view.
     */
    public function kanban(): View
    {
        return view('tasks.kanban');
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

        // project_id is required - task must be created from within a project
        $project = Project::with('users')->findOrFail($request->project_id);

        // Only get users that are members of this project
        $users = $project->users()->orderBy('name')->get();

        return view('tasks.create', compact('project', 'users'));
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = $this->taskService->create($request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): View
    {
        $task->load(['project', 'assignee', 'comments.user', 'attachments']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task): View
    {
        $projects = Project::where('status', 'active')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    /**
     * Update the specified task.
     */
    public function update(StoreTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->update($task, $request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Update task status (for Kanban drag & drop).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $this->taskService->updateStatus($task, $request->validated('status'));

        return response()->json([
            'success' => true,
            'message' => 'Task status updated.',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $projectId = $task->project_id;
        $task->delete();

        return redirect()
            ->route('projects.show', $projectId)
            ->with('success', 'Task deleted successfully.');
    }
}
