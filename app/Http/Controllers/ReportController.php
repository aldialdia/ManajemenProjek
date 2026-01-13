<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $project = $projectId ? Project::find($projectId) : null;

        // Base queries
        $projectsQuery = Project::query();
        $tasksQuery = Task::query();

        if ($project) {
            $tasksQuery->where('project_id', $project->id);
        }

        // Stats
        $totalProjects = $projectsQuery->count();
        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->where('status', 'done')->count();
        $totalHours = 186; // Placeholder
        $totalMembers = $project ? $project->users()->count() : User::count();

        // Project status distribution
        $projectsByStatus = [
            'completed' => Project::where('status', 'completed')->count(),
            'active' => Project::where('status', 'active')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'cancelled' => Project::where('status', 'cancelled')->count(),
        ];

        // Time distribution (placeholder data)
        $timeDistribution = [
            'development' => 40,
            'meetings' => 20,
            'review' => 15,
            'planning' => 15,
            'admin' => 10,
        ];

        // Tasks by user
        $tasksByUser = User::withCount([
            'assignedTasks as completed_count' => function ($q) use ($tasksQuery) {
                $q->where('status', 'done');
            },
            'assignedTasks as pending_count' => function ($q) {
                $q->where('status', '!=', 'done');
            }
        ])->take(5)->get();

        // Recent activities
        $recentActivities = Task::with(['project'])
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($task) {
                return [
                    'project' => $task->project?->name ?? 'No Project',
                    'activity' => 'Task completed: "' . $task->title . '"',
                    'time' => $task->updated_at->diffForHumans(),
                    'status' => $task->status->value === 'done' ? 'Selesai' : 'Dalam Proses',
                ];
            });

        return view('reports.index', compact(
            'project',
            'totalProjects',
            'totalTasks',
            'completedTasks',
            'totalHours',
            'totalMembers',
            'projectsByStatus',
            'timeDistribution',
            'tasksByUser',
            'recentActivities'
        ));
    }
}
