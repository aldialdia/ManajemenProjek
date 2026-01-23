@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $task->title }}</h1>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('tasks.index', ['project_id' => $task->project_id]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <a href="{{ route('tasks.calendar', ['project_id' => $task->project_id]) }}" class="btn btn-secondary">
                <i class="fas fa-calendar-alt"></i>
                Kalender
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3">
        <!-- Main Content -->
        <div style="grid-column: span 2;">
            <!-- Task Details -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-body">
                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <x-status-badge :status="$task->status" type="task" />
                        <x-status-badge :status="$task->priority" type="priority" />
                    </div>

                    @if($task->description)
                        <div class="task-description">
                            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Description</h3>
                            <p>{{ $task->description }}</p>
                        </div>
                    @else
                        <p class="text-muted">No description provided.</p>
                    @endif
                </div>
            </div>

            <!-- Attachments -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <i class="fas fa-paperclip"></i>
                    Dokumen ({{ $task->attachments->count() }})
                </div>

                <!-- File List -->
                <div class="attachments-list">
                    @forelse($task->attachments as $attachment)
                        <div class="attachment-item">
                            <div class="attachment-icon">
                                @php
                                    $ext = strtolower(pathinfo($attachment->filename ?? '', PATHINFO_EXTENSION));
                                    $iconClass = 'fa-file';
                                    $iconColor = '#64748b';

                                    if (in_array($ext, ['pdf', 'doc', 'docx', 'txt'])) {
                                        $iconClass = 'fa-file-alt';
                                        $iconColor = '#ef4444';
                                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
                                        $iconClass = 'fa-image';
                                        $iconColor = '#ec4899';
                                    } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                        $iconClass = 'fa-file-excel';
                                        $iconColor = '#22c55e';
                                    } elseif (in_array($ext, ['zip', 'rar', 'sql', 'js', 'php', 'html', 'css', 'json', 'py'])) {
                                        $iconClass = 'fa-file-code';
                                        $iconColor = '#3b82f6';
                                    }
                                @endphp
                                <i class="fas {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                            </div>
                            <div class="attachment-info">
                                <a href="{{ route('attachments.download', $attachment) }}" class="attachment-name"
                                    target="_blank">{{ $attachment->filename }}</a>

                                <div class="attachment-meta">
                                    <span>{{ $attachment->human_size }}</span>
                                    <span>•</span>
                                    <span>{{ $attachment->uploader->name }}</span>
                                    <span>•</span>
                                    <span>{{ $attachment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="attachment-actions">
                                <a href="{{ route('attachments.download', $attachment) }}" class="btn-icon-action"
                                    title="Download" target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                                @php
                                    $canDeleteAttachment = $attachment->uploaded_by === auth()->id()
                                        || $task->assigned_to === auth()->id()
                                        || auth()->user()->isManagerInProject($task->project);
                                @endphp
                                @if($canDeleteAttachment)
                                    <form action="{{ route('attachments.destroy', $attachment) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirmSubmit(this, 'Hapus file ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-action delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="attachment-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>Belum ada dokumen</p>
                        </div>
                    @endforelse
                </div>

                <!-- Upload Form - Hanya Manager, Admin, atau Assignee (tidak berlaku jika project ditunda untuk member) -->
                @auth
                    @php
                        $isManager = auth()->user()->isManagerInProject($task->project);
                        $isAssignee = $task->assigned_to === auth()->id();
                        $projectOnHold = $task->project->isOnHold();

                        // If project on hold, only manager can upload
                        // Otherwise, manager or assignee can upload
                        $canUploadAttachment = $projectOnHold
                            ? $isManager
                            : ($isManager || $isAssignee);
                    @endphp
                    @if($canUploadAttachment && $task->status->value !== 'done')
                        <div class="attachment-form-wrapper">
                            <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data"
                                class="attachment-form">
                                @csrf
                                <div class="file-input-wrapper">
                                    <input type="file" name="file" id="task-file" class="file-input" required
                                        onchange="handleTaskFileChange(this)"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.txt,.zip,.rar,.sql,.js,.php,.html,.css,.json,.py">
                                    <label for="task-file" class="file-label">
                                        <i class="fas fa-folder-open"></i> Pilih file
                                    </label>
                                    <span class="file-name-display" id="task-file-name">Tidak ada file dipilih</span>
                                    <button type="button" id="task-file-clear" class="btn-clear-file" onclick="clearTaskFile()"
                                        style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <button type="submit" id="task-upload-btn" class="btn-upload" disabled>Upload</button>
                            </form>
                            <div class="allowed-formats-hint">
                                <i class="fas fa-info-circle"></i>
                                <span><strong>Format:</strong> PDF, DOC, XLS, PPT, PNG, JPG, GIF, TXT, ZIP, RAR, SQL, JS, PHP, HTML,
                                    CSS, JSON, PY — Max 10MB</span>
                            </div>
                        </div>
                    @elseif($canUploadAttachment && $task->status->value === 'done')
                        <div class="attachment-form-wrapper">
                            <div class="task-completed-notice">
                                <i class="fas fa-check-circle"></i>
                                <span>Tugas sudah selesai. Upload file tidak tersedia.</span>
                            </div>
                        </div>
                    @elseif($projectOnHold && !$isManager && $isAssignee)
                        <div class="attachment-form-wrapper">
                            <div class="project-onhold-notice">
                                <i class="fas fa-pause-circle"></i>
                                <span>Project sedang ditunda. Upload file tidak tersedia.</span>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
            <!-- Comments -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-comments"></i>
                    Komentar ({{ $task->comments->count() }})
                </div>
                <div class="chat-container">
                    @php $lastDate = null; @endphp
                    @forelse($task->comments()->with('user')->oldest()->get() as $comment)
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
                                <p class="chat-text">
                                    {!! preg_replace('/@\[([^\]]+)\]\(\d+\)/', '<span class="mention-text">@$1</span>', e($comment->body)) !!}
                                </p>
                                <div class="chat-meta">
                                    <span class="chat-time">{{ $comment->created_at->format('H:i') }}</span>
                                    @if($isOwn)
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                            style="display: inline;" onsubmit="return confirmSubmit(this, 'Hapus komentar ini?')">
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
                            <p>Belum ada komentar</p>
                            <span>Mulai diskusi tentang tugas ini</span>
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form with @mention -->
                @auth
                    @php
                        $canComment = $task->project->isOnHold()
                            ? auth()->user()->isManagerInProject($task->project)
                            : true;
                    @endphp
                                @if($canComment)
                                    <div class="chat-input-area">
                                        @include('components.mention-comment-box', [
                                            'action' => route('tasks.comments.store', $task),
                                            'id' => 'task-' . $task->id,
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
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Task Info -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">Task Details</div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">Project</span>
                                <a href="{{ route('projects.show', $task->project) }}" class="info-link">
                                    {{ $task->project->name }}
                                </a>
                            </div>


                            <div class="info-row">
                                <span class="info-label">Assignee</span>
                                @if($task->assignee)
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
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
                                            <div class="avatar avatar-sm" style="background-image: url('{{ asset('storage/' . $task->assignee->avatar) }}'); background-size: cover; background-position: center;"></div>
                                        @else
                                            <div class="avatar avatar-sm" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);">{{ $task->assignee->initials }}</div>
                                        @endif
                                        <span>{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>

                            <div class="info-row">
                                <span class="info-label">Due Date</span>
                                @if($task->due_date)
                                    <span class="{{ $task->isOverdue() ? 'text-danger font-bold' : '' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue())
                                            <span class="badge badge-danger" style="margin-left: 0.25rem;">Overdue</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created</span>
                                <span>{{ $task->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated</span>
                                <span>{{ $task->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $isAssigneeForTimer = $task->assigned_to === auth()->id();
                        $statusValue = $task->status->value;
                        $canTrackTime = $isAssigneeForTimer && !in_array($statusValue, ['done', 'review']);
                        
                        // Get active time entry for this task (only for assignee)
                        $activeTimeEntry = null;
                        if ($isAssigneeForTimer) {
                            $activeTimeEntry = \App\Models\TimeEntry::forUser(auth()->id())
                                ->forTask($task->id)
                                ->active()
                                ->first();
                        }
                        
                        // Get recent logs for this task (visible to all members)
                        $taskLogs = \App\Models\TimeTrackingLog::forTask($task->id)
                            ->with('user')
                            ->orderByDesc('created_at')
                            ->limit(10)
                            ->get();
                        
                        // Get total time spent on this task (visible to all members)
                        $totalTimeSeconds = \App\Models\TimeEntry::forTask($task->id)
                            ->completed()
                            ->sum('duration_seconds');
                        
                        // Add running time if there's an active entry
                        $runningTimeEntry = \App\Models\TimeEntry::forTask($task->id)
                            ->active()
                            ->first();
                        if ($runningTimeEntry) {
                            $totalTimeSeconds += $runningTimeEntry->current_elapsed_seconds;
                        }
                    @endphp

                    <!-- Time Tracking & Total Time Card (Combined) -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-clock"></i>
                            @if($isAssigneeForTimer)
                                Time Tracking
                            @else
                                Waktu Pengerjaan
                            @endif
                        </div>
                        <div class="card-body">
                            @if($isAssigneeForTimer)
                                <!-- Timer Display (for assignee) -->
                                <div class="timer-display-task" id="taskTimerDisplay" style="text-align: center; margin-bottom: 1rem;">
                                    <div class="timer-value" id="timerValue" style="font-size: 2rem; font-weight: 700; color: #1e293b;">
                                        @if($activeTimeEntry)
                                            <span id="timerHours">0</span>:<span id="timerMinutes">00</span>:<span id="timerSeconds">00</span>
                                        @else
                                            00:00:00
                                        @endif
                                    </div>
                                    <div class="timer-status" style="font-size: 0.8rem; color: #64748b;">
                                        @if($activeTimeEntry && $activeTimeEntry->is_running)
                                            <span class="timer-status-badge running">
                                                <i class="fas fa-circle"></i> Sedang Berjalan
                                            </span>
                                        @else
                                            <span class="timer-status-badge idle">Belum Dimulai</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Timer Controls (for assignee) -->
                                <div class="timer-controls-task" style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                                    @if($canTrackTime)
                                        @if(!$activeTimeEntry || !$activeTimeEntry->is_running)
                                            <!-- Start Button -->
                                            <form action="{{ route('tasks.timer.start', $task) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success" style="width: 100%;">
                                                    <i class="fas fa-play"></i> Mulai
                                                </button>
                                            </form>
                                        @else
                                            <!-- Stop Button -->
                                            <form action="{{ route('time-tracking.stop', $activeTimeEntry) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="width: 100%;">
                                                    <i class="fas fa-square"></i> Berhenti
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <div class="timer-disabled-msg" style="text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                            @if(in_array($statusValue, ['done', 'review']))
                                                <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                                                Task sudah selesai
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Total Time Summary (visible to all) -->
                            <div class="timer-summary" style="@if($isAssigneeForTimer) padding-top: 1rem; border-top: 1px solid #e2e8f0; @endif">
                                <div style="text-align: center;">
                                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.25rem;">
                                        Waktu yang dihabiskan untuk task ini
                                    </div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">
                                        @php
                                            $hours = floor($totalTimeSeconds / 3600);
                                            $minutes = floor(($totalTimeSeconds % 3600) / 60);
                                            $seconds = $totalTimeSeconds % 60;
                                        @endphp
                                        {{ $hours }}j {{ $minutes }}m {{ $seconds }}d
                                    </div>
                                    @if($runningTimeEntry)
                                        <div style="font-size: 0.75rem; color: #22c55e; margin-top: 0.25rem;">
                                            <i class="fas fa-circle" style="font-size: 0.4rem; animation: pulse 1.5s infinite;"></i>
                                            Timer sedang berjalan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Card (Only for assignee) -->
                    @if($isAssigneeForTimer && $taskLogs->count() > 0)
                        <div class="card" style="margin-bottom: 1.5rem;">
                            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-history"></i>
                                Activity Log
                            </div>
                            <div class="card-body" style="padding: 0; max-height: 250px; overflow-y: auto;">
                                <div class="activity-timeline">
                                    @foreach($taskLogs as $log)
                                        <div class="activity-item">
                                            <div class="activity-icon {{ $log->action_color }}">
                                                <i class="fas {{ $log->action_icon }}"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-title">{{ $log->action_label }}</div>
                                                <div class="activity-meta">
                                                    <span>{{ $log->user->name }}</span>
                                                    <span>•</span>
                                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                                    @if($log->duration_at_action > 0)
                                                        <span>•</span>
                                                        <span>{{ $log->formatted_duration }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    @canany(['updateStatus', 'update', 'delete', 'approve'], $task)
                        <div class="card">
                            <div class="card-header">Actions</div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    @php
                                        $isManager = auth()->user()->isManagerInProject($task->project);
                                        $isAssignee = $task->assigned_to === auth()->id();
                                        $statusValue = $task->status->value;
                                    @endphp

                                    {{-- Status: Not review/done - Show "Mark as Done" for assignee/manager --}}
                                    @if(!in_array($statusValue, ['review', 'done']))
                                        @can('updateStatus', $task)
                                            @if($isAssignee && isset($activeTimeEntry) && $activeTimeEntry)
                                                {{-- If assignee has active timer, use complete route that stops timer --}}
                                                <form action="{{ route('tasks.timer.complete', $task) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" style="width: 100%;">
                                                        <i class="fas fa-check"></i>
                                                        Mark as Done
                                                    </button>
                                                </form>
                                            @else
                                                {{-- Normal Mark as Done --}}
                                                <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="review">
                                                    <button type="submit" class="btn btn-success" style="width: 100%;">
                                                        <i class="fas fa-check"></i>
                                                        Mark as Done
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    @endif

                                    {{-- Status: review (pending approval) --}}
                                    @if($statusValue === 'review')
                                        {{-- Manager/Admin sees Approve button --}}
                                        @can('approve', $task)
                                            <form action="{{ route('tasks.approve', $task) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-warning" style="width: 100%;">
                                                    <i class="fas fa-check-double"></i>
                                                    Approve Task
                                                </button>
                                            </form>
                                        @endcan

                                        {{-- Assignee/Manager can Reopen --}}
                                        @can('updateStatus', $task)
                                            <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="btn btn-secondary" style="width: 100%;">
                                                    <i class="fas fa-undo"></i>
                                                    Reopen Task
                                                </button>
                                            </form>
                                        @endcan
                                    @endif

                                    {{-- Status: done - Only Manager/Admin can Reopen --}}
                                    @if($statusValue === 'done')
                                        @if($isManager)
                                            <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="btn btn-secondary" style="width: 100%;">
                                                    <i class="fas fa-undo"></i>
                                                    Reopen Task
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @can('update', $task)
                                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary" style="width: 100%;">
                                            <i class="fas fa-edit"></i>
                                            Edit Task
                                        </a>
                                    @endcan

                                    @can('delete', $task)
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                            onsubmit="return confirmSubmit(this, 'Apakah Anda yakin ingin menghapus tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                                <i class="fas fa-trash"></i>
                                                Delete Task
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endcanany
                </div>
            </div>

            <style>
                .breadcrumb a {
                    color: #6366f1;
                    text-decoration: none;
                }

                .breadcrumb a:hover {
                    text-decoration: underline;
                }

                .task-description {
                    line-height: 1.6;
                }

                .attachment-item {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.75rem;
                    background: #f8fafc;
                    border-radius: 8px;
                    margin-bottom: 0.5rem;
                }

                .comment-item {
                    display: flex;
                    gap: 1rem;
                    margin-bottom: 1.5rem;
                }

                .comment-content {
                    flex: 1;
                }

                .comment-header {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    margin-bottom: 0.25rem;
                }

                .comment-author {
                    font-weight: 600;
                }

                .comment-body {
                    color: #374151;
                    line-height: 1.5;
                }

                .comment-form {
                    display: flex;
                    gap: 1rem;
                    margin-top: 1.5rem;
                    padding-top: 1.5rem;
                    border-top: 1px solid #e2e8f0;
                }

                .comment-input-wrapper {
                    flex: 1;
                }

                .info-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0.75rem 0;
                    border-bottom: 1px solid #e2e8f0;
                }

                .info-row:last-child {
                    border-bottom: none;
                }

                .info-label {
                    font-size: 0.875rem;
                    color: #64748b;
                }

                .info-link {
                    color: #6366f1;
                    text-decoration: none;
                }

                .info-link:hover {
                    text-decoration: underline;
                }

                .quick-actions {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }

                /* Chat Container - WhatsApp Style */
                .chat-container {
                    padding: 1rem;
                    max-height: 400px;
                    overflow-y: auto;
                    background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%);
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }

                .chat-message {
                    display: flex;
                    gap: 0.5rem;
                    max-width: 80%;
                }

                .chat-message.own {
                    align-self: flex-end;
                    flex-direction: row-reverse;
                }

                .chat-message.other {
                    align-self: flex-start;
                }

                .chat-avatar {
                    width: 28px;
                    height: 28px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 0.65rem;
                    font-weight: 600;
                    flex-shrink: 0;
                }

                .chat-bubble {
                    padding: 0.5rem 0.75rem;
                    border-radius: 14px;
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
                    font-size: 0.65rem;
                    font-weight: 600;
                    color: #6366f1;
                    margin-bottom: 0.125rem;
                }

                .chat-text {
                    margin: 0;
                    font-size: 0.8rem;
                    line-height: 1.4;
                }

                .chat-meta {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 0.375rem;
                    margin-top: 0.125rem;
                }

                .chat-time {
                    font-size: 0.6rem;
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
                    font-size: 0.6rem;
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
                    padding: 2rem 1rem;
                    text-align: center;
                    color: #94a3b8;
                }

                .chat-empty i {
                    font-size: 2rem;
                    margin-bottom: 0.5rem;
                    opacity: 0.5;
                }

                .chat-empty p {
                    font-weight: 600;
                    color: #64748b;
                    margin: 0 0 0.25rem 0;
                    font-size: 0.875rem;
                }

                .chat-empty span {
                    font-size: 0.75rem;
                }

                .chat-input-area {
                    padding: 0.875rem 1rem;
                    border-top: 1px solid #e2e8f0;
                    background: white;
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

                /* Attachments */
                .attachment-item {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    border-bottom: 1px solid #f1f5f9;
                }

                .attachment-item:last-child {
                    border-bottom: none;
                }

                .attachment-icon {
                    background: #f8fafc;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
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
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .attachment-name:hover {
                    color: #6366f1;
                }

                .attachment-meta {
                    color: #94a3b8;
                }

                .attachment-actions {
                    display: flex;
                    gap: 0.25rem;
                }

                .btn-icon-action {
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
                    color: #94a3b8;
                    padding: 2rem 1rem;
                }

                .attachment-empty i {
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

                .file-size-hint {
                    font-size: 0.75rem;
                    color: #94a3b8;
                    font-style: italic;
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

                .allowed-formats-hint {
                    display: flex;
                    align-items: flex-start;
                    gap: 0.5rem;
                    margin-top: 0.75rem;
                    padding: 0.625rem 0.875rem;
                    background: #f0f9ff;
                    border: 1px solid #bae6fd;
                    border-radius: 8px;
                    font-size: 0.75rem;
                    color: #0369a1;
                }

                .allowed-formats-hint i {
                    color: #0ea5e9;
                    margin-top: 0.125rem;
                }

                .btn-upload:disabled {
                    background: #cbd5e1;
                    cursor: not-allowed;
                    opacity: 0.6;
                }

                .btn-upload:disabled:hover {
                    background: #cbd5e1;
                }

                .task-completed-notice {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1rem;
                    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
                    border: 1px solid #86efac;
                    border-radius: 10px;
                    color: #166534;
                    font-size: 0.875rem;
                    font-weight: 500;
                }

                .task-completed-notice i {
                    font-size: 1.25rem;
                    color: #22c55e;
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

                .btn-clear-file {
                    width: 24px;
                    height: 24px;
                    border: none;
                    background: #fee2e2;
                    color: #ef4444;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 0.75rem;
                    transition: all 0.2s;
                    flex-shrink: 0;
                }

                .btn-clear-file:hover {
                    background: #fecaca;
                    color: #dc2626;
                }

                /* Attachment Options */
                .btn-lampirkan {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.625rem 1rem;
                    background: #6366f1;
                    border: none;
                    border-radius: 8px;
                    color: white;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-lampirkan:hover {
                    background: #4f46e5;
                }

                .attachment-buttons {
                    display: flex;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                }

                .attachment-options-row {
                    display: flex;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                    align-items: center;
                }

                .btn-cancel-text {
                    background: none;
                    border: none;
                    color: #94a3b8;
                    font-size: 0.875rem;
                    cursor: pointer;
                    padding: 0.5rem;
                }

                .btn-cancel-text:hover {
                    color: #64748b;
                    text-decoration: underline;
                }

                .option-btn {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.625rem 1rem;
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    color: #475569;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .option-btn:hover {
                    border-color: #6366f1;
                    color: #6366f1;
                }

                .option-btn.file i { color: #6366f1; }
                .option-btn.link i { color: #22c55e; }

                .inline-form-card {
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    overflow: hidden;
                }

                .inline-form-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0.75rem 1rem;
                    background: #f8fafc;
                    border-bottom: 1px solid #f1f5f9;
                }

                .inline-form-header span {
                    font-weight: 600;
                    color: #1e293b;
                    font-size: 0.875rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .btn-cancel {
                    background: none;
                    border: none;
                    color: #94a3b8;
                    cursor: pointer;
                    padding: 0.25rem;
                }

                .btn-cancel:hover {
                    color: #ef4444;
                }

                .inline-form-card .attachment-form {
                    padding: 1rem;
                }

                .link-form {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .form-input {
                    width: 100%;
                    padding: 0.625rem 0.875rem;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    font-size: 0.875rem;
                    outline: none;
                    transition: border-color 0.2s;
                }

                .form-input:focus {
                    border-color: #6366f1;
                    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
                }
            </style>

        <script>
            // Handle file selection
            function handleTaskFileChange(input) {
                const fileName = document.getElementById('task-file-name');
                const uploadBtn = document.getElementById('task-upload-btn');
                const clearBtn = document.getElementById('task-file-clear');

                if (input.files && input.files[0]) {
                    fileName.textContent = input.files[0].name;
                    uploadBtn.disabled = false;
                    clearBtn.style.display = 'flex';
                } else {
                    fileName.textContent = 'Tidak ada file dipilih';
                    uploadBtn.disabled = true;
                    clearBtn.style.display = 'none';
                }
            }

            // Clear file input
            function clearTaskFile() {
                const fileInput = document.getElementById('task-file');
                const fileName = document.getElementById('task-file-name');
                const uploadBtn = document.getElementById('task-upload-btn');
                const clearBtn = document.getElementById('task-file-clear');

                fileInput.value = '';
                fileName.textContent = 'Tidak ada file dipilih';
                uploadBtn.disabled = true;
                clearBtn.style.display = 'none';
            }

            // Show warning popup for members when project is on hold
            document.addEventListener('DOMContentLoaded', function() {
                @if($task->project->isOnHold() && !auth()->user()->isManagerInProject($task->project))
                    showProjectOnHoldModal('Project "{{ $task->project->name }}" sedang ditunda. Anda hanya dapat melihat data project.');
                @endif
            });
        </script>

        <style>
            /* Timer Status Badges */
            .timer-status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            
            .timer-status-badge.running {
                background: #dcfce7;
                color: #166534;
            }
            
            .timer-status-badge.running i {
                font-size: 0.5rem;
                animation: pulse 1.5s infinite;
            }
            
            .timer-status-badge.paused {
                background: #fef3c7;
                color: #92400e;
            }
            
            .timer-status-badge.idle {
                background: #f1f5f9;
                color: #64748b;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }
            
            /* Activity Timeline */
            .activity-timeline {
                padding: 0;
            }
            
            .activity-item {
                display: flex;
                gap: 0.75rem;
                padding: 0.875rem 1rem;
                border-bottom: 1px solid #f1f5f9;
                transition: background 0.15s;
            }
            
            .activity-item:last-child {
                border-bottom: none;
            }
            
            .activity-item:hover {
                background: #f8fafc;
            }
            
            .activity-icon {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                flex-shrink: 0;
            }
            
            .activity-icon.success {
                background: #dcfce7;
                color: #166534;
            }
            
            .activity-icon.warning {
                background: #fef3c7;
                color: #92400e;
            }
            
            .activity-icon.info {
                background: #dbeafe;
                color: #1e40af;
            }
            
            .activity-icon.primary {
                background: #e0e7ff;
                color: #4338ca;
            }
            
            .activity-icon.secondary {
                background: #f1f5f9;
                color: #64748b;
            }
            
            .activity-content {
                flex: 1;
                min-width: 0;
            }
            
            .activity-title {
                font-size: 0.8rem;
                font-weight: 500;
                color: #1e293b;
                margin-bottom: 0.125rem;
            }
            
            .activity-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.375rem;
                font-size: 0.7rem;
                color: #94a3b8;
            }
        </style>

        @if(isset($activeTimeEntry) && $activeTimeEntry?->is_running)
        <script>
            // Timer functionality for task detail page
            document.addEventListener('DOMContentLoaded', function() {
                const startTime = new Date('{{ $activeTimeEntry->started_at->toIso8601String() }}');
                
                function updateTaskTimer() {
                    const now = new Date();
                    let diff = Math.floor((now - startTime) / 1000);
                    if (diff < 0) diff = 0;
                    
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;
                    
                    const timerHours = document.getElementById('timerHours');
                    const timerMinutes = document.getElementById('timerMinutes');
                    const timerSeconds = document.getElementById('timerSeconds');
                    
                    if (timerHours && timerMinutes && timerSeconds) {
                        timerHours.textContent = hours;
                        timerMinutes.textContent = String(minutes).padStart(2, '0');
                        timerSeconds.textContent = String(seconds).padStart(2, '0');
                    }
                }
                
                updateTaskTimer();
                setInterval(updateTaskTimer, 1000);
            });
        </script>
        @endif
@endsection