@extends('layouts.app')

@section('title', ($project ? $project->name . ' - ' : '') . 'Laporan')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div>
            @if($project)
                <a href="{{ route('projects.show', $project) }}" class="back-link"
                    style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #64748b; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Proyek
                </a>
            @endif
            <h1 class="page-title">Laporan & Analitik</h1>
            <p class="page-subtitle">Laporan kinerja {{ $project ? $project->name : 'Anda' }}</p>
        </div>
        <div class="filter-dropdown">
            <select class="form-control" style="width: 180px;">
                <option>30 Hari Terakhir</option>
                <option>7 Hari Terakhir</option>
                <option>Bulan Ini</option>
                <option>Tahun Ini</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row-report">
        <div class="stat-card-report">
            <div class="stat-icon-sm stat-blue">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Proyek</span>
                <span class="stat-value">{{ $totalProjects }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +12% dari bulan lalu
                </span>
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Tugas Selesai</span>
                <span class="stat-value">{{ $completedTasks }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +8% dari bulan lalu
                </span>
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-purple">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Jam Kerja</span>
                <span class="stat-value">{{ $totalHours }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +15% dari bulan lalu
                </span>
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-orange">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Anggota Tim</span>
                <span class="stat-value">{{ $totalMembers }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +2 dari bulan lalu
                </span>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="report-tabs">
        <button class="tab-btn active" onclick="showTab('overview')">Overview</button>
        <button class="tab-btn" onclick="showTab('proyek')">Proyek</button>
        <button class="tab-btn" onclick="showTab('waktu')">Waktu</button>
    </div>

    <!-- Tab Content: Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
            <!-- Status Proyek Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <span>Status Proyek</span>
                </div>
                <div class="card-body">
                    <canvas id="statusProyekChart" height="280"></canvas>
                </div>
            </div>

            <!-- Distribusi Waktu Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <span>Distribusi Waktu</span>
                </div>
                <div class="card-body">
                    <canvas id="distribusiWaktuChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Proyek -->
    <div id="tab-proyek" class="tab-content">
        <div class="card">
            <div class="card-header">
                <span>Produktivitas Tim</span>
            </div>
            <div class="card-body">
                <canvas id="teamProductivityChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Tab Content: Waktu -->
    <div id="tab-waktu" class="tab-content">
        <!-- Recent Activities Table -->
        <div class="card">
            <div class="card-header">
                <span>Aktivitas Terbaru</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Proyek</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity['project'] }}</td>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['time'] }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $activity['status'] === 'Selesai' ? 'status-success' : 'status-pending' }}">
                                        {{ $activity['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Disable global animation
        Chart.defaults.animation = false;

        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Status Proyek Pie Chart
        const statusCtx = document.getElementById('statusProyekChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Sedang Berjalan', 'Ditunda', 'Baru'],
                datasets: [{
                    data: [
                            {{ $projectsByStatus['completed'] ?? 0 }},
                            {{ $projectsByStatus['active'] ?? 0 }},
                            {{ $projectsByStatus['on_hold'] ?? 0 }},
                        {{ $projectsByStatus['cancelled'] ?? 0 }}
                    ],
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
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
                            font: { size: 13 }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Distribusi Waktu Pie Chart
        const waktuCtx = document.getElementById('distribusiWaktuChart').getContext('2d');
        new Chart(waktuCtx, {
            type: 'doughnut',
            data: {
                labels: ['Development', 'Meetings', 'Review', 'Planning', 'Admin'],
                datasets: [{
                    data: [
                            {{ $timeDistribution['development'] ?? 40 }},
                            {{ $timeDistribution['meetings'] ?? 20 }},
                            {{ $timeDistribution['review'] ?? 15 }},
                            {{ $timeDistribution['planning'] ?? 15 }},
                        {{ $timeDistribution['admin'] ?? 10 }}
                    ],
                    backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#22c55e', '#ef4444'],
                    borderWidth: 0
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
                            font: { size: 13 }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Team Productivity Bar Chart
        const teamCtx = document.getElementById('teamProductivityChart').getContext('2d');
        new Chart(teamCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($tasksByUser->pluck('name')->toArray()) !!},
                datasets: [{
                    label: 'Pending',
                    data: {!! json_encode($tasksByUser->pluck('pending_count')->toArray()) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 4
                }, {
                    label: 'Selesai',
                    data: {!! json_encode($tasksByUser->pluck('completed_count')->toArray()) !!},
                    backgroundColor: '#22c55e',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
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
    </script>

    <style>
        .stats-row-report {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card-report {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-sm {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .stat-green {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .stat-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .stat-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-change {
            font-size: 0.7rem;
            color: #22c55e;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .report-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity-table th {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background: #f8fafc;
        }

        .activity-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
    </style>
@endsection