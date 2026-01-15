<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get date range based on period filter
     */
    private function getDateRange(string $period): array
    {
        $endDate = Carbon::now()->endOfDay();
        
        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                break;
            case '7':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                break;
            case '30':
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                break;
            default:
                $startDate = Carbon::now()->subDays(30)->startOfDay();
        }
        
        return [$startDate, $endDate];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userProjectIds = $user->projects()->pluck('projects.id')->toArray();
        
        $projectId = $request->get('project_id');
        $project = $projectId ? Project::find($projectId) : null;
        $period = $request->get('period', '30'); // Default 30 days

        // Get date range for filtering
        [$startDate, $endDate] = $this->getDateRange($period);

        // ===== PROJECT-WIDE STATS (all data from user's projects) =====
        $totalProjects = count($userProjectIds);
        
        // All tasks in user's projects within period (based on created_at or updated_at)
        $totalTasks = Task::whereIn('project_id', $userProjectIds)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->count();
            
        $completedTasks = Task::whereIn('project_id', $userProjectIds)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        
        // Total hours from all team members in user's projects within period
        $totalHours = round(\App\Models\TimeEntry::whereHas('task', function($q) use ($userProjectIds) {
            $q->whereIn('project_id', $userProjectIds);
        })
        ->whereBetween('started_at', [$startDate, $endDate])
        ->sum('duration_seconds') / 3600, 1);
        
        // Team members in user's projects (not filtered by period - this is a static count)
        $totalMembers = User::whereHas('projects', function($q) use ($userProjectIds) {
            $q->whereIn('projects.id', $userProjectIds);
        })->count();

        // ===== PERCENTAGE CHANGE CALCULATIONS (comparing current period vs previous period) =====
        $periodDays = $startDate->diffInDays($endDate);
        $prevEndDate = $startDate->copy()->subDay();
        $prevStartDate = $prevEndDate->copy()->subDays($periodDays);

        // Projects change
        $projectsThisPeriod = Project::whereIn('id', $userProjectIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $projectsLastPeriod = Project::whereIn('id', $userProjectIds)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();
        $projectChange = $projectsLastPeriod > 0 
            ? round((($projectsThisPeriod - $projectsLastPeriod) / $projectsLastPeriod) * 100) 
            : ($projectsThisPeriod > 0 ? 100 : 0);

        // Tasks completed change (all tasks in user's projects)
        $tasksThisPeriod = Task::whereIn('project_id', $userProjectIds)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $tasksLastPeriod = Task::whereIn('project_id', $userProjectIds)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$prevStartDate, $prevEndDate])
            ->count();
        $taskChange = $tasksLastPeriod > 0 
            ? round((($tasksThisPeriod - $tasksLastPeriod) / $tasksLastPeriod) * 100) 
            : ($tasksThisPeriod > 0 ? 100 : 0);

        // Hours change (all hours in user's projects)
        $hoursThisPeriod = \App\Models\TimeEntry::whereHas('task', function($q) use ($userProjectIds) {
            $q->whereIn('project_id', $userProjectIds);
        })->whereBetween('started_at', [$startDate, $endDate])
          ->sum('duration_seconds') / 3600;
        $hoursLastPeriod = \App\Models\TimeEntry::whereHas('task', function($q) use ($userProjectIds) {
            $q->whereIn('project_id', $userProjectIds);
        })->whereBetween('started_at', [$prevStartDate, $prevEndDate])
          ->sum('duration_seconds') / 3600;
        $hoursChange = $hoursLastPeriod > 0 
            ? round((($hoursThisPeriod - $hoursLastPeriod) / $hoursLastPeriod) * 100) 
            : ($hoursThisPeriod > 0 ? 100 : 0);

        // Member change
        $memberChange = 0;

        // Project status distribution (user's projects only - not filtered by period as it shows current state)
        $projectsByStatus = [
            'completed' => Project::whereIn('id', $userProjectIds)->where('status', 'completed')->count(),
            'active' => Project::whereIn('id', $userProjectIds)->where('status', 'active')->count(),
            'on_hold' => Project::whereIn('id', $userProjectIds)->where('status', 'on_hold')->count(),
            'cancelled' => Project::whereIn('id', $userProjectIds)->where('status', 'cancelled')->count(),
        ];

        // ===== TIME DISTRIBUTION BY TASK STATUS (filtered by period) =====
        $timeByStatus = \App\Models\TimeEntry::selectRaw('
                tasks.status,
                SUM(time_entries.duration_seconds) as total_seconds
            ')
            ->join('tasks', 'time_entries.task_id', '=', 'tasks.id')
            ->whereIn('tasks.project_id', $userProjectIds)
            ->whereBetween('time_entries.started_at', [$startDate, $endDate])
            ->when($project, function($q) use ($project) {
                $q->where('tasks.project_id', $project->id);
            })
            ->groupBy('tasks.status')
            ->get()
            ->pluck('total_seconds', 'status')
            ->toArray();

        $totalTimeSeconds = array_sum($timeByStatus) ?: 1; // Avoid division by zero

        $timeDistribution = [
            'done' => round(($timeByStatus['done'] ?? 0) / $totalTimeSeconds * 100),
            'in_progress' => round(($timeByStatus['in_progress'] ?? 0) / $totalTimeSeconds * 100),
            'review' => round(($timeByStatus['review'] ?? 0) / $totalTimeSeconds * 100),
            'todo' => round(($timeByStatus['todo'] ?? 0) / $totalTimeSeconds * 100),
        ];

        // Tasks by user - filtered by period
        $tasksByUser = User::whereHas('projects', function($q) use ($userProjectIds) {
            $q->whereIn('projects.id', $userProjectIds);
        })->withCount([
            'assignedTasks as completed_count' => function ($q) use ($userProjectIds, $startDate, $endDate) {
                $q->where('status', 'done')
                  ->whereIn('project_id', $userProjectIds)
                  ->whereBetween('updated_at', [$startDate, $endDate]);
            },
            'assignedTasks as pending_count' => function ($q) use ($userProjectIds, $startDate, $endDate) {
                $q->where('status', '!=', 'done')
                  ->whereIn('project_id', $userProjectIds)
                  ->where(function($subQ) use ($startDate, $endDate) {
                      $subQ->whereBetween('created_at', [$startDate, $endDate])
                           ->orWhereBetween('updated_at', [$startDate, $endDate]);
                  });
            }
        ])->take(10)->get();

        // Recent activities - all tasks from user's projects within period
        $recentActivities = Task::whereIn('project_id', $userProjectIds)
            ->with(['project', 'assignee'])
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($task) {
                $statusLabel = match($task->status->value) {
                    'done' => 'Selesai',
                    'in_progress' => 'Dikerjakan',
                    'review' => 'Review',
                    'todo' => 'Pending',
                    default => 'Unknown'
                };
                return [
                    'project' => $task->project?->name ?? 'No Project',
                    'activity' => $task->title,
                    'user' => $task->assignee?->name ?? 'Unassigned',
                    'time' => $task->updated_at->diffForHumans(),
                    'status' => $statusLabel,
                ];
            });

        return view('reports.index', compact(
            'project',
            'period',
            'totalProjects',
            'totalTasks',
            'completedTasks',
            'totalHours',
            'totalMembers',
            'projectChange',
            'taskChange',
            'hoursChange',
            'memberChange',
            'projectsByStatus',
            'timeDistribution',
            'tasksByUser',
            'recentActivities'
        ));
    }
}
