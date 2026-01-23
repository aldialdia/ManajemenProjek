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
        @if($canCreateProject)
            <a href="{{ route('projects.create') }}" class="btn btn-welcome">
                <i class="fas fa-plus"></i>
                Tambah Proyek Baru
            </a>
        @endif
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

    <!-- Bottom Row -->
    <div class="grid grid-cols-2">
        <!-- Active Projects -->
        <div class="card">
            <div class="card-header">
                <div>
                    <span>Proyek Aktif</span>
                    <p class="text-muted text-sm" style="margin: 0;">Progress terkini</p>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($activeProjectsList as $project)
                    <div class="project-row">
                        <div class="project-row-info">
                            <a href="{{ route('projects.show', $project) }}" class="project-row-title">{{ $project->name }}</a>
                            <span class="project-row-date">
                                <i class="fas fa-calendar"></i> {{ $project->end_date?->format('Y-m-d') ?? 'No deadline' }}
                            </span>
                        </div>
                        <div class="project-row-progress">
                            <span class="badge badge-primary">Tinggi</span>
                            <div class="mini-progress">
                                <div class="mini-progress-fill" style="width: {{ $project->progress }}%;"></div>
                            </div>
                            <span class="progress-text">{{ $project->progress }}%</span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 2rem; text-align: center; color: #64748b;">
                        <p>Belum ada proyek aktif</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="card">
            <div class="card-header">
                <div>
                    <span><i class="fas fa-exclamation-circle text-warning"></i> Deadline Terdekat</span>
                    <p class="text-muted text-sm" style="margin: 0;">Perhatian khusus diperlukan</p>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($upcomingDeadlines as $task)
                    <div class="deadline-row">
                        <div
                            class="deadline-priority {{ $task->priority->value === 'high' ? 'priority-high' : ($task->priority->value === 'medium' ? 'priority-medium' : 'priority-low') }}">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="deadline-info">
                            <a href="{{ route('tasks.show', $task) }}" class="deadline-title">{{ $task->title }}</a>
                            <span class="deadline-project">{{ $task->project?->name ?? 'No project' }}</span>
                        </div>
                        <div class="deadline-meta">
                            <span class="deadline-date">
                                <i class="fas fa-calendar"></i> {{ $task->due_date->format('Y-m-d') }}
                            </span>
                            <span class="deadline-remaining">
                                @php
                                    $daysLeft = now()->startOfDay()->diffInDays($task->due_date->startOfDay());
                                @endphp
                                <i class="fas fa-clock"></i> {{ $daysLeft }} hari lagi
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 2rem; text-align: center; color: #64748b;">
                        <p>Tidak ada deadline mendekati</p>
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
    </style>
@endsection