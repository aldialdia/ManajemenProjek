<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Faker\Provider\UserAgent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProjectService $projectService
    ) {
    }


    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->projectService->create($request->validated());

        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;

        // Assign pembuat project sebagai Manager otomatis
        // Cek jika user belum terdaftar di project (untuk menghindari duplikasi jika dia memilih dirinya sendiri di form)
        if (!$project->users()->where('user_id', $userId)->exists()) {
            $project->users()->attach($userId, ['role' => 'manager']);
        } else {
            // Jika sudah terdaftar (misal dipilih sbg member), update jadi manager
            $project->users()->updateExistingPivot($userId, ['role' => 'manager']);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['client', 'users', 'tasks.assignee', 'attachments', 'comments.user']);

        $tasksByStatus = $project->tasks->groupBy('status');

        return view('projects.show', compact('project', 'tasksByStatus'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        $users = User::orderBy('name')->get();

        return view('projects.edit', compact('project', 'users'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->projectService->update($project, $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Update project end date via AJAX (Calendar Drag & Drop).
     * Only managers/admins can do this.
     */
    public function updateEndDate(\Illuminate\Http\Request $request, Project $project): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if user is manager/admin
        if (!$user->isManagerInProject($project)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'end_date' => 'required|date',
        ]);

        $newEndDate = $validated['end_date'];
        
        // Find tasks with deadlines exceeding the new project end date
        $affectedTasks = $project->tasks()
            ->whereNotNull('due_date')
            ->where('due_date', '>', $newEndDate)
            ->get();

        // Update task deadlines and notify assigned users
        foreach ($affectedTasks as $task) {
            $oldDeadline = $task->due_date->format('Y-m-d');
            
            // Update task deadline to match project end date
            $task->update(['due_date' => $newEndDate]);
            
            // Notify assigned user if exists
            if ($task->assignee) {
                $task->assignee->notify(new \App\Notifications\TaskDeadlineAdjusted(
                    $task,
                    $oldDeadline,
                    $newEndDate,
                    'penyesuaian deadline project'
                ));
            }
        }

        // Update project end date
        $project->update(['end_date' => $newEndDate]);

        return response()->json([
            'success' => true,
            'adjusted_tasks' => $affectedTasks->count()
        ]);
    }
}
