@extends('layouts.app')

@section('title', $project->name . ' - Overview Proyek')

@section('content')
    <!-- Page Header -->
    <div class="page-header-overview">
        <div class="header-left">
            <a href="{{ route('projects.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="project-main-title">{{ $project->name }}</h1>
                <p class="project-date">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-edit">
                <i class="fas fa-edit"></i>
                Edit Project
            </a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="overview-grid">
        <!-- Left Column -->
        <div class="overview-main">
            <!-- Project Info Card -->
            <div class="info-card">
                <div class="info-card-header">
                    <div class="project-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="info-card-content">
                        <h2 class="info-card-title">{{ $project->name }}</h2>
                        <p class="info-card-desc">{{ $project->description ?? 'Tidak ada deskripsi' }}</p>
                    </div>
                </div>
                <div class="info-card-badges">
                    @php
                        $statusValue = $project->status->value ?? $project->status;
                        $statusClass = match($statusValue) {
                            'active' => 'badge-active',
                            'completed' => 'badge-completed',
                            'on_hold' => 'badge-hold',
                            'cancelled' => 'badge-cancelled',
                            default => 'badge-hold'
                        };
                        $statusLabel = match($statusValue) {
                            'active' => 'Aktif',
                            'completed' => 'Selesai',
                            'on_hold' => 'Ditunda',
                            'cancelled' => 'Dibatalkan',
                            default => 'Unknown'
                        };
                    @endphp
                    <span class="badge-status {{ $statusClass }}">
                        <i class="fas fa-circle"></i>
                        {{ $statusLabel }}
                    </span>
                    <span class="badge-date">
                        <i class="fas fa-calendar"></i>
                        {{ $project->start_date?->format('d M Y') ?? 'TBD' }} -
                        {{ $project->end_date?->format('d M Y') ?? 'TBD' }}
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-row">
                <div class="stat-box stat-progress">
                    <div class="stat-box-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-box-info">
                        <span class="stat-box-label">Progress</span>
                        <span class="stat-box-value">{{ $project->progress }}%</span>
                    </div>
                </div>
                <div class="stat-box stat-tasks">
                    <div class="stat-box-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-box-info">
                        <span class="stat-box-label">Tugas</span>
                        <span
                            class="stat-box-value">{{ $project->tasks->where('status', 'done')->count() }}/{{ $project->tasks->count() }}</span>
                    </div>
                </div>
                <div class="stat-box stat-team">
                    <div class="stat-box-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-box-info">
                        <span class="stat-box-label">Anggota</span>
                        <span class="stat-box-value">{{ $project->users->count() }}</span>
                    </div>
                </div>
                <div class="stat-box stat-deadline">
                    <div class="stat-box-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-box-info">
                        <span class="stat-box-label">Deadline</span>
                        <span class="stat-box-value">{{ $project->end_date?->format('d M') ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-card">
                <div class="progress-card-header">
                    <span class="progress-card-title">Progress Proyek</span>
                    <span class="progress-card-percent">{{ $project->progress }}%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $project->progress }}%;"></div>
                </div>
                <div class="progress-card-dates">
                    <span><i class="fas fa-play"></i> Mulai: {{ $project->start_date?->format('d M Y') ?? '-' }}</span>
                    <span><i class="fas fa-flag-checkered"></i> Target:
                        {{ $project->end_date?->format('d M Y') ?? '-' }}</span>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="tasks-card">
                <div class="tasks-card-header">
                    <h3 class="tasks-card-title">
                        <i class="fas fa-tasks"></i>
                        Daftar Tugas
                    </h3>
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn-add-task">
                        <i class="fas fa-plus"></i>
                        Tambah Tugas
                    </a>
                </div>
                <div class="tasks-list">
                    @forelse($project->tasks as $task)
                        <div class="task-item">
                            <div class="task-item-left">
                                <div class="task-checkbox {{ $task->status->value === 'done' ? 'checked' : '' }}">
                                    @if($task->status->value === 'done')
                                        <i class="fas fa-check"></i>
                                    @endif
                                </div>
                                <div class="task-item-info">
                                    <a href="{{ route('tasks.show', $task) }}"
                                        class="task-item-title {{ $task->status->value === 'done' ? 'completed' : '' }}">
                                        {{ $task->title }}
                                    </a>
                                    <div class="task-item-badges">
                                        <x-status-badge :status="$task->status" type="task" />
                                        <x-status-badge :status="$task->priority" type="priority" />
                                    </div>
                                </div>
                            </div>
                            <div class="task-item-right">
                                @if($task->assignee)
                                    <div class="task-avatar" title="{{ $task->assignee->name }}">
                                        {{ $task->assignee->initials }}
                                    </div>
                                @endif
                                @if($task->due_date)
                                    <span class="task-date {{ $task->isOverdue() ? 'overdue' : '' }}">
                                        {{ $task->due_date->format('M d') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="tasks-empty">
                            <i class="fas fa-clipboard-list"></i>
                            <p>Belum ada tugas</p>
                            <span>Tambahkan tugas pertama untuk memulai</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column - Team -->
        <div class="overview-sidebar">
            <div class="team-card">
                <div class="team-card-header">
                    <h3 class="team-card-title">
                        <i class="fas fa-users"></i>
                        Anggota Tim
                    </h3>
                    <span class="team-card-count">{{ $project->users->count() }} anggota</span>
                </div>
                <div class="team-list">
                    @forelse($project->users as $user)
                        <div class="team-member">
                            <div class="team-member-avatar"
                                style="background: linear-gradient(135deg, {{ ['#6366f1', '#f97316', '#22c55e', '#ec4899'][($loop->index % 4)] }} 0%, {{ ['#4f46e5', '#ea580c', '#16a34a', '#db2777'][($loop->index % 4)] }} 100%);">
                                {{ $user->initials }}
                            </div>
                            <div class="team-member-info">
                                <span class="team-member-name">{{ $user->name }}</span>
                                <span
                                    class="team-member-role">{{ ucfirst($user->pivot->role ?? $user->role ?? 'Member') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="team-empty">
                            <i class="fas fa-user-plus"></i>
                            <p>Belum ada anggota</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Page Header */
        .page-header-overview {
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
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: white;
            color: #475569;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
        }

        .back-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .project-main-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .project-date {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Overview Grid */
        .overview-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 1.5rem;
            align-items: start;
        }

        .overview-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 0;
        }

        .overview-sidebar {
            position: sticky;
            top: 1rem;
        }

        /* Info Card */
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .info-card-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .project-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .info-card-content {
            flex: 1;
        }

        .info-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.25rem 0;
        }

        .info-card-desc {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
            line-height: 1.5;
        }

        .info-card-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-status i {
            font-size: 0.5rem;
        }

        .badge-active {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #15803d;
        }

        .badge-completed {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
        }

        .badge-hold {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #b45309;
        }

        .badge-cancelled {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .badge-date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            background: #f1f5f9;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #475569;
        }

        .badge-date i {
            color: #6366f1;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat-box {
            background: white;
            border-radius: 14px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-box-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: white;
        }

        .stat-progress .stat-box-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .stat-tasks .stat-box-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .stat-team .stat-box-icon {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .stat-deadline .stat-box-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-box-info {
            display: flex;
            flex-direction: column;
        }

        .stat-box-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        .stat-box-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Progress Card */
        .progress-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .progress-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .progress-card-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        .progress-card-percent {
            font-size: 1.25rem;
            font-weight: 700;
            color: #3b82f6;
        }

        .progress-bar-container {
            height: 10px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        .progress-card-dates {
            display: flex;
            justify-content: space-between;
            margin-top: 0.875rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .progress-card-dates i {
            margin-right: 0.375rem;
        }

        /* Tasks Card */
        .tasks-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .tasks-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tasks-card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .tasks-card-title i {
            color: #6366f1;
        }

        .btn-add-task {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-add-task:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
            color: white;
        }

        .tasks-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-item:hover {
            background: #f8fafc;
        }

        .task-item-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .task-checkbox {
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            transition: all 0.2s;
        }

        .task-checkbox.checked {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-color: #22c55e;
        }

        .task-item-info {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .task-item-title {
            font-weight: 500;
            color: #1e293b;
            text-decoration: none;
            transition: color 0.15s;
        }

        .task-item-title:hover {
            color: #6366f1;
        }

        .task-item-title.completed {
            color: #94a3b8;
            text-decoration: line-through;
        }

        .task-item-badges {
            display: flex;
            gap: 0.5rem;
        }

        .task-item-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .task-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .task-date {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        .task-date.overdue {
            color: #ef4444;
        }

        .tasks-empty {
            padding: 3rem 1.5rem;
            text-align: center;
            color: #94a3b8;
        }

        .tasks-empty i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.5;
        }

        .tasks-empty p {
            font-weight: 600;
            color: #64748b;
            margin: 0 0 0.25rem 0;
        }

        .tasks-empty span {
            font-size: 0.8rem;
        }

        /* Team Card */
        .team-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .team-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .team-card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .team-card-title i {
            color: #f97316;
        }

        .team-card-count {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6366f1;
            background: #eef2ff;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
        }

        .team-list {
            max-height: 450px;
            overflow-y: auto;
        }

        .team-member {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .team-member:last-child {
            border-bottom: none;
        }

        .team-member:hover {
            background: #f8fafc;
        }

        .team-member-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .team-member-info {
            display: flex;
            flex-direction: column;
        }

        .team-member-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .team-member-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        .team-empty {
            padding: 2rem 1.5rem;
            text-align: center;
            color: #94a3b8;
        }

        .team-empty i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .team-empty p {
            margin: 0;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .overview-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .team-card {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .page-header-overview {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
@endsection