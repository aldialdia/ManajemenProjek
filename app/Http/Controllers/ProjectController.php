<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
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

        // Assign pembuat project sebagai Manager otomatis
        // Cek jika user belum terdaftar di project (untuk menghindari duplikasi jika dia memilih dirinya sendiri di form)
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            $project->users()->attach(auth()->id(), ['role' => 'manager']);
        } else {
            // Jika sudah terdaftar (misal dipilih sbg member), update jadi manager
            $project->users()->updateExistingPivot(auth()->id(), ['role' => 'manager']);
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
}
