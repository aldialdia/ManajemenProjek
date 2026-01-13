@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $user->name ?? 'User Name' }}</h1>
            <p class="page-subtitle">Team Member Profile</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            @if(auth()->user()->role ?? '' === 'admin')
                <a href="{{ route('users.edit', $user ?? 1) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit User
                </a>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3">
        <!-- Profile Card -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="avatar avatar-lg"
                    style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                    @if($user->avatar ?? false)
                        <img src="{{ $user->avatar }}" alt="Avatar"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">
                    {{ $user->name ?? 'User Name' }}
                </h3>
                <p class="text-muted" style="font-size: 0.875rem;">
                    {{ $user->email ?? 'user@example.com' }}
                </p>
                <div style="margin-top: 1rem;">
                    @php
                        $roleColors = [
                            'admin' => 'badge-danger',
                            'manager' => 'badge-warning',
                            'member' => 'badge-info',
                        ];
                    @endphp
                    <span class="badge {{ $roleColors[$user->role ?? 'member'] ?? 'badge-secondary' }}">
                        {{ ucfirst($user->role ?? 'member') }}
                    </span>
                </div>
                <div class="text-muted text-sm" style="margin-top: 1rem;">
                    Member since {{ $user->created_at?->format('M Y') ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div style="grid-column: span 2;">
            <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">
                            {{ $projectsCount ?? 0 }}
                        </div>
                        <p class="text-muted text-xs">Projects</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--info);">
                            {{ $tasksCount ?? 0 }}
                        </div>
                        <p class="text-muted text-xs">Total Tasks</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--success);">
                            {{ $completedTasks ?? 0 }}
                        </div>
                        <p class="text-muted text-xs">Completed</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--warning);">
                            {{ $pendingTasks ?? 0 }}
                        </div>
                        <p class="text-muted text-xs">Pending</p>
                    </div>
                </div>
            </div>

            <!-- Assigned Projects -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-folder" style="margin-right: 0.5rem;"></i>
                    Assigned Projects
                </div>
                <div class="card-body">
                    @if(isset($user->projects) && $user->projects->count() > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($user->projects as $project)
                                <a href="{{ route('projects.show', $project) }}"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f1f5f9; border-radius: 8px; text-decoration: none; color: var(--dark); font-size: 0.875rem;">
                                    <span
                                        style="width: 8px; height: 8px; border-radius: 50%; background: {{ $project->color ?? 'var(--primary)' }};"></span>
                                    {{ $project->name }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-sm" style="text-align: center; padding: 1rem;">No projects assigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tasks -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-tasks" style="margin-right: 0.5rem;"></i>
            Assigned Tasks
        </div>
        <div class="card-body">
            @if(isset($user->tasks) && $user->tasks->count() > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->tasks->take(10) as $task)
                                <tr>
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}"
                                            style="color: var(--dark); text-decoration: none; font-weight: 500;">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="text-muted text-sm">{{ $task->project->name ?? '-' }}</td>
                                    <td>
                                        <x-status-badge :status="$task->priority" type="priority" />
                                    </td>
                                    <td>
                                        <x-status-badge :status="$task->status" type="task" />
                                    </td>
                                    <td class="text-sm">{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: var(--secondary);">
                    <i class="fas fa-clipboard-list" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <p>No tasks assigned</p>
                </div>
            @endif
        </div>
    </div>
@endsection