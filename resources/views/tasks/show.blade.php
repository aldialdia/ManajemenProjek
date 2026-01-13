@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb" style="margin-bottom: 0.5rem;">
                <a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a>
                <i class="fas fa-chevron-right" style="margin: 0 0.5rem; font-size: 0.75rem; color: #94a3b8;"></i>
                <span>Task</span>
            </div>
            <h1 class="page-title">{{ $task->title }}</h1>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i>
                Edit Task
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
                    Attachments ({{ $task->attachments->count() }})
                </div>
                <div class="card-body">
                    @forelse($task->attachments as $attachment)
                        <div class="attachment-item">
                            <i class="fas fa-file"></i>
                            <span>{{ $attachment->filename }}</span>
                            <span class="text-muted text-sm">{{ $attachment->human_size }}</span>
                        </div>
                    @empty
                        <p class="text-muted" style="text-align: center;">No attachments yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Comments -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-comments"></i>
                    Comments ({{ $task->comments->count() }})
                </div>
                <div class="card-body">
                    @forelse($task->comments as $comment)
                        <div class="comment-item">
                            <div class="avatar">{{ $comment->user->initials }}</div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">{{ $comment->user->name }}</span>
                                    <span
                                        class="comment-time text-muted text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="comment-body">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted" style="text-align: center; padding: 1rem;">No comments yet. Start the discussion!
                        </p>
                    @endforelse

                    <!-- Add Comment Form -->
                    @auth
                        <form action="{{ route('comments.store', $task) }}" method="POST" class="comment-form">
                            @csrf
                            <div class="avatar avatar-sm">{{ auth()->user()->initials }}</div>
                            <div class="comment-input-wrapper">
                                <textarea name="body" class="form-control" placeholder="Write a comment..." rows="2"
                                    required></textarea>
                                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                                    <i class="fas fa-paper-plane"></i>
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    @endauth
                </div>
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
                                <div class="avatar avatar-sm">{{ $task->assignee->initials }}</div>
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

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="quick-actions">
                        @if($task->status->value !== 'done')
                            <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="btn btn-success" style="width: 100%;">
                                    <i class="fas fa-check"></i>
                                    Mark as Done
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="todo">
                                <button type="submit" class="btn btn-secondary" style="width: 100%;">
                                    <i class="fas fa-undo"></i>
                                    Reopen Task
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-edit"></i>
                            Edit Task
                        </a>

                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                <i class="fas fa-trash"></i>
                                Delete Task
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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
    </style>
@endsection