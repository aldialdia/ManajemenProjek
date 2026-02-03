<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TimeEntry;
use App\Exports\DashboardReportExport;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     * Super Admin sees system-wide overview, regular users see personal overview.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Check if user is super admin
        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return $this->userDashboard();
    }

    /**
     * Super Admin Dashboard - System-wide overview
     */
    private function superAdminDashboard(): View
    {
        // System-wide statistics
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'on_hold_projects' => Project::where('status', 'on_hold')->count(),
            'completed_projects' => Project::where('status', 'done')->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'pending_tasks' => Task::whereIn('status', ['todo', 'in_progress'])->count(),
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
        ];

        // Project status distribution
        $projectsByStatus = [
            'new' => Project::where('status', 'new')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'done' => Project::where('status', 'done')->count(),
        ];

        // Task status distribution
        $tasksByStatus = [
            'todo' => Task::where('status', 'todo')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'review' => Task::where('status', 'review')->count(),
            'done' => Task::where('status', 'done')->count(),
        ];

        // Recent projects (all system projects)
        $recentProjects = Project::with(['users'])
            ->latest()
            ->take(8)
            ->get();

        // Projects with issues (overdue or on hold)
        $projectsWithIssues = Project::where(function ($query) {
            $query->where('status', 'on_hold')
                ->orWhere(function ($q) {
                    $q->where('end_date', '<', now())
                        ->where('status', '!=', 'done');
                });
        })
            ->with(['users'])
            ->take(5)
            ->get();

        // Top active users (by time entries this month)
        // First try to get users with time entries this month
        $topUsers = User::select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.status', 'users.role')
            ->join('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->whereMonth('time_entries.started_at', now()->month)
            ->whereYear('time_entries.started_at', now()->year)
            ->where('time_entries.is_running', false)
            ->whereNotNull('time_entries.ended_at')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar', 'users.status', 'users.role')
            ->selectRaw('SUM(time_entries.duration_seconds) as total_seconds')
            ->orderByDesc('total_seconds')
            ->take(5)
            ->get();

        // If less than 5 users have time entries, fill with most active users overall
        if ($topUsers->count() < 5) {
            $excludeIds = $topUsers->pluck('id')->toArray();
            $additionalUsers = User::select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.status', 'users.role')
                ->leftJoin('time_entries', 'users.id', '=', 'time_entries.user_id')
                ->whereNotIn('users.id', $excludeIds)
                ->where('users.status', 'active')
                ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar', 'users.status', 'users.role')
                ->selectRaw('COALESCE(SUM(time_entries.duration_seconds), 0) as total_seconds')
                ->orderByDesc('total_seconds')
                ->take(5 - $topUsers->count())
                ->get();

            $topUsers = $topUsers->merge($additionalUsers);
        }

        // Monthly trends (last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyTrends[] = [
                'month' => $date->locale('id')->isoFormat('MMM'),
                'projects' => Project::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'tasks' => Task::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }

        // System health metrics
        $systemHealth = [
            'overdue_tasks' => Task::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
            'overdue_projects' => Project::whereNotNull('end_date')
                ->where('end_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
            'unassigned_tasks' => Task::doesntHave('assignees')->count(),
        ];

        // Project type distribution (RBB vs Non-RBB)
        $projectsByType = [
            'rbb' => Project::where('type', 'rbb')->count(),
            'non_rbb' => Project::where('type', 'non_rbb')->count(),
        ];

        // Task distribution per member (top 10 members with most tasks) with project info
        $taskDistribution = User::select('users.id', 'users.name')
            ->join('task_user', 'users.id', '=', 'task_user.user_id')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status = "done" THEN 1 ELSE 0 END) as completed_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status != "done" THEN 1 ELSE 0 END) as pending_tasks')
            ->orderByDesc('total_tasks')
            ->take(10)
            ->get()
            ->map(function ($user) {
                // Get tasks with project info for each user
                $user->tasks_with_projects = \DB::table('tasks')
                    ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
                    ->join('projects', 'tasks.project_id', '=', 'projects.id')
                    ->where('task_user.user_id', $user->id)
                    ->select('tasks.id', 'tasks.title', 'tasks.status', 'projects.name as project_name', 'projects.id as project_id')
                    ->orderBy('tasks.created_at', 'desc')
                    ->take(3) // Show only 3 recent tasks per user
                    ->get();
                return $user;
            });

        // All task distribution (for View All modal)
        $allTaskDistribution = User::select('users.id', 'users.name')
            ->join('task_user', 'users.id', '=', 'task_user.user_id')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status = "done" THEN 1 ELSE 0 END) as completed_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status != "done" THEN 1 ELSE 0 END) as pending_tasks')
            ->orderByDesc('total_tasks')
            ->get()
            ->map(function ($user) {
                // Get all tasks with project info for each user
                $user->tasks_with_projects = \DB::table('tasks')
                    ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
                    ->join('projects', 'tasks.project_id', '=', 'projects.id')
                    ->where('task_user.user_id', $user->id)
                    ->select('tasks.id', 'tasks.title', 'tasks.status', 'projects.name as project_name', 'projects.id as project_id')
                    ->orderBy('tasks.created_at', 'desc')
                    ->get();
                return $user;
            });

        return view('dashboard-super-admin', compact(
            'stats',
            'projectsByStatus',
            'tasksByStatus',
            'projectsWithIssues',
            'topUsers',
            'monthlyTrends',
            'systemHealth',
            'projectsByType',
            'taskDistribution',
            'allTaskDistribution'
        ));
    }

    /**
     * Regular User Dashboard - Personal overview
     */
    private function userDashboard(): View
    {
        $user = auth()->user();

        // Get only projects where user is registered
        $userProjectIds = $user->projects()->pluck('projects.id');

        $stats = [
            'total_projects' => $user->projects()->count(),
            'active_projects' => $user->projects()->where('status', 'in_progress')->count(),
            'total_tasks' => Task::whereIn('project_id', $userProjectIds)->count(),
            'completed_tasks' => Task::whereIn('project_id', $userProjectIds)->where('status', 'done')->count(),
            'pending_tasks' => Task::whereIn('project_id', $userProjectIds)->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'total_users' => User::whereHas('projects', function ($q) use ($userProjectIds) {
                $q->whereIn('projects.id', $userProjectIds);
            })->count(),
        ];

        // Only show recent projects where user is registered
        $recentProjects = $user->projects()
            ->latest()
            ->take(5)
            ->get();

        $myTasks = auth()->check()
            ? Task::whereHas('assignees', fn($q) => $q->where('user_id', auth()->id()))
                ->whereNot('status', 'done')
                ->with('project')
                ->orderBy('due_date')
                ->take(10)
                ->get()
            : collect();

        // Only show upcoming deadlines from user's projects
        $upcomingDeadlines = Task::whereIn('project_id', $userProjectIds)
            ->whereNotNull('due_date')
            ->whereNot('status', 'done')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['project', 'assignees'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Project type distribution (RBB vs Non-RBB) - only user's projects
        $projectsByType = [
            'rbb' => Project::whereIn('id', $userProjectIds)->where('type', 'rbb')->count(),
            'non_rbb' => Project::whereIn('id', $userProjectIds)->where('type', 'non_rbb')->count(),
        ];


        return view('dashboard', compact('stats', 'recentProjects', 'myTasks', 'upcomingDeadlines', 'projectsByType'));
    }

    /**
     * Export Dashboard Report to Excel (Super Admin Only)
     */
    public function exportDashboardReport()
    {
        // Check if user is super admin
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all the data needed for the report
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'on_hold_projects' => Project::where('status', 'on_hold')->count(),
            'completed_projects' => Project::where('status', 'done')->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'pending_tasks' => Task::whereIn('status', ['todo', 'in_progress'])->count(),
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_hours_this_month' => TimeEntry::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at) / 3600')),
        ];

        // Project type distribution
        $projectsByType = [
            'rbb' => Project::where('type', 'rbb')->count(),
            'non_rbb' => Project::where('type', 'non_rbb')->count(),
        ];

        // Task distribution per member
        $taskDistribution = User::select('users.id', 'users.name', 'users.email', 'users.role', 'users.status', 'users.created_at', 'users.updated_at', 'users.email_verified_at')
            ->selectRaw('COUNT(DISTINCT task_user.task_id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT CASE WHEN tasks.status = "done" THEN task_user.task_id END) as completed_tasks')
            ->selectRaw('COUNT(DISTINCT CASE WHEN tasks.status != "done" THEN task_user.task_id END) as pending_tasks')
            ->leftJoin('task_user', 'users.id', '=', 'task_user.user_id')
            ->leftJoin('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role', 'users.status', 'users.created_at', 'users.updated_at', 'users.email_verified_at')
            ->orderByDesc('total_tasks')
            ->get();

        // Projects with issues
        $projectsWithIssues = Project::where(function ($query) {
            $query->where('status', 'on_hold')
                ->orWhere(function ($q) {
                    $q->where('end_date', '<', now())
                        ->where('status', '!=', 'done');
                });
        })->get();

        // Generate filename with current date
        $filename = 'Dashboard_Report_' . now()->format('Y-m-d_His') . '.xlsx';

        // Export to Excel
        return Excel::download(
            new DashboardReportExport($stats, $projectsByType, $taskDistribution, $projectsWithIssues),
            $filename
        );
    }
}

