@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
    <!-- Super Admin Welcome Banner -->
    <div class="modern-banner">
        <div class="banner-overlay"></div>
        <div class="banner-content-wrapper">
            <div class="admin-badge-modern">
                <i class="fas fa-crown"></i>
                Super Admin
            </div>
            <h1 class="banner-title-modern">System Overview Dashboard</h1>
            <p class="banner-subtitle-modern">Monitoring dan manajemen seluruh sistem manajemen project</p>
        </div>
        <div class="banner-actions-modern">
            <a href="{{ route('projects.index') }}" class="btn-modern primary">
                <i class="fas fa-folder"></i>
                Semua Project
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

    <!-- Floating Export Excel Button -->
    <a href="{{ route('dashboard.export') }}" class="floating-export-btn" title="Export Excel">
        <i class="fas fa-file-excel"></i>
        <span>Export</span>
    </a>

    <style>
        .floating-export-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669, #047857);
        }

        .floating-export-btn i {
            font-size: 1.1rem;
        }
    </style>

    <!-- Main Stats Grid -->
    <div class="modern-stats-grid">
        <!-- Projects Stats -->
        <div class="modern-stat-card blue">
            <div class="stat-icon-wrapper blue">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label-modern">Total Projects</span>
                <span class="stat-value-modern">{{ $stats['total_projects'] }}</span>
                <div class="stat-breakdown-modern">
                    <div class="breakdown-item-modern">
                        <span>RBB</span>
                        <strong>{{ $projectsByType['rbb'] ?? 0 }}</strong>
                    </div>
                    <div class="breakdown-item-modern">
                        <span>Non-RBB</span>
                        <strong>{{ $projectsByType['non_rbb'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
            <div class="stat-progress-bar blue"></div>
        </div>

        <!-- Tasks Stats -->
        <div class="modern-stat-card orange">
            <div class="stat-icon-wrapper orange">
                <i class="fas fa-list-check"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label-modern">Total Tasks</span>
                <span class="stat-value-modern">{{ $stats['total_tasks'] }}</span>
                <div class="stat-breakdown-modern">
                    <div class="breakdown-item-modern">
                        <span>Completed</span>
                        <strong>{{ $tasksByStatus['done'] ?? 0 }}</strong>
                    </div>
                    <div class="breakdown-item-modern">
                        <span>Pending</span>
                        <strong>{{ ($tasksByStatus['todo'] ?? 0) + ($tasksByStatus['in_progress'] ?? 0) }}</strong>
                    </div>
                    <div class="breakdown-item-modern">
                        <span>Completion</span>
                        <strong>{{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }}%</strong>
                    </div>
                </div>
            </div>
            <div class="stat-progress-bar orange"></div>
        </div>

        <!-- Users Stats -->
        <div class="modern-stat-card purple">
            <div class="stat-icon-wrapper purple">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label-modern">Total Users</span>
                <span class="stat-value-modern">{{ $stats['total_users'] }}</span>
                <div class="stat-breakdown-modern">
                    <div class="breakdown-item-modern">
                        <span>Active</span>
                        <strong>{{ $stats['active_users'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="stat-progress-bar purple"></div>
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


    <!-- Task Distribution & Projects with Issues (Side by Side) -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Task Distribution per Member -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-user-check text-success"></i> Distribusi Tugas per Anggota</span>
                <a href="#" onclick="showAllTaskDistribution(); return false;" class="text-sm text-primary"
                    style="cursor: pointer;">
                    <i class="fas fa-external-link-alt"></i> View All
                </a>
            </div>
            <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                @forelse($taskDistribution as $index => $member)
                    <div class="admin-list-item">
                        <div class="item-content">
                            <div class="item-title">{{ $member->name }}</div>
                            <div class="item-meta">
                                <span class="meta-item">
                                    <i class="fas fa-tasks"></i>
                                    {{ $member->total_tasks }} tugas
                                </span>
                                @php
                                    // Get task counts by status
                                    $tasksByStatus = \DB::table('tasks')
                                        ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
                                        ->where('task_user.user_id', $member->id)
                                        ->select('tasks.status', \DB::raw('COUNT(*) as count'))
                                        ->groupBy('tasks.status')
                                        ->pluck('count', 'status');

                                    $todoCount = $tasksByStatus['todo'] ?? 0;
                                    $inProgressCount = $tasksByStatus['in_progress'] ?? 0;
                                    $reviewCount = $tasksByStatus['review'] ?? 0;
                                    $doneCount = $tasksByStatus['done'] ?? 0;
                                @endphp
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
        @else<div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem; color: #94a3b8;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">Semua project berjalan lancar! 🎉</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal: All Task Distribution -->
    <div id="allTaskDistributionModal" class="modal-overlay" style="display: none;">
        <div class="modal-container-large">
            <div class="modal-header">
                <h3><i class="fas fa-users"></i> Distribusi Tugas Semua Anggota</h3>
                <button onclick="closeAllTaskDistributionModal()" class="modal-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-scrollable">
                @forelse($allTaskDistribution as $index => $member)
                    <div class="task-distribution-item-modal">
                        <div class="distribution-header">
                            <div class="rank-badge-small">
                                #{{ $index + 1 }}
                            </div>
                            <div class="member-info">
                                <div class="member-name">{{ $member->name }}</div>
                                <div class="member-stats">
                                    <span class="task-status-badge total">
                                        <i class="fas fa-list"></i>
                                        {{ $member->total_tasks }} tugas
                                    </span>
                                    @php
                                        // Get task counts by status for modal
                                        $tasksByStatus = collect($member->tasks_with_projects)->groupBy('status');
                                        $todoCount = $tasksByStatus->get('todo', collect())->count();
                                        $inProgressCount = $tasksByStatus->get('in_progress', collect())->count();
                                        $reviewCount = $tasksByStatus->get('review', collect())->count();
                                        $doneCount = $tasksByStatus->get('done', collect())->count();
                                    @endphp
                                    @if($doneCount > 0)
                                        <span class="task-status-badge done">
                                            <i class="fas fa-check-circle"></i>
                                            {{ $doneCount }} selesai
                                        </span>
                                    @endif
                                    @if($inProgressCount > 0)
                                        <span class="task-status-badge in-progress">
                                            <i class="fas fa-spinner"></i>
                                            {{ $inProgressCount }} in progress
                                        </span>
                                    @endif
                                    @if($todoCount > 0)
                                        <span class="task-status-badge todo">
                                            <i class="fas fa-circle"></i>
                                            {{ $todoCount }} to do
                                        </span>
                                    @endif
                                    @if($reviewCount > 0)
                                        <span class="task-status-badge review">
                                            <i class="fas fa-eye"></i>
                                            {{ $reviewCount }} review
                                        </span>
                                    @endif
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

                        @if($member->tasks_with_projects && $member->tasks_with_projects->count() > 0)
                            <div class="task-list-modal">
                                @foreach($member->tasks_with_projects as $task)
                                    <div class="task-item-mini">
                                        <div class="task-status-dot {{ $task->status }}"></div>
                                        <div class="task-details">
                                            <span class="task-title-mini">{{ $task->title }}</span>
                                            <span class="task-project-mini">
                                                <i class="fas fa-folder"></i>
                                                {{ $task->project_name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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

        // Project Type Distribution Chart (RBB vs Non-RBB)
        const typeCtx = document.getElementById('projectTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['RBB', 'Non-RBB'],
                datasets: [{
                    data: [
                                                                                                                                                                                    {{ $projectsByType['rbb'] }},
                        {{ $projectsByType['non_rbb'] }}
                    ],
                    backgroundColor: ['#6366f1', '#64748b'],
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

        // Function to show all task distribution
        function showAllTaskDistribution() {
            document.getElementById('allTaskDistributionModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        // Function to close all task distribution modal
        function closeAllTaskDistributionModal() {
            document.getElementById('allTaskDistributionModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal when clicking outside
        document.getElementById('allTaskDistributionModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeAllTaskDistributionModal();
            }
        });
    </script>

    <style>
        /* Modern Banner Styles */
        .modern-banner {
            position: relative;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 40%, #f97316 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            overflow: hidden;
            min-height: 200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.3), transparent 50%);
            pointer-events: none;
        }

        .banner-content-wrapper {
            position: relative;
            z-index: 1;
            color: white;
        }

        .admin-badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(139, 92, 246, 0.9);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        .admin-badge-modern i {
            font-size: 1rem;
        }

        .banner-title-modern {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }

        .banner-subtitle-modern {
            font-size: 1rem;
            opacity: 0.95;
            color: rgba(255, 255, 255, 0.9);
        }

        .banner-actions-modern {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 1rem;
            flex-direction: column;
        }

        .btn-modern {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-modern.primary {
            background: white;
            color: #1e3a8a;
        }

        .btn-modern.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .btn-modern.secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-modern.secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn-modern.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-modern.success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Modern Stats Grid */
        .modern-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .modern-stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .modern-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            color: white;
        }

        .stat-icon-wrapper.blue {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .stat-icon-wrapper.orange {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .stat-icon-wrapper.purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-label-modern {
            display: block;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-value-modern {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .stat-breakdown-modern {
            display: flex;
            gap: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
        }

        .breakdown-item-modern {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .breakdown-item-modern span {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .breakdown-item-modern strong {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
        }

        .stat-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-progress-bar.blue {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }

        .stat-progress-bar.orange {
            background: linear-gradient(90deg, #f97316, #ea580c);
        }

        .stat-progress-bar.purple {
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
        }

        /* System Alerts */
        .system-alerts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            border-left: 5px solid;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .alert i {
            font-size: 1.75rem;
            margin-top: 0.125rem;
        }

        .alert strong {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .alert p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.4;
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

        /* Task Status Badge Styles */
        .task-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .task-status-badge i {
            font-size: 0.7rem;
        }

        .task-status-badge.total {
            background: #f1f5f9;
            color: #64748b;
        }

        .task-status-badge.done {
            background: #dcfce7;
            color: #16a34a;
        }

        .task-status-badge.in-progress {
            background: #dbeafe;
            color: #2563eb;
        }

        .task-status-badge.todo {
            background: #f1f5f9;
            color: #64748b;
        }

        .task-status-badge.review {
            background: #fef3c7;
            color: #d97706;
        }

        /* Task Distribution Styles */
        .task-distribution-item {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s ease;
        }

        .task-distribution-item:hover {
            background: #f8fafc;
        }

        .task-distribution-item:last-child {
            border-bottom: none;
        }

        .distribution-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .member-info {
            flex: 1;
            min-width: 0;
        }

        .member-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .member-stats {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            background: #f1f5f9;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .stat-badge.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-badge.warning {
            background: #fef3c7;
            color: #d97706;
        }

        .stat-badge i {
            font-size: 0.7rem;
        }

        .task-list {
            margin-top: 0.75rem;
            padding-left: 3rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .task-item-mini {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .task-item-mini:hover {
            background: #f1f5f9;
            border-left-color: #3b82f6;
        }

        .task-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .task-status-dot.todo {
            background: #94a3b8;
        }

        .task-status-dot.in_progress {
            background: #3b82f6;
        }

        .task-status-dot.review {
            background: #f59e0b;
        }

        .task-status-dot.done {
            background: #22c55e;
        }

        .task-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .task-title-mini {
            font-size: 0.8125rem;
            color: #1e293b;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .task-project-mini {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .task-project-mini i {
            font-size: 0.7rem;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .modal-container-large {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 900px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 1.75rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.375rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-header h3 i {
            color: #3b82f6;
        }

        .modal-close-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 1.125rem;
        }

        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .modal-body-scrollable {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .task-distribution-item-modal {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s ease;
        }

        .task-distribution-item-modal:hover {
            background: #f8fafc;
        }

        .task-distribution-item-modal:last-child {
            border-bottom: none;
        }

        .task-list-modal {
            margin-top: 1rem;
            padding-left: 3rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        /* Scrollbar Styling */
        .modal-body-scrollable::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body-scrollable::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .modal-body-scrollable::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .modal-body-scrollable::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endsection