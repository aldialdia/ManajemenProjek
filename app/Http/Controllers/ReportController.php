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

    /**
     * Display reports for a specific project
     */
    public function index(Request $request, Project $project)
    {
        $user = auth()->user();
        $period = $request->get('period', '30'); // Default 30 days

        // Get date range for filtering
        [$startDate, $endDate] = $this->getDateRange($period);

        // ===== PROJECT-SPECIFIC STATS =====
        
        // All tasks in THIS PROJECT within period
        $totalTasks = Task::where('project_id', $project->id)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->count();
            
        $completedTasks = Task::where('project_id', $project->id)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        
        // Total hours from all team members in THIS PROJECT within period
        // Include completed entries
        $completedHours = \App\Models\TimeEntry::whereHas('task', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })
        ->whereBetween('started_at', [$startDate, $endDate])
        ->where('is_running', false)
        ->whereNotNull('ended_at')
        ->sum('duration_seconds') / 3600;
        
        // Add running timer elapsed time for this project
        $runningEntries = \App\Models\TimeEntry::whereHas('task', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })
        ->whereBetween('started_at', [$startDate, $endDate])
        ->where('is_running', true)
        ->get();
        $runningSeconds = $runningEntries->sum(fn($entry) => $entry->current_elapsed_seconds);
        
        $totalHours = round($completedHours + ($runningSeconds / 3600), 1);
        
        // Team members in THIS PROJECT
        $totalMembers = $project->users()->count();

        // ===== PERCENTAGE CHANGE CALCULATIONS =====
        $periodDays = $startDate->diffInDays($endDate);
        $prevEndDate = $startDate->copy()->subDay();
        $prevStartDate = $prevEndDate->copy()->subDays($periodDays);

        // Tasks completed change
        $tasksThisPeriod = Task::where('project_id', $project->id)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $tasksLastPeriod = Task::where('project_id', $project->id)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$prevStartDate, $prevEndDate])
            ->count();
        $taskChange = $tasksLastPeriod > 0 
            ? round((($tasksThisPeriod - $tasksLastPeriod) / $tasksLastPeriod) * 100) 
            : ($tasksThisPeriod > 0 ? 100 : 0);

        // Hours change (only completed entries for fair comparison)
        $hoursThisPeriod = \App\Models\TimeEntry::whereHas('task', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })->whereBetween('started_at', [$startDate, $endDate])
          ->where('is_running', false)
          ->whereNotNull('ended_at')
          ->sum('duration_seconds') / 3600;
        $hoursLastPeriod = \App\Models\TimeEntry::whereHas('task', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })->whereBetween('started_at', [$prevStartDate, $prevEndDate])
          ->where('is_running', false)
          ->whereNotNull('ended_at')
          ->sum('duration_seconds') / 3600;
        $hoursChange = $hoursLastPeriod > 0 
            ? round((($hoursThisPeriod - $hoursLastPeriod) / $hoursLastPeriod) * 100) 
            : ($hoursThisPeriod > 0 ? 100 : 0);

        // Member change (static, no change for now)
        $memberChange = 0;

        // ===== TASKS BY STATUS (for Status Tugas chart) =====
        $tasksByStatus = [
            'done' => Task::where('project_id', $project->id)->where('status', 'done')->count(),
            'in_progress' => Task::where('project_id', $project->id)->where('status', 'in_progress')->count(),
            'review' => Task::where('project_id', $project->id)->where('status', 'review')->count(),
            'todo' => Task::where('project_id', $project->id)->where('status', 'todo')->count(),
        ];

        // ===== TIME DISTRIBUTION BY TASK STATUS =====
        $timeByStatus = \App\Models\TimeEntry::selectRaw('
                tasks.status,
                SUM(time_entries.duration_seconds) as total_seconds
            ')
            ->join('tasks', 'time_entries.task_id', '=', 'tasks.id')
            ->where('tasks.project_id', $project->id)
            ->whereBetween('time_entries.started_at', [$startDate, $endDate])
            ->groupBy('tasks.status')
            ->get()
            ->pluck('total_seconds', 'status')
            ->toArray();

        $totalTimeSeconds = array_sum($timeByStatus) ?: 1;

        $timeDistribution = [
            'done' => round(($timeByStatus['done'] ?? 0) / $totalTimeSeconds * 100),
            'in_progress' => round(($timeByStatus['in_progress'] ?? 0) / $totalTimeSeconds * 100),
            'review' => round(($timeByStatus['review'] ?? 0) / $totalTimeSeconds * 100),
            'todo' => round(($timeByStatus['todo'] ?? 0) / $totalTimeSeconds * 100),
        ];

        // Tasks by user in THIS PROJECT
        $tasksByUser = $project->users()->withCount([
            'assignedTasks as completed_count' => function ($q) use ($project, $startDate, $endDate) {
                $q->where('status', 'done')
                  ->where('project_id', $project->id)
                  ->whereBetween('updated_at', [$startDate, $endDate]);
            },
            'assignedTasks as total_tasks_count' => function ($q) use ($project, $startDate, $endDate) {
                $q->where('project_id', $project->id)
                  ->where(function($subQ) use ($startDate, $endDate) {
                      $subQ->whereBetween('created_at', [$startDate, $endDate])
                           ->orWhereBetween('updated_at', [$startDate, $endDate]);
                  });
            }
        ])->get()->map(function ($user) {
            $user->completion_percentage = $user->total_tasks_count > 0 
                ? round(($user->completed_count / $user->total_tasks_count) * 100) 
                : 0;
            return $user;
        });

        // Recent activities - tasks from THIS PROJECT only
        $recentActivities = Task::where('project_id', $project->id)
            ->with(['assignee'])
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($task) {
                $statusLabel = match($task->status->value) {
                    'done' => 'Done',
                    'in_progress' => 'In Progress',
                    'review' => 'Review',
                    'todo' => 'To Do',
                    default => 'Unknown'
                };
                return [
                    'activity' => $task->title,
                    'user' => $task->assignee?->name ?? 'Unassigned',
                    'date' => $task->updated_at->format('d M Y'),
                    'time' => $task->updated_at->diffForHumans(),
                    'status' => $statusLabel,
                ];
            });

        return view('reports.index', compact(
            'project',
            'period',
            'totalTasks',
            'completedTasks',
            'totalHours',
            'totalMembers',
            'taskChange',
            'hoursChange',
            'memberChange',
            'tasksByStatus',
            'timeDistribution',
            'tasksByUser',
            'recentActivities'
        ));
    }
}
