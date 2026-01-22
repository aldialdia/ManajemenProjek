@extends('layouts.app')

@section('title', 'Time Tracking - ' . $project->name)

@section('content')
    @php
        $projectOnHold = $project->isOnHold();
        $isManager = auth()->user()->isManagerInProject($project);
        $canTrack = !$projectOnHold || $isManager;
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">Time Tracking</h1>
            <p class="page-subtitle">Catat dan pantau jam kerja untuk project <strong>{{ $project->name }}</strong></p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Jam Hari Ini</span>
                <span class="stat-value">{{ floor($todaySeconds / 3600) }}.{{ floor(($todaySeconds % 3600) / 60) }}j</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    dari kemarin
                </span>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Minggu Ini</span>
                <span class="stat-value">{{ round($weekSeconds / 3600, 1) }}j</span>

            </div>
            <div class="stat-icon green">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Rata-rata/Hari</span>
                <span class="stat-value">{{ round($avgDailySeconds / 3600, 1) }}j</span>
                <span class="stat-meta">7 hari terakhir</span>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Recent Entries -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3>Entri Waktu Terbaru</h3>
            <span class="text-muted">Daftar waktu yang telah dicatat</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($recentEntries as $entry)
                <div class="time-entry">
                    <div class="entry-main">
                        <div class="entry-task">{{ $entry->task->title }}</div>
                        <div class="entry-details">
                            <span class="entry-user">
                                <i class="fas fa-user"></i>
                                {{ $entry->user->name }}
                            </span>
                            <span class="entry-time">
                                <i class="fas fa-clock"></i>
                                {{ $entry->started_at->format('H:i') }} - {{ $entry->ended_at->format('H:i') }}
                            </span>
                            <span class="entry-date">
                                <i class="fas fa-calendar"></i>
                                {{ $entry->started_at->format('d M Y') }}
                                @if($entry->started_at->format('d M Y') != $entry->ended_at->format('d M Y'))
                                    - {{ $entry->ended_at->format('d M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="entry-duration">
                        {{ floor($entry->duration_seconds / 3600) }}j {{ floor(($entry->duration_seconds % 3600) / 60) }}m
                        {{ $entry->duration_seconds % 60 }}d
                        <span class="entry-duration-decimal">{{ round($entry->duration_seconds / 3600, 2) }} jam</span>
                    </div>
                </div>
            @empty
                <div style="padding: 2rem; text-align: center; color: #94a3b8;">
                    <i class="fas fa-clock" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <p>Belum ada entri waktu</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Activity Logs -->
    @if(isset($recentLogs) && $recentLogs->count() > 0)
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3><i class="fas fa-history" style="margin-right: 0.5rem;"></i>Activity Log</h3>
            <span class="text-muted">Riwayat aktivitas time tracking</span>
        </div>
        <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
            <div class="activity-log-list">
                @foreach($recentLogs as $log)
                    <div class="activity-log-item">
                        <div class="activity-log-icon {{ $log->action_color }}">
                            <i class="fas {{ $log->action_icon }}"></i>
                        </div>
                        <div class="activity-log-content">
                            <div class="activity-log-header">
                                <span class="activity-log-action">{{ $log->action_label }}</span>
                                <span class="activity-log-task">{{ $log->task->title }}</span>
                            </div>
                            <div class="activity-log-meta">
                                <span><i class="fas fa-user"></i> {{ $log->user->name }}</span>
                                <span>•</span>
                                <span><i class="fas fa-clock"></i> {{ $log->created_at->format('d M Y H:i') }}</span>
                                @if($log->duration_at_action > 0)
                                    <span>•</span>
                                    <span><i class="fas fa-stopwatch"></i> {{ $log->formatted_duration }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <style>
        .timer-card {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 50%, #a855f7 100%);
            border-radius: 20px;
            padding: 1.25rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .timer-header {
            margin-bottom: 0.75rem;
        }

        .timer-label {
            display: block;
            font-weight: 600;
            font-size: 1rem;
            opacity: 0.9;
        }

        .timer-sublabel {
            font-size: 0.875rem;
            opacity: 0.7;
        }

        .timer-display {
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .timer-display span {
            display: inline-block;
            min-width: 1.5ch;
        }

        .timer-task {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.75rem;
        }

        .timer-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .task-select {
            min-width: 250px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 10px;
            color: #1e293b;
        }

        .task-select option {
            color: #1e293b;
            background: white;
        }

        .btn-timer {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-start {
            background: white;
            color: #6366f1;
        }

        .btn-start:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-stop {
            background: #ef4444;
            color: white;
        }

        .btn-stop:hover {
            background: #dc2626;
        }

        .btn-pause {
            background: #f59e0b;
            color: white;
        }

        .btn-pause:hover {
            background: #d97706;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-change {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .stat-change.positive {
            color: #22c55e;
        }

        .stat-meta {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.blue {
            background: #dbeafe;
            color: #3b82f6;
        }

        .stat-icon.green {
            background: #dcfce7;
            color: #22c55e;
        }

        .stat-icon.purple {
            background: #f3e8ff;
            color: #a855f7;
        }

        .time-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .time-entry:hover {
            background: #f8fafc;
        }

        .time-entry:last-child {
            border-bottom: none;
        }

        .entry-task {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .entry-details {
            display: flex;
            gap: 1rem;
            font-size: 0.8rem;
            color: #64748b;
        }

        .entry-details span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .entry-duration {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
        }

        .entry-duration-decimal {
            display: block;
            font-size: 0.75rem;
            font-weight: 400;
            color: #94a3b8;
        }

        .timer-disabled-notice {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Activity Log Styles */
        .activity-log-list {
            padding: 0;
        }

        .activity-log-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .activity-log-item:last-child {
            border-bottom: none;
        }

        .activity-log-item:hover {
            background: #f8fafc;
        }

        .activity-log-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .activity-log-icon.success {
            background: #dcfce7;
            color: #166534;
        }

        .activity-log-icon.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .activity-log-icon.info {
            background: #dbeafe;
            color: #1e40af;
        }

        .activity-log-icon.primary {
            background: #e0e7ff;
            color: #4338ca;
        }

        .activity-log-icon.secondary {
            background: #f1f5f9;
            color: #64748b;
        }

        .activity-log-content {
            flex: 1;
            min-width: 0;
        }

        .activity-log-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
            flex-wrap: wrap;
        }

        .activity-log-action {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .activity-log-task {
            color: #6366f1;
            font-size: 0.85rem;
            background: #f1f5f9;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
        }

        .activity-log-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .activity-log-meta i {
            margin-right: 0.25rem;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @push('scripts')
        <script>
            // Timer functionality
            @if($runningEntry && $runningEntry->is_running)
                const startTime = new Date('{{ $runningEntry->started_at->toIso8601String() }}');

                function updateTimer() {
                    const now = new Date();
                    let diff = Math.floor((now - startTime) / 1000);
                    if (diff < 0) diff = 0;

                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;

                    document.getElementById('timerHours').textContent = hours;
                    document.getElementById('timerMinutes').textContent = minutes;
                    document.getElementById('timerSeconds').textContent = seconds;
                }

                updateTimer();
                setInterval(updateTimer, 1000);
            @endif

            // Show warning popup for members when project is on hold
            @if($project->isOnHold() && !auth()->user()->isManagerInProject($project))
                document.addEventListener('DOMContentLoaded', function() {
                    showProjectOnHoldModal('Project "{{ $project->name }}" sedang ditunda. Anda hanya dapat melihat data project.');
                });
            @endif
        </script>
    @endpush
@endsection