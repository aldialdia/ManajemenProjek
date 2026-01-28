@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $userProjectIds = $user->projects()->pluck('projects.id')->toArray();

        // Only managers/admins can create projects
        // Check if user is system admin OR has manager/admin role in any project
        $canCreateProject = $user->isAdmin() || $user->projects()
            ->wherePivotIn('role', ['manager', 'admin'])
            ->exists();

        // User-specific stats
        $totalProjects = count($userProjectIds);
        $activeProjects = \App\Models\Project::whereIn('id', $userProjectIds)->where('status', 'in_progress')->count();
        $totalTasks = \App\Models\Task::where('assigned_to', $user->id)->count();
        $completedTasks = \App\Models\Task::where('assigned_to', $user->id)->where('status', 'done')->count();
        $pendingTasks = \App\Models\Task::where('assigned_to', $user->id)->whereIn('status', ['todo', 'in_progress'])->count();

        // Team members in user's projects
        $totalTeamMembers = \App\Models\User::whereHas('projects', function ($q) use ($userProjectIds) {
            $q->whereIn('projects.id', $userProjectIds);
        })->count();

        // Calculate actual work hours from TimeEntry (user's own hours)
        // Include completed entries + currently running timers
        $completedHoursThisMonth = \App\Models\TimeEntry::where('user_id', $user->id)
            ->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->where('is_running', false)
            ->whereNotNull('ended_at')
            ->sum('duration_seconds') / 3600;

        // Add running timer elapsed time
        $runningEntryThisMonth = \App\Models\TimeEntry::where('user_id', $user->id)
            ->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->where('is_running', true)
            ->first();
        $runningSecondsThisMonth = $runningEntryThisMonth ? $runningEntryThisMonth->current_elapsed_seconds : 0;
        $totalHoursThisMonth = $completedHoursThisMonth + ($runningSecondsThisMonth / 3600);

        // Last month (only completed, no running timers from last month)
        $totalHoursLastMonth = \App\Models\TimeEntry::where('user_id', $user->id)
            ->whereMonth('started_at', now()->subMonth()->month)
            ->whereYear('started_at', now()->subMonth()->year)
            ->where('is_running', false)
            ->whereNotNull('ended_at')
            ->sum('duration_seconds') / 3600;

        // Calculate percentage changes (user-specific)
        $projectsLastMonth = \App\Models\Project::whereIn('id', $userProjectIds)
            ->whereMonth('created_at', now()->subMonth()->month)->count();
        $projectsThisMonth = \App\Models\Project::whereIn('id', $userProjectIds)
            ->whereMonth('created_at', now()->month)->count();
        $projectChange = $projectsLastMonth > 0 ? round((($projectsThisMonth - $projectsLastMonth) / $projectsLastMonth) * 100) : ($projectsThisMonth > 0 ? 100 : 0);

        $tasksCompletedLastMonth = \App\Models\Task::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->whereMonth('updated_at', now()->subMonth()->month)->count();
        $tasksCompletedThisMonth = \App\Models\Task::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->whereMonth('updated_at', now()->month)->count();
        $taskChange = $tasksCompletedLastMonth > 0 ? round((($tasksCompletedThisMonth - $tasksCompletedLastMonth) / $tasksCompletedLastMonth) * 100) : ($tasksCompletedThisMonth > 0 ? 100 : 0);

        $hoursChange = $totalHoursLastMonth > 0 ? round((($totalHoursThisMonth - $totalHoursLastMonth) / $totalHoursLastMonth) * 100) : ($totalHoursThisMonth > 0 ? 100 : 0);

        // Tasks by status for pie chart (user's tasks only)
        $tasksByStatus = [
            'todo' => \App\Models\Task::where('assigned_to', $user->id)->where('status', 'todo')->count(),
            'in_progress' => \App\Models\Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'review' => \App\Models\Task::where('assigned_to', $user->id)->where('status', 'review')->count(),
            'done' => \App\Models\Task::where('assigned_to', $user->id)->where('status', 'done')->count(),
        ];

        // Weekly productivity (last 7 days) - User's data
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $hoursOnDay = \App\Models\TimeEntry::where('user_id', $user->id)
                ->whereDate('started_at', $date->toDateString())
                ->sum('duration_seconds') / 3600;
            $weeklyData[] = [
                'day' => $date->locale('id')->isoFormat('ddd'),
                'hours' => round($hoursOnDay, 1),
                'completed' => \App\Models\Task::where('assigned_to', $user->id)
                    ->where('status', 'done')
                    ->whereDate('updated_at', $date->toDateString())->count(),
            ];
        }

        // Active projects (user's projects only)
        $activeProjectsList = \App\Models\Project::whereIn('id', $userProjectIds)
            ->where('status', 'in_progress')
            ->with(['tasks'])
            ->latest()
            ->take(5)
            ->get();

        // Upcoming deadlines (user's tasks only) - within 7 days
        $upcomingDeadlines = \App\Models\Task::where('assigned_to', $user->id)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->with(['project'])
            ->take(5)
            ->get();
    @endphp

    <!-- Welcome Header -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div class="welcome-badge">
                <i class="fas fa-chart-line"></i>
                Dashboard Overview
            </div>
            <h1 class="welcome-title">Selamat Datang Kembali, {{ auth()->user()->name ?? 'Guest' }}!</h1>
            <p class="welcome-subtitle">Berikut adalah ringkasan aktivitas proyek Anda hari ini.</p>
        </div>
        <div class="welcome-actions">
            <a href="{{ route('projects.index') }}" class="btn btn-welcome">
                <i class="fas fa-folder-open"></i>
                Semua Proyek
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card-dashboard stat-blue">
            <div class="stat-icon-circle">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Proyek</span>
                <span class="stat-value">{{ $totalProjects }}</span>
                @if($projectChange != 0)
                    <span class="stat-change {{ $projectChange >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ $projectChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ $projectChange >= 0 ? '+' : '' }}{{ $projectChange }}%
                    </span>
                @endif
            </div>
            <span class="stat-note">{{ $activeProjects }} proyek aktif</span>
        </div>

        <div class="stat-card-dashboard stat-green">
            <div class="stat-icon-circle">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Tugas Selesai</span>
                <span class="stat-value">{{ $completedTasks }}</span>
                @if($taskChange != 0)
                    <span class="stat-change {{ $taskChange >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ $taskChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ $taskChange >= 0 ? '+' : '' }}{{ $taskChange }}%
                    </span>
                @endif
            </div>
            <span class="stat-note">{{ $pendingTasks }} tugas tertunda</span>
        </div>

        <div class="stat-card-dashboard stat-purple">
            <div class="stat-icon-circle">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Jam Kerja</span>
                <span class="stat-value">{{ number_format($totalHoursThisMonth, 0) }}h</span>
                @if($hoursChange != 0)
                    <span class="stat-change {{ $hoursChange >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ $hoursChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ $hoursChange >= 0 ? '+' : '' }}{{ $hoursChange }}%
                    </span>
                @endif
            </div>
            <span class="stat-note">Bulan ini</span>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Weekly Productivity Chart -->
        <div class="card">
            <div class="card-header">
                <span>Produktivitas Mingguan</span>
                <span class="text-muted text-sm">Jam kerja dan tugas selesai</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 250px;">
                    <canvas id="productivityChart"></canvas>
                </div>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-dot" style="background: #3b82f6;"></span> Jam Kerja</span>
                    <span class="legend-item"><span class="legend-dot" style="background: #22c55e;"></span> Tugas
                        Selesai</span>
                </div>
            </div>
        </div>

        <!-- Task Distribution Chart -->
        <div class="card">
            <div class="card-header">
                <span>Distribusi Tugas</span>
                <span class="text-muted text-sm">Total: {{ $totalTasks }} tugas</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 250px;">
                    <canvas id="taskDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Projects & Deadlines Row (Side by Side) -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Active Projects (Compact) -->
        <div class="card">
            <div class="card-header" style="padding: 0.75rem 1rem;">
                <span style="font-size: 0.9rem;"><i class="fas fa-rocket text-primary"></i> Proyek Aktif</span>
            </div>
            <div class="card-body" style="padding: 0; max-height: 280px; overflow-y: auto;">
                @forelse($activeProjectsList as $project)
                    <div class="project-row-compact">
                        <div class="project-row-info">
                            <a href="{{ route('projects.show', $project) }}" class="project-row-title">{{ Str::limit($project->name, 25) }}</a>
                            <span class="project-row-date">
                                {{ $project->end_date?->format('d M') ?? '-' }}
                            </span>
                        </div>
                        <div class="project-row-progress">
                            <div class="mini-progress" style="width: 50px;">
                                <div class="mini-progress-fill" style="width: {{ $project->progress }}%;"></div>
                            </div>
                            <span class="progress-text">{{ $project->progress }}%</span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 1.5rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                        <i class="fas fa-folder-open"></i> Belum ada proyek aktif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Deadlines (Compact) -->
        <div class="card">
            <div class="card-header" style="padding: 0.75rem 1rem;">
                <span style="font-size: 0.9rem;"><i class="fas fa-exclamation-triangle text-warning"></i> Deadline Terdekat</span>
            </div>
            <div class="card-body" style="padding: 0; max-height: 280px; overflow-y: auto;">
                @forelse($upcomingDeadlines as $task)
                    <div class="deadline-row-compact">
                        <div class="deadline-priority-dot {{ $task->priority->value === 'high' ? 'priority-high' : ($task->priority->value === 'medium' ? 'priority-medium' : 'priority-low') }}"></div>
                        <div class="deadline-info" style="flex: 1; min-width: 0;">
                            <a href="{{ route('tasks.show', $task) }}" class="deadline-title">{{ Str::limit($task->title, 30) }}</a>
                        </div>
                        <span class="deadline-remaining-badge {{ $task->due_date->isToday() ? 'urgent' : '' }}">
                            @php $daysLeft = now()->startOfDay()->diffInDays($task->due_date->startOfDay()); @endphp
                            @if($task->due_date->isToday()) Hari ini @elseif($daysLeft == 1) Besok @else {{ $daysLeft }}h @endif
                        </span>
                    </div>
                @empty
                    <div style="padding: 1.5rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i> Tidak ada deadline 🎉
                    </div>
                @endforelse
            </div>
        </div>
    </div>


    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Disable global animation to prevent infinite loops
        Chart.defaults.animation = false;

        // Weekly Productivity Chart
        const productivityCtx = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(productivityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($weeklyData, 'day')) !!},
                datasets: [{
                    label: 'Jam Kerja',
                    data: {!! json_encode(array_column($weeklyData, 'hours')) !!},
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Tugas Selesai',
                    data: {!! json_encode(array_column($weeklyData, 'completed')) !!},
                    borderColor: '#22c55e',
                    borderWidth: 3,
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 15,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { stepSize: 3 }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Task Distribution Pie Chart
        const taskDistCtx = document.getElementById('taskDistributionChart').getContext('2d');
        const taskDistChart = new Chart(taskDistCtx, {
            type: 'doughnut',
            data: {
                labels: ['Done', 'In Progress', 'Review', 'To Do'],
                datasets: [{
                    data: [
                                            {{ $tasksByStatus['done'] }},
                                            {{ $tasksByStatus['in_progress'] }},
                                            {{ $tasksByStatus['review'] }},
                        {{ $tasksByStatus['todo'] }}
                    ],
                    backgroundColor: ['#10b981', '#3b82f6', '#f97316', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    </script>

    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #7c3aed 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .welcome-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .btn-welcome {
            background: white;
            color: #6366f1;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-welcome:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .welcome-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-kanban-small {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-kanban-small:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card-dashboard {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
        }

        .stat-blue .stat-icon-circle {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .stat-green .stat-icon-circle {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .stat-purple .stat-icon-circle {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .stat-orange .stat-icon-circle {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .stat-info {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            width: 100%;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-change {
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: #22c55e;
        }

        .stat-note {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .project-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .project-row:last-child {
            border-bottom: none;
        }

        .project-row-title {
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
        }

        .project-row-title:hover {
            color: var(--primary);
        }

        .project-row-date {
            font-size: 0.75rem;
            color: #94a3b8;
            display: block;
            margin-top: 0.25rem;
        }

        .project-row-progress {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .mini-progress {
            width: 80px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .mini-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 999px;
        }

        .progress-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            min-width: 40px;
        }

        .deadline-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .deadline-row:last-child {
            border-bottom: none;
        }

        .deadline-priority {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }

        .priority-high {
            background: #fef2f2;
            color: #ef4444;
        }

        .priority-medium {
            background: #fff7ed;
            color: #f97316;
        }

        .priority-low {
            background: #f0fdf4;
            color: #22c55e;
        }

        .deadline-info {
            flex: 1;
        }

        .deadline-title {
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
            display: block;
        }

        .deadline-title:hover {
            color: var(--primary);
        }

        .deadline-project {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .deadline-meta {
            text-align: right;
        }

        .deadline-date,
        .deadline-remaining {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
        }

        .deadline-remaining {
            color: #f97316;
        }

        .badge-primary {
            background: #fef2f2;
            color: #ef4444;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-outline:hover {
            background: #f1f5f9;
        }

        /* Kanban Board Styles */
        .kanban-board {
            display: flex;
            gap: 1rem;
            min-height: 400px;
            padding-bottom: 1rem;
        }

        .kanban-column {
            flex: 1;
            min-width: 240px;
            max-width: 280px;
            background: #f8fafc;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-radius: 16px 16px 0 0;
            font-weight: 600;
            color: white;
        }

        .kanban-column-header.new {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        .kanban-column-header.in_progress {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .kanban-column-header.review {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .kanban-column-header.done {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .column-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }

        .kanban-column-body {
            flex: 1;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-height: 100px;
            transition: background 0.2s;
        }

        .kanban-column-body.drag-over {
            background: rgba(99, 102, 241, 0.1);
            border: 2px dashed #6366f1;
            border-radius: 0 0 12px 12px;
        }

        .kanban-card {
            background: white;
            border-radius: 10px;
            padding: 0.875rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .kanban-card.draggable {
            cursor: grab;
        }

        .kanban-card.draggable:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .kanban-card.dragging {
            opacity: 0.5;
            transform: rotate(3deg);
            cursor: grabbing;
        }

        .kanban-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .kanban-card-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            text-decoration: none;
            line-height: 1.3;
        }

        .kanban-card-title:hover {
            color: #6366f1;
        }

        .kanban-card-lock {
            color: #94a3b8;
            font-size: 0.7rem;
        }

        .kanban-card-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .progress-bar-mini {
            flex: 1;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar-mini .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 2px;
            transition: width 0.3s;
        }

        .progress-text {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 500;
        }

        .kanban-card-meta {
            display: flex;
            gap: 0.75rem;
            font-size: 0.7rem;
            color: #64748b;
        }

        .kanban-card-meta i {
            margin-right: 0.25rem;
        }

        .kanban-card-status-date {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.65rem;
            color: #94a3b8;
        }

        .kanban-card-status-date i {
            margin-right: 0.25rem;
        }

        .status-changed-by {
            font-style: italic;
        }

        .kanban-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .kanban-empty i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        /* Compact Project Row Styles */
        .project-row-compact {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .project-row-compact:last-child {
            border-bottom: none;
        }

        .project-row-compact:hover {
            background: #f8fafc;
        }

        .project-row-compact .project-row-title {
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            text-decoration: none;
        }

        .project-row-compact .project-row-title:hover {
            color: #6366f1;
        }

        .project-row-compact .project-row-date {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        /* Compact Deadline Row Styles */
        .deadline-row-compact {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .deadline-row-compact:last-child {
            border-bottom: none;
        }

        .deadline-row-compact:hover {
            background: #f8fafc;
        }

        .deadline-priority-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .deadline-priority-dot.priority-high {
            background: #ef4444;
        }

        .deadline-priority-dot.priority-medium {
            background: #f59e0b;
        }

        .deadline-priority-dot.priority-low {
            background: #10b981;
        }

        .deadline-row-compact .deadline-title {
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .deadline-row-compact .deadline-title:hover {
            color: #6366f1;
        }

        .deadline-remaining-badge {
            font-size: 0.7rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .deadline-remaining-badge.urgent {
            background: #fef2f2;
            color: #dc2626;
            font-weight: 600;
        }

        /* Kanban Link Card Styles */
        .kanban-link-card {
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.2s;
        }

        .kanban-link-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .kanban-link-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            text-decoration: none;
            color: inherit;
        }

        .kanban-link-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .kanban-link-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
        }

        .kanban-link-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
        }

        .kanban-status-badges {
            display: flex;
            gap: 0.5rem;
        }

        .kanban-mini-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* My Projects Section Styles */
        .my-projects-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .section-title i {
            color: #6366f1;
        }

        .projects-count {
            font-size: 0.8rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }

        /* Empty State */
        .empty-projects-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }

        .empty-projects-state .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .empty-projects-state .empty-icon i {
            font-size: 2rem;
            color: #94a3b8;
        }

        .empty-projects-state h3 {
            font-size: 1.125rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .empty-projects-state p {
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }

        /* Year Groups */
        .year-group {
            margin-bottom: 1.5rem;
        }

        .year-group:last-child {
            margin-bottom: 0;
        }

        .year-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }


        /* Projects Grid */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        /* Project Card Item */
        .project-card-item {
            display: block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .project-card-item:hover {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }

        .project-card-header {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .project-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 4px;
        }

        .project-card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

        .project-card-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .project-status-badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .project-status-badge.new {
            background: #f1f5f9;
            color: #64748b;
        }

        .project-status-badge.in_progress {
            background: #dbeafe;
            color: #2563eb;
        }

        .project-status-badge.on_hold {
            background: #fef3c7;
            color: #d97706;
        }

        .project-status-badge.done {
            background: #d1fae5;
            color: #059669;
        }

        .project-date {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .project-date i {
            margin-right: 0.25rem;
        }

        .project-card-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .progress-bar-small {
            flex: 1;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-small .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 3px;
            transition: width 0.3s;
        }

        .progress-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            min-width: 32px;
            text-align: right;
        }

        .project-card-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .project-card-stats i {
            margin-right: 0.25rem;
        }

        @media (max-width: 768px) {
            .projects-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>

    <script>        // Project Kanban Drag and Drop
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.kanban-card.draggable');
            const columnBodies = document.querySelectorAll('.kanban-column-body');

            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            columnBodies.forEach(column => {
                column.addEventListener('dragover', handleDragOver);
                column.addEventListener('dragleave', handleDragLeave);
                column.addEventListener('drop', handleDrop);
            });

            let draggedCard = null;

            function handleDragStart(e) {
                draggedCard = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', this.dataset.projectId);
            }

            function handleDragEnd(e) {
                this.classList.remove('dragging');
                columnBodies.forEach(col => col.classList.remove('drag-over'));
                draggedCard = null;
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
            }

            function handleDragLeave(e) {
                this.classList.remove('drag-over');
            }

            function handleDrop(e) {
                e.preventDefault();
                this.classList.remove('drag-over');

                if (!draggedCard) return;

                const projectId = draggedCard.dataset.projectId;
                const projectName = draggedCard.dataset.projectName;
                const newStatus = this.dataset.status;
                const oldStatus = draggedCard.closest('.kanban-column').dataset.status;

                if (newStatus === oldStatus) return;

                // Optimistic UI update
                this.appendChild(draggedCard);
                updateColumnCounts();

                // Send AJAX request
                fetch(`/projects/${projectId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.changed) {
                        // Update the status date display
                        let statusDateEl = draggedCard.querySelector('.kanban-card-status-date');
                        if (!statusDateEl) {
                            statusDateEl = document.createElement('div');
                            statusDateEl.className = 'kanban-card-status-date';
                            draggedCard.appendChild(statusDateEl);
                        }
                        statusDateEl.innerHTML = `<i class="fas fa-clock"></i> ${data.changed_at} <span class="status-changed-by">oleh ${data.changed_by}</span>`;
                        
                        showToast(`Proyek "${projectName}" dipindahkan ke ${getStatusLabel(newStatus)}`, 'success');
                    } else if (!data.success) {
                        // Revert on error
                        document.querySelector(`.kanban-column[data-status="${oldStatus}"] .kanban-column-body`).appendChild(draggedCard);
                        updateColumnCounts();
                        showToast(data.message || 'Gagal memindahkan proyek', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert on error
                    document.querySelector(`.kanban-column[data-status="${oldStatus}"] .kanban-column-body`).appendChild(draggedCard);
                    updateColumnCounts();
                    showToast('Terjadi kesalahan', 'error');
                });
            }

            function updateColumnCounts() {
                document.querySelectorAll('.kanban-column').forEach(column => {
                    const count = column.querySelectorAll('.kanban-card').length;
                    column.querySelector('.column-count').textContent = count;
                    
                    // Show/hide empty state
                    const emptyState = column.querySelector('.kanban-empty');
                    if (emptyState) {
                        emptyState.style.display = count === 0 ? 'flex' : 'none';
                    }
                });
            }

            function getStatusLabel(status) {
                const labels = {
                    'new': 'Baru',
                    'in_progress': 'Berjalan',
                    'review': 'Review',
                    'done': 'Selesai'
                };
                return labels[status] || status;
            }

            function showToast(message, type = 'info') {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `toast-notification toast-${type}`;
                toast.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(toast);

                // Animate in
                setTimeout(() => toast.classList.add('show'), 10);

                // Remove after 3 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        });
    </script>

    <style>
        /* Toast Notification - Compact & Modern */
        .toast-notification {
            position: fixed;
            bottom: 16px;
            right: 16px;
            padding: 0.65rem 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 9999;
            transform: translateY(80px) scale(0.95);
            opacity: 0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.8rem;
            max-width: 320px;
            backdrop-filter: blur(8px);
        }

        .toast-notification.show {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .toast-notification i {
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .toast-notification span {
            line-height: 1.4;
            color: #374151;
        }

        .toast-success {
            border-left: 3px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }

        .toast-success i {
            color: #10b981;
        }

        .toast-error {
            border-left: 3px solid #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        }

        .toast-error i {
            color: #ef4444;
        }

        .toast-warning {
            border-left: 3px solid #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
        }

        .toast-warning i {
            color: #f59e0b;
        }
    </style>
@endsection