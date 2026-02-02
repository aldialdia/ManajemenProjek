<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TimeEntry;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
            'total_clients' => Client::count(),
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
        $recentProjects = Project::with(['client', 'users'])
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

        // Task distribution per member (top 10 members with most tasks)
        $taskDistribution = User::select('users.id', 'users.name')
            ->join('task_user', 'users.id', '=', 'task_user.user_id')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status = "done" THEN 1 ELSE 0 END) as completed_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.status != "done" THEN 1 ELSE 0 END) as pending_tasks')
            ->orderByDesc('total_tasks')
            ->take(10)
            ->get();

        return view('dashboard-super-admin', compact(
            'stats',
            'projectsByStatus',
            'tasksByStatus',
            'recentProjects',
            'projectsWithIssues',
            'topUsers',
            'monthlyTrends',
            'systemHealth',
            'projectsByType',
            'taskDistribution'
        ));
    }

    /**
     * Regular User Dashboard - Personal overview
     */
    private function userDashboard(): View
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'pending_tasks' => Task::whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'total_clients' => Client::count(),
            'total_users' => User::count(),
        ];

        $recentProjects = Project::with('client')
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

        $upcomingDeadlines = Task::whereNotNull('due_date')
            ->whereNot('status', 'done')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['project', 'assignees'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentProjects', 'myTasks', 'upcomingDeadlines'));
    }
}
