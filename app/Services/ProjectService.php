<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Create a new project with optional team members.
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $users = $data['users'] ?? [];
            unset($data['users']);

            $project = Project::create($data);

            if (!empty($users)) {
                $this->syncUsers($project, $users);
            }

            return $project;
        });
    }

    /**
     * Update an existing project.
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $users = $data['users'] ?? null;
            unset($data['users']);

            $project->update($data);

            if ($users !== null) {
                $this->syncUsers($project, $users);
            }

            return $project->fresh();
        });
    }

    /**
     * Sync users to a project.
     */
    public function syncUsers(Project $project, array $userIds): void
    {
        $project->users()->sync($userIds);
    }

    /**
     * Assign users to a project with specific roles.
     */
    public function assignUsers(Project $project, array $assignments): void
    {
        // Format: ['user_id' => ['role' => 'manager']]
        $syncData = [];
        foreach ($assignments as $userId => $data) {
            $syncData[$userId] = ['role' => $data['role'] ?? 'member'];
        }

        $project->users()->sync($syncData);
    }

    /**
     * Add a single user to a project.
     */
    public function addUser(Project $project, int $userId, string $role = 'member'): void
    {
        $project->users()->attach($userId, ['role' => $role]);
    }

    /**
     * Remove a user from a project.
     */
    public function removeUser(Project $project, int $userId): void
    {
        $project->users()->detach($userId);
    }

    /**
     * Get project statistics.
     */
    public function getStatistics(Project $project): array
    {
        $tasks = $project->tasks;

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'pending_tasks' => $tasks->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'overdue_tasks' => $tasks->filter(fn($task) => $task->isOverdue())->count(),
            'progress' => $project->progress,
            'team_count' => $project->users->count(),
        ];
    }

    /**
     * Duplicate a project.
     */
    public function duplicate(Project $project, string $newName = null): Project
    {
        return DB::transaction(function () use ($project, $newName) {
            $newProject = $project->replicate();
            $newProject->name = $newName ?? $project->name . ' (Copy)';
            $newProject->status = 'active';
            $newProject->save();

            // Copy team members
            $newProject->users()->attach($project->users->pluck('id'));

            return $newProject;
        });
    }
}
