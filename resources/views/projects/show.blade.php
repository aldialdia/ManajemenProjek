@extends('layouts.app')

@section('title', $project->name . ' - Overview Proyek')

@section('content')
    <!-- Main Content -->
    <div class="overview-container">
        <!-- Project Info Card -->
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-content">
                    <div class="info-card-title-row">
                        <h2 class="info-card-title">{{ $project->name }}</h2>
                        <div class="info-card-actions">
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="btn-sm btn-edit-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete', $project)
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline;"
                                    onsubmit="return confirmSubmit(this, 'Apakah Anda yakin ingin menghapus project ini? Semua tugas dalam project juga akan terhapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-delete-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    <p class="info-card-desc">{{ $project->description ?? 'Tidak ada deskripsi' }}</p>
                    @if($project->goals)
                        <p class="info-card-goals"><strong>Tujuan:</strong> {{ $project->goals }}</p>
                    @endif
                    <div class="info-card-badges">
                        @php
                            $statusValue = $project->status->value ?? $project->status;
                            $statusClass = match ($statusValue) {
                                'new' => 'badge-new',
                                'in_progress' => 'badge-inprogress',
                                'on_hold' => 'badge-hold',
                                'done' => 'badge-done',
                                default => 'badge-new'
                            };
                            $statusLabel = match ($statusValue) {
                                'new' => 'Baru',
                                'in_progress' => 'Sedang Berjalan',
                                'on_hold' => 'Ditunda',
                                'done' => 'Selesai',
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
                        
                        {{-- Button Tunda/Lanjutkan Project - Di paling kanan --}}
                        @if(auth()->user()->isManagerInProject($project) || auth()->user()->isAdmin())
                            <div class="badge-toggle-container">
                                <form action="{{ route('projects.toggle-hold', $project) }}" method="POST"
                                    onsubmit="return confirmSubmit(this, '{{ $project->isOnHold() ? 'Lanjutkan project ini?' : 'Tunda project ini? Project akan berstatus on hold.' }}')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-toggle-hold {{ $project->isOnHold() ? 'btn-resume' : 'btn-hold' }}">
                                        {{ $project->isOnHold() ? 'Lanjutkan Project' : 'Tunda Project' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
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
                @if(auth()->user()->isManagerInProject($project))
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn-add-task">
                        <i class="fas fa-plus"></i>
                        Tambah Tugas
                    </a>
                @endif
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
                                @php
                                    $colorIndex = $task->assignee->id % 4;
                                    $colors = [
                                        ['start' => '#6366f1', 'end' => '#4f46e5'],
                                        ['start' => '#f97316', 'end' => '#ea580c'],
                                        ['start' => '#22c55e', 'end' => '#16a34a'],
                                        ['start' => '#ec4899', 'end' => '#db2777'],
                                    ];
                                    $userColor = $colors[$colorIndex];
                                @endphp
                                @if($task->assignee->avatar)
                                    <div class="task-avatar" title="{{ $task->assignee->name }}" style="background-image: url('{{ asset('storage/' . $task->assignee->avatar) }}'); background-size: cover; background-position: center;"></div>
                                @else
                                    <div class="task-avatar" title="{{ $task->assignee->name }}" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);">
                                        {{ $task->assignee->initials }}
                                    </div>
                                @endif
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

        <!-- Diskusi Card -->
        <div class="discussion-card">
            <div class="discussion-card-header">
                <h3 class="discussion-card-title">
                    <i class="fas fa-comments"></i>
                    Diskusi Proyek
                </h3>
                <span class="discussion-card-count">{{ $project->comments->count() }} komentar</span>
            </div>
            <div class="chat-container">
                @php $lastDate = null; @endphp
                @forelse($project->comments()->with('user')->oldest()->get() as $comment)
                    @php 
                        $isOwn = $comment->user_id === auth()->id();
                        $commentDate = $comment->created_at->format('Y-m-d');
                        $showDateSeparator = $lastDate !== $commentDate;
                        $lastDate = $commentDate;
                        
                        // Format tanggal untuk ditampilkan
                        $today = now()->format('Y-m-d');
                        $yesterday = now()->subDay()->format('Y-m-d');
                        
                        if ($commentDate === $today) {
                            $displayDate = 'Hari Ini';
                        } elseif ($commentDate === $yesterday) {
                            $displayDate = 'Kemarin';
                        } else {
                            $displayDate = $comment->created_at->format('d/m/Y');
                        }
                        
                        // Warna konsisten berdasarkan user ID
                        $colorIndex = $comment->user_id % 4;
                        $colors = [
                            ['start' => '#6366f1', 'end' => '#4f46e5'], // Indigo
                            ['start' => '#f97316', 'end' => '#ea580c'], // Orange
                            ['start' => '#22c55e', 'end' => '#16a34a'], // Green
                            ['start' => '#ec4899', 'end' => '#db2777'], // Pink
                        ];
                        $userColor = $colors[$colorIndex];
                    @endphp
                    
                    {{-- Date Separator --}}
                    @if($showDateSeparator)
                        <div class="chat-date-separator">
                            <span class="chat-date-badge">{{ $displayDate }}</span>
                        </div>
                    @endif
                    
                    <div class="chat-message {{ $isOwn ? 'own' : 'other' }}">
                        @if(!$isOwn)
                            @if($comment->user->avatar)
                                <div class="chat-avatar" style="background-image: url('{{ asset('storage/' . $comment->user->avatar) }}'); background-size: cover; background-position: center;">
                            @else
                                <div class="chat-avatar" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);">
                                    {{ $comment->user->initials }}
                            @endif
                            </div>
                        @endif
                        <div class="chat-bubble {{ $isOwn ? 'own' : 'other' }}">
                            @if(!$isOwn)
                                <span class="chat-author">{{ $comment->user->name }}</span>
                            @endif
                            <p class="chat-text">{!! preg_replace('/@\[([^\]]+)\]\(\d+\)/', '<span class="mention-text">@$1</span>', e($comment->body)) !!}</p>
                            <div class="chat-meta">
                                <span class="chat-time">{{ $comment->created_at->format('H:i') }}</span>
                                @if($isOwn)
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" style="display: inline;"
                                        onsubmit="return confirmSubmit(this, 'Hapus komentar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="chat-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                        <div class="chat-empty">
                            <i class="fas fa-comment-dots"></i>
                            <p>Belum ada diskusi</p>
                            <span>Mulai diskusi tentang proyek ini</span>
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form with @mention -->
                @auth
                    @php
                        $canComment = $project->isOnHold()
                            ? auth()->user()->isManagerInProject($project)
                            : true;
                    @endphp
                    @if($canComment)
                        <div class="chat-input-area">
                            @include('components.mention-comment-box', [
                                'action' => route('projects.comments.store', $project),
                                'id' => 'project-' . $project->id,
                                'placeholder' => 'Tulis pesan... (@ untuk mention)'
                            ])
                        </div>
                    @else
                        <div class="chat-input-area">
                            <div class="project-onhold-notice">
                                <i class="fas fa-pause-circle"></i>
                                <span>Project sedang ditunda. Komentar tidak tersedia.</span>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>


            <!-- Team Card - di bagian bawah -->
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
                        @php
                            // Warna konsisten berdasarkan user ID
                            $colorIndex = $user->id % 4;
                            $colors = [
                                ['start' => '#6366f1', 'end' => '#4f46e5'], // Indigo
                                ['start' => '#f97316', 'end' => '#ea580c'], // Orange
                                ['start' => '#22c55e', 'end' => '#16a34a'], // Green
                                ['start' => '#ec4899', 'end' => '#db2777'], // Pink
                            ];
                            $userColor = $colors[$colorIndex];
                        @endphp
                        <div class="team-member">
                            @if($user->avatar)
                                <div class="team-member-avatar" style="background-image: url('{{ asset('storage/' . $user->avatar) }}'); background-size: cover; background-position: center;">
                                </div>
                            @else
                                <div class="team-member-avatar" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);">
                                    {{ $user->initials }}
                                </div>
                            @endif
                            <div class="team-member-info">
                                <span class="team-member-name">{{ $user->name }}</span>
                                <span class="team-member-role">{{ ucfirst($user->pivot->role ?? 'Member') }}</span>
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

        <style>
            /* Overview Container */
            .overview-container {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
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
                align-items: flex-start;
            }

            .back-btn {
                width: 44px;
                height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                color: #475569;
                text-decoration: none;
                transition: all 0.2s;
                flex-shrink: 0;
            }

            .back-btn:hover {
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
                color: #1e293b;
                transform: translateX(-2px);
            }

            .back-btn i {
                font-size: 1rem;
            }

            .info-card-content {
                flex: 1;
            }

            .info-card-title-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.5rem;
            }

            .info-card-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #1e293b;
                margin: 0;
            }

            .info-card-actions {
                display: flex;
                gap: 0.5rem;
            }

            .btn-sm {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s;
                font-size: 0.8rem;
            }

            .btn-edit-sm {
                background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                color: white;
                text-decoration: none;
                box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
            }

            .btn-edit-sm:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(99, 102, 241, 0.4);
                color: white;
            }

            .btn-delete-sm {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
            }

            .btn-delete-sm:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            }

            /* Button Toggle Hold - Text Button */
            .btn-toggle-hold {
                display: inline-flex;
                align-items: center;
                padding: 0.5rem 1rem;
                border: none;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                color: white;
            }

            .btn-hold {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                box-shadow: 0 2px 6px rgba(249, 115, 22, 0.3);
            }

            .btn-hold:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(249, 115, 22, 0.4);
                background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            }

            .btn-resume {
                background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                box-shadow: 0 2px 6px rgba(34, 197, 94, 0.3);
            }

            .btn-resume:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(34, 197, 94, 0.4);
                background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            }

            .info-card-desc {
                color: #64748b;
                font-size: 0.875rem;
                margin: 0;
                line-height: 1.5;
            }

            .info-card-goals {
                color: #475569;
                font-size: 0.875rem;
                margin: 0.75rem 0 0 0;
                line-height: 1.5;
                padding: 0.75rem;
                background: #f8fafc;
                border-radius: 8px;
                border-left: 3px solid #6366f1;
            }

            .info-card-badges {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                align-items: center;
                margin-top: 1rem;
            }

            .badge-toggle-container {
                margin-left: auto;
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

            .badge-new {
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                color: #64748b;
            }

            .badge-inprogress {
                background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
                color: #2563eb;
            }

            .badge-done {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
                color: #059669;
            }

            .badge-hold {
                background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
                color: #ea580c;
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
                border: none;
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
                border: none;
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
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 0.75rem;
                padding: 1rem 1.5rem;
            }

            .team-member {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.875rem 1rem;
                background: #f8fafc;
                border-radius: 12px;
                transition: all 0.2s;
            }

            .team-member:hover {
                background: #f1f5f9;
                transform: translateY(-1px);
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

            /* Discussion Card */
            .discussion-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }

            .discussion-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid #f1f5f9;
            }

            .discussion-card-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
                font-weight: 600;
                color: #1e293b;
                margin: 0;
            }

            .discussion-card-title i {
                color: #6366f1;
            }

            .discussion-card-count {
                font-size: 0.8rem;
                font-weight: 600;
                color: #6366f1;
                background: #eef2ff;
                padding: 0.375rem 0.75rem;
                border-radius: 20px;
            }

            /* Chat Container - WhatsApp Style */
            .chat-container {
                padding: 1rem 1.5rem;
                max-height: 450px;
                overflow-y: auto;
                /* Berikan background unik */
                background-color: #f1effe;
                background-image:  radial-gradient(#6366f1 0.5px, transparent 0.5px), radial-gradient(#6366f1 0.5px, #f1effe 0.5px);
                background-size: 20px 20px;
                background-position: 0 0, 10px 10px;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .chat-message {
                display: flex;
                gap: 0.5rem;
                max-width: 75%;
            }

            .chat-message.own {
                align-self: flex-end;
                flex-direction: row-reverse;
            }

            .chat-message.other {
                align-self: flex-start;
            }

            .chat-avatar {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 0.7rem;
                font-weight: 600;
                flex-shrink: 0;
            }

            .chat-bubble {
                padding: 0.625rem 0.875rem;
                border-radius: 16px;
                position: relative;
                word-wrap: break-word;
            }

            .chat-bubble.own {
                background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
                color: white;
                border-bottom-right-radius: 4px;
            }

            .chat-bubble.other {
                background: white;
                color: #1e293b;
                border-bottom-left-radius: 4px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .chat-author {
                display: block;
                font-size: 0.7rem;
                font-weight: 600;
                color: #6366f1;
                margin-bottom: 0.25rem;
            }

            .chat-text {
                margin: 0;
                font-size: 0.875rem;
                line-height: 1.4;
            }

            .chat-meta {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.5rem;
                margin-top: 0.25rem;
            }

            .chat-time {
                font-size: 0.65rem;
                opacity: 0.7;
            }

            .chat-bubble.own .chat-time {
                color: rgba(255, 255, 255, 0.8);
            }

            .chat-bubble.other .chat-time {
                color: #94a3b8;
            }

            .chat-delete {
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                font-size: 0.65rem;
                opacity: 0.6;
                transition: opacity 0.2s;
            }

            .chat-bubble.own .chat-delete {
                color: white;
            }

            .chat-delete:hover {
                opacity: 1;
            }

            .chat-empty {
                padding: 3rem 1.5rem;
                text-align: center;
                color: #94a3b8;
                background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%);
            }

            .chat-empty i {
                font-size: 2.5rem;
                margin-bottom: 0.75rem;
                opacity: 0.5;
            }

            .chat-empty p {
                font-weight: 600;
                color: #64748b;
                margin: 0 0 0.25rem 0;
            }

            .chat-empty span {
                font-size: 0.8rem;
            }

            .chat-input-area {
                padding: 1rem 1.5rem;
                border-top: 1px solid #e2e8f0;
                background: white;
            }

            .project-onhold-notice {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem;
                background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
                border: 1px solid #fde047;
                border-radius: 10px;
                color: #854d0e;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .project-onhold-notice i {
                font-size: 1.25rem;
                color: #ca8a04;
            }

            /* Mention text - hanya warna, tanpa background */
            .mention-text {
                color: #6366f1;
                font-weight: 600;
            }

            .chat-bubble.own .mention-text {
                color: #fde047;
                font-weight: 700;
            }

            /* Chat Date Separator - WhatsApp Style */
            .chat-date-separator {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0.75rem 0;
                width: 100%;
            }

            .chat-date-badge {
                background: rgba(225, 229, 234, 0.92);
                color: #54656f;
                font-size: 0.7rem;
                font-weight: 500;
                padding: 0.35rem 0.75rem;
                border-radius: 8px;
                box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.13);
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

                .chat-message {
                    max-width: 85%;
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

                .chat-message {
                    max-width: 90%;
                }
            }

            /* Attachments */
            .attachments-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }

            .attachments-list {
                padding: 0.5rem 1.5rem;
            }

            .attachment-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem 0;
                border-bottom: 1px solid #f1f5f9;
            }

            .attachment-item:last-child {
                border-bottom: none;
            }

            .attachment-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: #f8fafc;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                flex-shrink: 0;
            }

            .attachment-info {
                flex: 1;
                min-width: 0;
            }

            .attachment-name {
                display: block;
                font-weight: 500;
                color: #1e293b;
                text-decoration: none;
                margin-bottom: 0.125rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .attachment-name:hover {
                color: #6366f1;
            }

            .attachment-meta {
                font-size: 0.75rem;
                color: #94a3b8;
                display: flex;
                gap: 0.5rem;
            }

            .attachment-actions {
                display: flex;
                gap: 0.5rem;
            }

            .btn-icon-action {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                background: transparent;
                color: #94a3b8;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                font-size: 0.875rem;
            }

            .btn-icon-action:hover {
                background: #f1f5f9;
                color: #1e293b;
            }

            .btn-icon-action.delete:hover {
                background: #fee2e2;
                color: #ef4444;
            }

            .attachment-empty {
                text-align: center;
                padding: 2rem;
                color: #94a3b8;
            }

            .attachment-empty i {
                font-size: 2rem;
                margin-bottom: 0.5rem;
                opacity: 0.5;
            }

            .attachment-empty p {
                margin: 0;
                font-size: 0.875rem;
            }

            .attachment-form-wrapper {
                padding: 1rem 1.5rem;
                border-top: 1px solid #f1f5f9;
                background: #f8fafc;
            }

            .attachment-form {
                display: flex;
                gap: 1rem;
                align-items: center;
            }

            .file-input-wrapper {
                flex: 1;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .file-input {
                display: none;
            }

            .file-label {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                color: #64748b;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }

            .file-label:hover {
                border-color: #cbd5e1;
                color: #1e293b;
            }

            .file-name-display {
                font-size: 0.875rem;
                color: #64748b;
            }

            .btn-upload {
                padding: 0.5rem 1rem;
                background: #6366f1;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                transition: background 0.2s;
            }

            .btn-upload:hover {
                background: #4f46e5;
            }
            /* Documents Card */
            .documents-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                overflow: hidden;
                margin-bottom: 1.25rem;
            }

            .documents-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid #f1f5f9;
            }

            .documents-card-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
                font-weight: 600;
                color: #1e293b;
                margin: 0;
            }

            .documents-card-title i {
                color: #ef4444; /* Red for PDF/Files */
            }

            .btn-view-docs {
                font-size: 0.8rem;
                color: #6366f1;
                text-decoration: none;
                font-weight: 500;
            }

            .documents-list-preview {
                padding: 1rem;
            }

            .doc-preview-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.75rem;
                border-radius: 12px;
                transition: background 0.2s;
                text-decoration: none;
            }

            .doc-preview-item:hover {
                background: #f8fafc;
            }

            .doc-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                flex-shrink: 0;
            }

            .doc-file {
                background: #fef2f2;
                color: #ef4444;
            }

            .doc-content {
                background: #eff6ff;
                color: #3b82f6;
            }

            .doc-info {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .doc-title {
                font-weight: 500;
                color: #1e293b;
                font-size: 0.9rem;
                text-decoration: none;
            }

            .doc-meta {
                font-size: 0.75rem;
                color: #64748b;
            }

            .btn-doc-arrow {
                color: #cbd5e1;
                font-size: 0.8rem;
            }

            .docs-empty {
                text-align: center;
                padding: 2rem;
                color: #94a3b8;
                font-size: 0.9rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
            }

            .docs-empty i {
                font-size: 2rem;
                margin-bottom: 0.5rem;
                opacity: 0.5;
            }

            .documents-card-actions {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .btn-upload-doc {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.5rem 0.875rem;
                background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                color: white;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s;
            }

            .btn-upload-doc:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
                color: white;
            }

            .btn-upload-first {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.5rem 1rem;
                background: #6366f1;
                color: white;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.2s;
            }

            .btn-upload-first:hover {
                background: #4f46e5;
                color: white;
            }

            /* Simplified Document Item for Overview */
            .doc-preview-item-simple {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.75rem;
                border-radius: 12px;
                transition: background 0.2s;
                border-bottom: 1px solid #f1f5f9;
            }

            .doc-preview-item-simple:last-child {
                border-bottom: none;
            }

            .doc-preview-item-simple:hover {
                background: #f8fafc;
            }

            .doc-preview-left {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex: 1;
                min-width: 0;
            }

            .doc-preview-actions {
                display: flex;
                gap: 0.5rem;
                flex-shrink: 0;
            }

            .doc-action-btn {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                background: #f1f5f9;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                font-size: 0.8rem;
            }

            .doc-action-btn.download {
                color: #6366f1;
            }

            .doc-action-btn.download:hover {
                background: #eef2ff;
                color: #4f46e5;
            }

            .doc-action-btn.delete {
                color: #94a3b8;
            }

            .doc-action-btn.delete:hover {
                background: #fee2e2;
                color: #ef4444;
            }
        </style>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    @if($project->isOnHold() && !auth()->user()->isManagerInProject($project))
                        // Show warning popup for members when project is on hold
                        showProjectOnHoldModal('Project "{{ $project->name }}" sedang ditunda. Anda hanya dapat melihat data project.');
                    @endif
                });
            </script>
        @endpush
@endsection