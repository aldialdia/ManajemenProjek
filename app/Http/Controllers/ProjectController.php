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

    /**
     * Display project kanban board.
     */
    public function kanban(): View
    {
        $user = Auth::user();
        $userProjectIds = $user->projects()->pluck('projects.id')->toArray();

        $projectStatuses = [
            'new' => ['label' => 'Baru', 'color' => '#94a3b8', 'icon' => 'fa-plus-circle'],
            'in_progress' => ['label' => 'Berjalan', 'color' => '#3b82f6', 'icon' => 'fa-spinner'],
            'on_hold' => ['label' => 'Ditunda', 'color' => '#f59e0b', 'icon' => 'fa-pause-circle'],
            'done' => ['label' => 'Selesai', 'color' => '#10b981', 'icon' => 'fa-check-circle'],
        ];

        $projects = Project::whereIn('id', $userProjectIds)
            ->with(['tasks', 'latestStatusLog.changedBy'])
            ->get()
            ->groupBy(fn($p) => $p->status->value);

        return view('projects.kanban', compact('projectStatuses', 'projects'));
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

        $project->load(['client', 'users', 'tasks.assignees', 'attachments', 'comments.user']);

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
     * Check if updating project end date will affect tasks.
     * Returns count of tasks that will be adjusted.
     */
    public function checkEndDateUpdate(\Illuminate\Http\Request $request, Project $project): \Illuminate\Http\JsonResponse
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
        $affectedTasksCount = $project->tasks()
            ->whereNotNull('due_date')
            ->where('due_date', '>', $newEndDate)
            ->count();

        return response()->json([
            'success' => true,
            'has_affected_tasks' => $affectedTasksCount > 0,
            'affected_tasks_count' => $affectedTasksCount
        ]);
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
            'confirmed' => 'sometimes|boolean', // Untuk konfirmasi dari user
        ]);

        $newEndDate = $validated['end_date'];

        // Find tasks with deadlines exceeding the new project end date
        $affectedTasks = $project->tasks()
            ->whereNotNull('due_date')
            ->where('due_date', '>', $newEndDate)
            ->get();

        // Jika ada affected tasks dan belum dikonfirmasi, return butuh konfirmasi
        if ($affectedTasks->count() > 0 && !($validated['confirmed'] ?? false)) {
            return response()->json([
                'success' => false,
                'needs_confirmation' => true,
                'affected_tasks_count' => $affectedTasks->count()
            ]);
        }

        // Update task deadlines and notify assigned users
        foreach ($affectedTasks as $task) {
            $oldDeadline = $task->due_date->format('Y-m-d');

            // Update task deadline to match project end date
            $task->update(['due_date' => $newEndDate]);

            // Notify all assigned users if exists
            foreach ($task->assignees as $assignee) {
                $assignee->notify(new \App\Notifications\TaskDeadlineAdjusted(
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
    /**
     * Update project status via AJAX (Kanban Drag & Drop).
     * Only managers/admins can do this.
     * When project is put ON_HOLD, all incomplete tasks are also put on hold.
     * When project is restored from ON_HOLD, tasks return to their previous state.
     */
    public function updateStatus(\Illuminate\Http\Request $request, Project $project): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if user is manager/admin in project OR system admin
        if (!$user->isManagerInProject($project) && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,on_hold,done',
        ]);

        $oldStatus = $project->status->value;
        $newStatus = $validated['status'];

        // Don't update if same status
        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'changed' => false]);
        }

        // Get task statistics (exclude on_hold tasks for normal counting)
        $totalTasks = $project->tasks()->count();
        $doneTasks = $project->tasks()->where('status', 'done')->count();
        $todoTasks = $project->tasks()->where('status', 'todo')->count();
        $incompleteTasks = $totalTasks - $doneTasks;

        // ============ ON_HOLD LOGIC ============
        // Note: Task status is NOT changed when project is put on hold
        if ($newStatus === 'on_hold') {
            // Cannot change from done to on_hold if all tasks are complete
            if ($oldStatus === 'done' && $totalTasks > 0 && $doneTasks === $totalTasks) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat memindahkan dari Selesai ke Ditunda. Semua tugas sudah selesai. Ubah status tugas terlebih dahulu untuk mengubah status proyek."
                ]);
            }

            // Only update project status, tasks remain unchanged
            $project->update(['status' => $newStatus]);

            $latestLog = $project->statusLogs()->first();

            return response()->json([
                'success' => true,
                'changed' => true,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_at' => $latestLog?->created_at?->format('d M Y, H:i'),
                'changed_by' => $user->name,
            ]);
        }

        // Moving FROM on_hold
        if ($oldStatus === 'on_hold') {
            // Only update project status, tasks remain unchanged
            $project->update(['status' => $newStatus]);

            $latestLog = $project->statusLogs()->first();

            return response()->json([
                'success' => true,
                'changed' => true,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_at' => $latestLog?->created_at?->format('d M Y, H:i'),
                'changed_by' => $user->name,
            ]);
        }

        // ============ NORMAL STATUS LOGIC ============
        // Cannot move to 'done' if there are incomplete tasks
        if ($newStatus === 'done' && $incompleteTasks > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat memindahkan ke Selesai. Masih ada {$incompleteTasks} tugas yang belum selesai."
            ]);
        }

        // Cannot move FROM 'done' to 'in_progress' if all tasks are complete
        if ($oldStatus === 'done' && $newStatus === 'in_progress' && $totalTasks > 0 && $doneTasks === $totalTasks) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat memindahkan dari Selesai ke Sedang Berjalan. Semua tugas sudah selesai. Ubah status tugas terlebih dahulu untuk mengubah status proyek."
            ]);
        }

        // Cannot move FROM 'done' to 'on_hold' if all tasks are complete
        if ($oldStatus === 'done' && $newStatus === 'on_hold' && $totalTasks > 0 && $doneTasks === $totalTasks) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat memindahkan dari Selesai ke Ditunda. Semua tugas sudah selesai. Ubah status tugas terlebih dahulu untuk mengubah status proyek."
            ]);
        }

        // Cannot move to 'new' if there are non-todo tasks
        if ($newStatus === 'new' && $totalTasks > 0 && $todoTasks !== $totalTasks) {
            $nonTodoTasks = $totalTasks - $todoTasks;
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat memindahkan ke Baru. Ada {$nonTodoTasks} tugas yang sudah dikerjakan atau selesai."
            ]);
        }

        // Cannot move from 'new' to 'in_progress' if all tasks are still todo
        if ($newStatus === 'in_progress' && $totalTasks > 0 && $todoTasks === $totalTasks) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat memindahkan ke Berjalan. Mulai kerjakan minimal satu tugas terlebih dahulu."
            ]);
        }

        // Update status (this will trigger the boot() method to log the change)
        $project->update(['status' => $newStatus]);

        // Get the latest log for response
        $latestLog = $project->statusLogs()->first();

        return response()->json([
            'success' => true,
            'changed' => true,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_at' => $latestLog?->created_at?->format('d M Y, H:i'),
            'changed_by' => $user->name,
        ]);
    }

    /**
     * Toggle project between on_hold and previous status.
     * Only managers/admins can do this.
     * Note: Task status is NOT changed when project is put on hold.
     */
    public function toggleHold(\Illuminate\Http\Request $request, Project $project): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if user is manager/admin in project OR system admin
        if (!$user->isManagerInProject($project) && !$user->isAdmin()) {
            abort(403, 'Hanya manager atau admin yang dapat menunda/melanjutkan project.');
        }

        $currentStatus = $project->status->value;

        if ($currentStatus === 'on_hold') {
            // Resume project from hold - set to in_progress
            // Task status remains unchanged
            $project->update(['status' => 'in_progress']);

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Project dilanjutkan. Status berubah menjadi Sedang Berjalan.');
        } else {
            // Put project on hold
            // Task status remains unchanged (tasks keep their current status)
            $project->update(['status' => 'on_hold']);

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Project ditunda. Tugas tetap mempertahankan status masing-masing.');
        }
    }
}
