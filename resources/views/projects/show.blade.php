@extends('layouts.app')

@section('title', $project->name . ' - Overview Proyek')

@section('content')
    <!-- Page Header -->
    <div class="project-header">
        <div class="header-left">
            <a href="{{ route('projects.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="project-main-title">{{ $project->name }} - Overview Proyek</h1>
                <p class="project-date">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="header-right">
            <button class="header-btn">
                <i class="fas fa-bell"></i>
                <span class="notification-dot"></span>
            </button>
            @auth
                <div class="user-profile">
                    <div class="avatar avatar-sm">
                        {{ auth()->user()->initials }}
                    </div>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                </div>
            @endauth
        </div>
    </div>

    <!-- Project Card -->
    <div class="project-card">
        <div class="project-card-header">
            <div class="project-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="project-info">
                <h2 class="project-title">{{ $project->name }}</h2>
                <p class="project-description">{{ $project->description ?? 'No description' }}</p>
                <div class="project-badges">
                    <span class="badge badge-aktif">
                        {{ $project->status === 'active' ? 'Aktif' : ($project->status === 'completed' ? 'Selesai' : 'On Hold') }}
                    </span>
                    @if($project->users->first())
                        <span class="badge badge-role">
                            <i class="fas fa-user"></i>
                            Role:
                            {{ ucfirst($project->users->first()->pivot->role ?? $project->users->first()->role ?? 'Member') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Progress</span>
                    <span class="stat-value">{{ $project->progress }}%</span>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Tugas Selesai</span>
                    <span
                        class="stat-value">{{ $project->tasks->where('status', 'done')->count() }}/{{ $project->tasks->count() }}</span>
                </div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Anggota Tim</span>
                    <span class="stat-value">{{ $project->users->count() }}</span>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Deadline</span>
                    <span class="stat-value">{{ $project->end_date?->format('Y-m-d') ?? 'TBD' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3" style="margin-top: 1.5rem;">
        <!-- Progress Section -->
        <div style="grid-column: span 2;">
            <div class="card">
                <div class="card-header">
                    <span>Progress Proyek</span>
                </div>
                <div class="card-body">
                    <div class="progress-section">
                        <div class="progress-header">
                            <span class="progress-label">Penyelesaian keseluruhan</span>
                            <span class="progress-percent">{{ $project->progress }}%</span>
                        </div>
                        <div class="progress-bar-lg">
                            <div class="progress-fill" style="width: {{ $project->progress }}%;"></div>
                        </div>
                        <div class="progress-dates">
                            <span>Mulai: {{ $project->start_date?->format('Y-m-d') ?? 'Not set' }}</span>
                            <span>Target: {{ $project->end_date?->format('Y-m-d') ?? 'Not set' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Daftar Tugas</span>
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary"
                        style="padding: 0.5rem 1rem;">
                        <i class="fas fa-plus"></i> Tambah Tugas
                    </a>
                </div>
                <div class="card-body" style="padding: 0;">
                    @forelse($project->tasks as $task)
                        <div class="task-row">
                            <div class="task-row-left">
                                <input type="checkbox" {{ $task->status->value === 'done' ? 'checked disabled' : '' }}>
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="task-row-title">
                                        {{ $task->title }}
                                    </a>
                                    <div class="task-row-meta">
                                        <x-status-badge :status="$task->status" type="task" />
                                        <x-status-badge :status="$task->priority" type="priority" />
                                    </div>
                                </div>
                            </div>
                            <div class="task-row-right">
                                @if($task->assignee)
                                    <div class="avatar avatar-sm" title="{{ $task->assignee->name }}">
                                        {{ $task->assignee->initials }}
                                    </div>
                                @endif
                                @if($task->due_date)
                                    <span class="text-sm {{ $task->isOverdue() ? 'text-danger' : 'text-muted' }}">
                                        {{ $task->due_date->format('M d') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="padding: 2rem; text-align: center; color: #64748b;">
                            <i class="fas fa-tasks" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                            <p>Belum ada tugas. Tambahkan tugas pertama!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div>
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Anggota Tim</span>
                    <span class="team-count">{{ $project->users->count() }} anggota</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    @forelse($project->users as $user)
                        <div class="team-row">
                            <div class="avatar"
                                style="background: linear-gradient(135deg, {{ ['#f97316', '#3b82f6', '#22c55e', '#8b5cf6'][($loop->index % 4)] }} 0%, {{ ['#ea580c', '#1d4ed8', '#16a34a', '#7c3aed'][($loop->index % 4)] }} 100%);">
                                {{ $user->initials }}
                            </div>
                            <div>
                                <div class="team-name">{{ $user->name }}</div>
                                <div class="team-role text-muted text-sm">{{ ucfirst($user->pivot->role ?? $user->role) }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 1.5rem; text-align: center; color: #64748b;">
                            <p>Belum ada anggota tim</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Project Details -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">Detail Proyek</div>
                <div class="card-body">
                    @if($project->client)
                        <div class="detail-item">
                            <i class="fas fa-building"></i>
                            <div>
                                <span class="detail-label">Klien</span>
                                <span class="detail-value">{{ $project->client->name }}</span>
                            </div>
                        </div>
                    @endif

                    @if($project->budget)
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>
                                <span class="detail-label">Budget</span>
                                <span class="detail-value">Rp {{ number_format($project->budget, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="detail-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="detail-label">Timeline</span>
                            <span class="detail-value">{{ $project->start_date?->format('d M Y') ?? 'TBD' }} -
                                {{ $project->end_date?->format('d M Y') ?? 'TBD' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: white;
            color: var(--dark);
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            background: #f1f5f9;
        }

        .project-main-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .project-date {
            color: #64748b;
            font-size: 0.875rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-btn {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: white;
            border: none;
            color: var(--dark);
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .project-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .project-card-header {
            display: flex;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .project-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .project-info {
            flex: 1;
        }

        .project-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .project-description {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }

        .project-badges {
            display: flex;
            gap: 0.5rem;
        }

        .badge-aktif {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-role {
            background: #f1f5f9;
            color: #3b82f6;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-role i {
            margin-right: 0.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
        }

        .stat-blue {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .stat-blue .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .stat-blue .stat-value {
            color: #1d4ed8;
        }

        .stat-green {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .stat-green .stat-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .stat-green .stat-value {
            color: #16a34a;
        }

        .stat-orange {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        }

        .stat-orange .stat-icon {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .stat-orange .stat-value {
            color: #ea580c;
        }

        .stat-red {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        .stat-red .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-red .stat-value {
            color: #dc2626;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .progress-section {
            padding: 0.5rem 0;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .progress-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .progress-percent {
            font-weight: 600;
            color: var(--dark);
        }

        .progress-bar-lg {
            height: 12px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-lg .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 999px;
            transition: width 0.3s;
        }

        .progress-dates {
            display: flex;
            justify-content: space-between;
            margin-top: 0.75rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .task-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .task-row:last-child {
            border-bottom: none;
        }

        .task-row:hover {
            background: #f8fafc;
        }

        .task-row-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .task-row-left input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .task-row-title {
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
        }

        .task-row-title:hover {
            color: var(--primary);
        }

        .task-row-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }

        .task-row-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .team-count {
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 500;
        }

        .team-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .team-row:last-child {
            border-bottom: none;
        }

        .team-name {
            font-weight: 500;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item i {
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 0.875rem;
        }

        .detail-label {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
        }

        .detail-value {
            font-weight: 500;
            color: var(--dark);
        }
    </style>
@endsection