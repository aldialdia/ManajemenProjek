@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tasks</h1>
            <p class="page-subtitle">View and manage all tasks</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('tasks.kanban') }}" class="btn btn-secondary">
                <i class="fas fa-columns"></i>
                Kanban View
            </a>
            @if(request('project_id'))
                <a href="{{ route('tasks.create', ['project_id' => request('project_id')]) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    New Task
                </a>
            @else
                <a href="{{ route('projects.index') }}" class="btn btn-primary" title="Pilih project terlebih dahulu">
                    <i class="fas fa-plus"></i>
                    New Task
                </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form action="{{ route('tasks.index') }}" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="search" class="form-control" placeholder="Search tasks..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <select name="project_id" class="form-control">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="priority" class="form-control">
                            <option value="">All Priority</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Task</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assignee</th>
                        <th>Due Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('tasks.show', $task) }}" class="task-link">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $task->project) }}" class="text-muted"
                                    style="text-decoration: none;">
                                    {{ $task->project->name }}
                                </a>
                            </td>
                            <td>
                                <x-status-badge :status="$task->status" type="task" />
                            </td>
                            <td>
                                <x-status-badge :status="$task->priority" type="priority" />
                            </td>
                            <td>
                                @if($task->assignee)
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="avatar avatar-sm">{{ $task->assignee->initials }}</div>
                                        <span>{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($task->due_date)
                                    <span class="{{ $task->isOverdue() ? 'text-danger font-bold' : '' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon text-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-tasks" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                                <p class="text-muted">No tasks found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $tasks->withQueryString()->links() }}
        </div>
    @endif

    <style>
        .filter-form .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 120px;
        }

        .task-link {
            font-weight: 500;
            color: #1e293b;
            text-decoration: none;
        }

        .task-link:hover {
            color: #6366f1;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-icon.text-danger:hover {
            background: #fee2e2;
            color: #ef4444;
        }
    </style>
@endsection