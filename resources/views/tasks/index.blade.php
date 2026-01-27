@extends('layouts.app')

@section('title', $project ? 'Tugas - ' . $project->name : 'Tasks')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $project ? 'Tugas' : 'Semua Tugas' }}</h1>
            <p class="page-subtitle">
                @if($project)
                    Kelola tugas untuk project <strong>{{ $project->name }}</strong>
                @else
                    Lihat semua tugas dari project Anda
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($project)
                <a href="{{ route('tasks.kanban', ['project_id' => $project->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-columns"></i>
                    Kanban View
                </a>
                @if(auth()->user()->isManagerInProject($project))
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Tugas
                    </a>
                @endif
            @else
                <a href="{{ route('tasks.kanban') }}" class="btn btn-secondary">
                    <i class="fas fa-columns"></i>
                    Kanban View
                </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form action="{{ route('tasks.index') }}" method="GET" class="filter-form">
                @if($project)
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                @endif
                <div class="filter-row">
                    <div class="filter-group" style="flex: 2;">
                        <input type="text" name="search" class="form-control" placeholder="Cari tugas..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="priority" class="form-control">
                            <option value="">Semua Prioritas</option>
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
                        <th style="width: 45%;">Tugas</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Assignee</th>
                        <th>Due Date</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        {{-- Parent Task --}}
                        <tr class="task-row parent-task">
                            <td>
                                <a href="{{ route('tasks.show', $task) }}" class="task-link">
                                    {{ $task->title }}
                                </a>
                                @if($task->subtasks->count() > 0)
                                    <span class="subtask-count">{{ $task->subtasks->count() }} sub-task</span>
                                @endif
                            </td>
                            <td>
                                <x-status-badge :status="$task->status" type="task" />
                            </td>
                            <td>
                                <x-status-badge :status="$task->priority" type="priority" />
                            </td>
                            <td>
                                @if($task->assignees->count() > 0)
                                    <div class="assignees-stack">
                                        @foreach($task->assignees->take(3) as $assignee)
                                            @php
                                                $colorIndex = $assignee->id % 4;
                                                $colors = [
                                                    ['start' => '#6366f1', 'end' => '#4f46e5'],
                                                    ['start' => '#f97316', 'end' => '#ea580c'],
                                                    ['start' => '#22c55e', 'end' => '#16a34a'],
                                                    ['start' => '#ec4899', 'end' => '#db2777'],
                                                ];
                                                $userColor = $colors[$colorIndex];
                                            @endphp
                                            @if($assignee->avatar)
                                                <div class="avatar avatar-sm stacked" style="background-image: url('{{ asset('storage/' . $assignee->avatar) }}'); background-size: cover; background-position: center;" title="{{ $assignee->name }}"></div>
                                            @else
                                                <div class="avatar avatar-sm stacked" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);" title="{{ $assignee->name }}">{{ $assignee->initials }}</div>
                                            @endif
                                        @endforeach
                                        @if($task->assignees->count() > 3)
                                            <div class="avatar avatar-sm stacked more" title="{{ $task->assignees->count() - 3 }} more">+{{ $task->assignees->count() - 3 }}</div>
                                        @endif
                                        @if($task->assignees->count() == 1)
                                            <span class="assignee-name">{{ $task->assignees->first()->name }}</span>
                                        @endif
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
                                    @can('update', $task)
                                        <a href="{{ route('tasks.edit', $task) }}" class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $task)
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                            onsubmit="return confirmSubmit(this, 'Hapus tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon text-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        {{-- Subtasks --}}
                        @foreach($task->subtasks as $subtask)
                            <tr class="task-row subtask-row">
                                <td>
                                    <div class="subtask-indent">
                                        <i class="fas fa-level-up-alt fa-rotate-90 subtask-icon"></i>
                                        <a href="{{ route('tasks.show', $subtask) }}" class="task-link">
                                            {{ $subtask->title }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <x-status-badge :status="$subtask->status" type="task" />
                                </td>
                                <td>
                                    <x-status-badge :status="$subtask->priority" type="priority" />
                                </td>
                                <td>
                                    @if($subtask->assignees->count() > 0)
                                        <div class="assignees-stack">
                                            @foreach($subtask->assignees->take(3) as $assignee)
                                                @php
                                                    $colorIndex = $assignee->id % 4;
                                                    $colors = [
                                                        ['start' => '#6366f1', 'end' => '#4f46e5'],
                                                        ['start' => '#f97316', 'end' => '#ea580c'],
                                                        ['start' => '#22c55e', 'end' => '#16a34a'],
                                                        ['start' => '#ec4899', 'end' => '#db2777'],
                                                    ];
                                                    $userColor = $colors[$colorIndex];
                                                @endphp
                                                @if($assignee->avatar)
                                                    <div class="avatar avatar-sm stacked" style="background-image: url('{{ asset('storage/' . $assignee->avatar) }}'); background-size: cover; background-position: center;" title="{{ $assignee->name }}"></div>
                                                @else
                                                    <div class="avatar avatar-sm stacked" style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);" title="{{ $assignee->name }}">{{ $assignee->initials }}</div>
                                                @endif
                                            @endforeach
                                            @if($subtask->assignees->count() > 3)
                                                <div class="avatar avatar-sm stacked more" title="{{ $subtask->assignees->count() - 3 }} more">+{{ $subtask->assignees->count() - 3 }}</div>
                                            @endif
                                            @if($subtask->assignees->count() == 1)
                                                <span class="assignee-name">{{ $subtask->assignees->first()->name }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subtask->due_date)
                                        <span class="{{ $subtask->isOverdue() ? 'text-danger font-bold' : '' }}">
                                            {{ $subtask->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        @can('update', $subtask)
                                            <a href="{{ route('tasks.edit', $subtask) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $subtask)
                                            <form action="{{ route('tasks.destroy', $subtask) }}" method="POST"
                                                onsubmit="return confirmSubmit(this, 'Hapus sub-task ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon text-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-tasks" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                                <p class="text-muted">Tidak ada tugas ditemukan</p>
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

        .subtask-count {
            margin-left: 0.5rem;
            padding: 0.15rem 0.5rem;
            background: #e0e7ff;
            color: #4f46e5;
            font-size: 0.7rem;
            border-radius: 10px;
            font-weight: 500;
        }

        .assignees-stack {
            display: flex;
            align-items: center;
        }

        .assignees-stack .avatar.stacked {
            margin-left: -8px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .assignees-stack .avatar.stacked:first-child {
            margin-left: 0;
        }

        .assignees-stack .avatar.more {
            background: #e2e8f0;
            color: #475569;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .assignees-stack .assignee-name {
            margin-left: 0.5rem;
            font-size: 0.85rem;
            color: #334155;
        }

        .subtask-row {
            background: #f8fafc;
        }

        .subtask-row:hover {
            background: #f1f5f9;
        }

        .subtask-indent {
            display: flex;
            align-items: center;
            padding-left: 1.5rem;
        }

        .subtask-icon {
            color: #94a3b8;
            margin-right: 0.5rem;
            font-size: 0.8rem;
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

    @if($project && $project->isOnHold())
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showProjectOnHoldModal('Project "{{ $project->name }}" sedang ditunda. Tugas-tugas tidak dapat dikerjakan saat ini.');
            });
        </script>
        @endpush
    @endif
@endsection