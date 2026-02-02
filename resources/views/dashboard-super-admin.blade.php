@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
    <!-- Super Admin Welcome Banner -->
    <div class="super-admin-banner">
        <div class="banner-content">
            <div class="admin-badge">
                <i class="fas fa-crown"></i>
                Super Admin
            </div>
            <h1 class="banner-title">System Overview Dashboard</h1>
            <p class="banner-subtitle">Monitoring dan manajemen seluruh sistem manajemen project</p>
        </div>
        <div class="banner-actions">
            <a href="{{ route('projects.index') }}" class="btn-admin-action">
                <i class="fas fa-folder-open"></i>
                Semua Project
            </a>
            <a href="{{ route('projects.kanban') }}" class="btn-admin-action secondary">
                <i class="fas fa-th"></i>
                Kanban Board
            </a>
        </div>
    </div>

    <!-- System Health Alerts -->
    @if($systemHealth['overdue_tasks'] > 0 || $systemHealth['overdue_projects'] > 0 || $systemHealth['unassigned_tasks'] > 0)
        <div class="system-alerts">
            @if($systemHealth['overdue_projects'] > 0)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>{{ $systemHealth['overdue_projects'] }} Project Terlambat</strong>
                        <p>Project yang melewati deadline dan belum selesai</p>
                    </div>
                </div>
            @endif
            @if($systemHealth['overdue_tasks'] > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>{{ $systemHealth['overdue_tasks'] }} Task Terlambat</strong>
                        <p>Task yang melewati due date dan belum selesai</p>
                    </div>
                </div>
            @endif
            @if($systemHealth['unassigned_tasks'] > 0)
                <div class="alert alert-info">
                    <i class="fas fa-user-slash"></i>
                    <div>
                        <strong>{{ $systemHealth['unassigned_tasks'] }} Task Belum Ditugaskan</strong>
                        <p>Task yang belum memiliki assignee</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Stats Grid -->
    <div class="admin-stats-grid">
        <!-- Projects Stats -->
        <div class="admin-stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Projects</span>
                    <span class="stat-value">{{ $stats['total_projects'] }}</span>
                </div>
            </div>
            <div class="stat-breakdown">
                <div class="breakdown-item">
                    <span class="breakdown-label">Active</span>
                    <span class="breakdown-value">{{ $stats['active_projects'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">On Hold</span>
                    <span class="breakdown-value text-warning">{{ $stats['on_hold_projects'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Completed</span>
                    <span class="breakdown-value text-success">{{ $stats['completed_projects'] }}</span>
                </div>
            </div>
        </div>

        <!-- Tasks Stats -->
        <div class="admin-stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Tasks</span>
                    <span class="stat-value">{{ $stats['total_tasks'] }}</span>
                </div>
            </div>
            <div class="stat-breakdown">
                <div class="breakdown-item">
                    <span class="breakdown-label">Completed</span>
                    <span class="breakdown-value text-success">{{ $stats['completed_tasks'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Pending</span>
                    <span class="breakdown-value">{{ $stats['pending_tasks'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Completion</span>
                    <span
                        class="breakdown-value">{{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- Users Stats -->
        <div class="admin-stat-card purple">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Users</span>
                    <span class="stat-value">{{ $stats['total_users'] }}</span>
                </div>
            </div>
            <div class="stat-breakdown">
                <div class="breakdown-item">
                    <span class="breakdown-label">Active</span>
                    <span class="breakdown-value text-success">{{ $stats['active_users'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Clients</span>
                    <span class="breakdown-value">{{ $stats['total_clients'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Monthly Trends Chart -->
        <div class="card">
            <div class="card-header">
                <span>Trend Bulanan (6 Bulan Terakhir)</span>
                <span class="text-muted text-sm">Projects & Tasks</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Project Status Distribution -->
        <div class="card">
            <div class="card-header">
                <span>Distribusi Status Project</span>
                <span class="text-muted text-sm">Total: {{ $stats['total_projects'] }} projects</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="projectStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Project Type Distribution & Task Distribution -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Project Type Distribution (RBB vs Non-RBB) -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-chart-pie text-primary"></i> Distribusi Tipe Project</span>
                <span class="text-muted text-sm">RBB vs Non-RBB</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="projectTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Task Distribution per Member -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-user-check text-success"></i> Distribusi Tugas per Anggota</span>
                <span class="text-muted text-sm">Top 10 Members</span>
            </div>
            <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                @forelse($taskDistribution as $index => $member)
                    <div class="admin-list-item">
                        <div class="rank-badge-small">
                            #{{ $index + 1 }}
                        </div>
                        <div class="item-content">
                            <div class="item-title">{{ $member->name }}</div>
                            <div class="item-meta">
                                <span class="meta-item">
                                    <i class="fas fa-tasks"></i>
                                    {{ $member->total_tasks }} tugas
                                </span>
                                <span class="meta-item text-success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $member->completed_tasks }} selesai
                                </span>
                                <span class="meta-item text-warning">
                                    <i class="fas fa-clock"></i>
                                    {{ $member->pending_tasks }} pending
                                </span>
                            </div>
                        </div>
                        <div class="task-progress-bar">
                            @php
                                $completionRate = $member->total_tasks > 0 ? round(($member->completed_tasks / $member->total_tasks) * 100) : 0;
                            @endphp
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: {{ $completionRate }}%;"></div>
                            </div>
                            <span class="progress-text">{{ $completionRate }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>Belum ada distribusi tugas</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Projects with Issues -->
    @if($projectsWithIssues->count() > 0)
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-exclamation-circle text-danger"></i> Projects Memerlukan Perhatian</span>
                <span class="text-muted text-sm">{{ $projectsWithIssues->count() }} projects</span>
            </div>
            <div class="card-body" style="padding: 0;">
                @foreach($projectsWithIssues as $project)
                    <div class="admin-list-item issue-item">
                        <div class="item-icon danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="item-content">
                            <a href="{{ route('projects.show', $project) }}" class="item-title">
                                {{ $project->name }}
                            </a>
                            <div class="item-meta">
                                @if($project->status->value === 'on_hold')
                                    <span class="meta-item text-warning">
                                        <i class="fas fa-pause-circle"></i>
                                        Project On Hold
                                    </span>
                                @endif
                                @if($project->end_date && $project->end_date->isPast() && $project->status->value !== 'done')
                                    <span class="meta-item text-danger">
                                        <i class="fas fa-calendar-times"></i>
                                        Terlambat {{ $project->end_date->locale('id')->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="item-status">
                            <span class="status-badge {{ $project->status->value }}">
                                {{ $project->status->label() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        Chart.defaults.animation = false;

        // Monthly Trends Chart
        const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlyTrends, 'month')) !!},
                datasets: [{
                    label: 'Projects',
                    data: {!! json_encode(array_column($monthlyTrends, 'projects')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Tasks',
                    data: {!! json_encode(array_column($monthlyTrends, 'tasks')) !!},
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
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
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Project Status Distribution Chart
        const statusCtx = document.getElementById('projectStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['New', 'In Progress', 'On Hold', 'Done'],
                datasets: [{
                    data: [
                                            {{ $projectsByStatus['new'] }},
                                            {{ $projectsByStatus['in_progress'] }},
                                            {{ $projectsByStatus['on_hold'] }},
                        {{ $projectsByStatus['done'] }}
                    ],
                    backgroundColor: ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981'],
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
        .super-admin-banner {
            background: linear-gradient(135deg, #f97316 0%, #3b82f6 50%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.25);
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .banner-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .banner-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .banner-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-admin-action {
            background: white;
            color: #dc2626;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-admin-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-admin-action.secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-admin-action.secondary:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .system-alerts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            border-left: 4px solid;
        }

        .alert i {
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }

        .alert strong {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .alert p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }

        .alert-info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .admin-stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .admin-stat-card.primary .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .admin-stat-card.success .stat-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .admin-stat-card.purple .stat-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-label {
            display: block;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-breakdown {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .breakdown-item {
            text-align: center;
        }

        .breakdown-label {
            display: block;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }

        .breakdown-value {
            display: block;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
        }

        .text-success {
            color: #22c55e !important;
        }

        .text-warning {
            color: #f59e0b !important;
        }

        .text-danger {
            color: #ef4444 !important;
        }

        .admin-list-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }

        .admin-list-item:hover {
            background: #f8fafc;
        }

        .admin-list-item:last-child {
            border-bottom: none;
        }

        .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }

        .item-icon.new {
            background: #94a3b8;
        }

        .item-icon.in_progress {
            background: #3b82f6;
        }

        .item-icon.on_hold {
            background: #f59e0b;
        }

        .item-icon.done {
            background: #22c55e;
        }

        .item-icon.danger {
            background: #ef4444;
        }

        .item-content {
            flex: 1;
            min-width: 0;
        }

        .item-title {
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
            display: block;
            margin-bottom: 0.25rem;
        }

        .item-title:hover {
            color: var(--primary);
        }

        .item-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .meta-item {
            font-size: 0.75rem;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-badge.new {
            background: #f1f5f9;
            color: #64748b;
        }

        .status-badge.in_progress {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-badge.on_hold {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge.done {
            background: #dcfce7;
            color: #16a34a;
        }

        .rank-badge {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .rank-badge.rank-1 {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }

        .rank-badge.rank-2 {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
        }

        .rank-badge.rank-3 {
            background: linear-gradient(135deg, #fb923c, #ea580c);
            color: white;
        }

        .rank-badge.rank-4,
        .rank-badge.rank-5 {
            background: #f1f5f9;
            color: #64748b;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-initials {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.875rem;
        }

        .issue-item {
            background: #fef2f2;
        }

        .issue-item:hover {
            background: #fee2e2;
        }

        /* Task Distribution Styles */
        .rank-badge-small {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            background: #f1f5f9;
            color: #64748b;
        }

        .task-progress-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 100px;
        }

        .progress-bar-container {
            flex: 1;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            min-width: 35px;
            text-align: right;
        }
    </style>
@endsection