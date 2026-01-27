<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index(): View
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
