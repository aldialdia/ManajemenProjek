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
            <select class="form-control" id="periodFilter" style="width: 180px;" onchange="filterByPeriod()">
                <option value="30" {{ ($period ?? '30') == '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
                <option value="7" {{ ($period ?? '30') == '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                <option value="today" {{ ($period ?? '30') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="month" {{ ($period ?? '30') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="year" {{ ($period ?? '30') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row-report">
        <div class="stat-card-report">
            <div class="stat-icon-sm stat-blue">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Tugas</span>
                <span class="stat-value">{{ $totalTasks }}</span>
                @if($taskChange != 0)
                <span class="stat-change {{ $taskChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $taskChange >= 0 ? 'up' : 'down' }}"></i> {{ $taskChange >= 0 ? '+' : '' }}{{ $taskChange }}% dari bulan lalu
                </span>
                @endif
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Tugas Selesai</span>
                <span class="stat-value">{{ $completedTasks }}</span>
                @if($taskChange != 0)
                <span class="stat-change {{ $taskChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $taskChange >= 0 ? 'up' : 'down' }}"></i> {{ $taskChange >= 0 ? '+' : '' }}{{ $taskChange }}% dari bulan lalu
                </span>
                @endif
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-purple">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Jam Kerja</span>
                <span class="stat-value">{{ $totalHours }}</span>
                @if($hoursChange != 0)
                <span class="stat-change {{ $hoursChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $hoursChange >= 0 ? 'up' : 'down' }}"></i> {{ $hoursChange >= 0 ? '+' : '' }}{{ $hoursChange }}% dari bulan lalu
                </span>
                @endif
            </div>
        </div>

        <div class="stat-card-report">
            <div class="stat-icon-sm stat-orange">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Anggota Tim</span>
                <span class="stat-value">{{ $totalMembers }}</span>
                @if($memberChange != 0)
                <span class="stat-change {{ $memberChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $memberChange >= 0 ? 'up' : 'down' }}"></i> {{ $memberChange >= 0 ? '+' : '' }}{{ $memberChange }} dari bulan lalu
                </span>
                @endif
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
            <!-- Status Tugas Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <span>Status Tugas</span>
                    <span class="text-muted text-sm">Distribusi berdasarkan status</span>
                </div>
                <div class="card-body">
                    <canvas id="statusTugasChart" height="280"></canvas>
                </div>
            </div>

            <!-- Distribusi Waktu Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <span>Distribusi Waktu Kerja</span>
                    <span class="text-muted text-sm">Berdasarkan status tugas</span>
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
                            <th>Aktivitas</th>
                            <th>Ditugaskan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['user'] }}</td>
                                <td>{{ $activity['date'] }}</td>
                                <td>{{ $activity['time'] }}</td>
                                <td>
                                    @php
                                        $statusClass = match($activity['status']) {
                                            'Done' => 'status-done',
                                            'In Progress' => 'status-in-progress',
                                            'Review' => 'status-review',
                                            'To Do' => 'status-pending',
                                            default => 'status-pending'
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
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

        // Period filter
        function filterByPeriod() {
            const period = document.getElementById('periodFilter').value;
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        }


        // Status Tugas Pie Chart
        const statusCtx = document.getElementById('statusTugasChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Done', 'In Progress', 'Review', 'To Do'],
                datasets: [{
                    data: [
                            {{ $tasksByStatus['done'] ?? 0 }},
                            {{ $tasksByStatus['in_progress'] ?? 0 }},
                            {{ $tasksByStatus['review'] ?? 0 }},
                        {{ $tasksByStatus['todo'] ?? 0 }}
                    ],
                    backgroundColor: ['#22c55e', '#3b82f6', '#8b5cf6', '#f59e0b'],
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
                labels: ['Done', 'In Progress', 'Review', 'To Do'],
                datasets: [{
                    data: [
                            {{ $timeDistribution['done'] ?? 0 }},
                            {{ $timeDistribution['in_progress'] ?? 0 }},
                            {{ $timeDistribution['review'] ?? 0 }},
                        {{ $timeDistribution['todo'] ?? 0 }}
                    ],
                    backgroundColor: ['#22c55e', '#3b82f6', '#8b5cf6', '#f59e0b'],
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

        // Team Productivity Bar Chart - Completion Percentage
        const teamCtx = document.getElementById('teamProductivityChart').getContext('2d');
        const completedCounts = {!! json_encode($tasksByUser->pluck('completed_count')->toArray()) !!};
        const totalCounts = {!! json_encode($tasksByUser->pluck('total_tasks_count')->toArray()) !!};
        const percentages = {!! json_encode($tasksByUser->pluck('completion_percentage')->toArray()) !!};
        
        // Dynamic colors based on percentage
        const barColors = percentages.map(function(pct) {
            if (pct >= 80) return '#22c55e'; // Green - Excellent
            if (pct >= 60) return '#84cc16'; // Lime - Good
            if (pct >= 30) return '#f59e0b'; // Orange - Needs improvement
            return '#ef4444'; // Red - Low
        });
        
        new Chart(teamCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($tasksByUser->pluck('name')->toArray()) !!},
                datasets: [{
                    label: 'Tugas Selesai (%)',
                    data: percentages,
                    backgroundColor: barColors,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                const completed = completedCounts[idx];
                                const total = totalCounts[idx];
                                const percentage = context.parsed.y;
                                return completed + ' / ' + total + ' tugas selesai (' + percentage + '%)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
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
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: #22c55e;
        }

        .stat-change.negative {
            color: #ef4444;
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

        .status-done {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-in-progress {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-review {
            background: #ede9fe;
            color: #7c3aed;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
    </style>
@endsection