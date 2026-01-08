@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">View and manage your account information</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    </div>

    <div class="grid grid-cols-3">
        <!-- Profile Card -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="avatar avatar-lg"
                    style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                    @if(auth()->user()->avatar ?? false)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">
                    {{ auth()->user()->name ?? 'User Name' }}
                </h3>
                <p class="text-muted" style="font-size: 0.875rem;">
                    {{ auth()->user()->email ?? 'user@example.com' }}
                </p>
                <div style="margin-top: 1rem;">
                    <span class="badge badge-primary">
                        {{ ucfirst(auth()->user()->role ?? 'member') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                Account Information
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Full Name</label>
                        <p style="font-weight: 500;">{{ auth()->user()->name ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Email
                            Address</label>
                        <p style="font-weight: 500;">{{ auth()->user()->email ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Role</label>
                        <p style="font-weight: 500;">{{ ucfirst(auth()->user()->role ?? 'Member') }}</p>
                    </div>
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Member
                            Since</label>
                        <p style="font-weight: 500;">{{ auth()->user()->created_at?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-4" style="margin-top: 1.5rem;">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                    {{ $projectsCount ?? 0 }}
                </div>
                <p class="text-muted text-sm">Projects Assigned</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                    {{ $tasksCount ?? 0 }}
                </div>
                <p class="text-muted text-sm">Total Tasks</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                    {{ $completedTasksCount ?? 0 }}
                </div>
                <p class="text-muted text-sm">Tasks Completed</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">
                    {{ $pendingTasksCount ?? 0 }}
                </div>
                <p class="text-muted text-sm">Tasks Pending</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
            Recent Tasks
        </div>
        <div class="card-body">
            @if(isset($recentTasks) && $recentTasks->count() > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->project->name ?? '-' }}</td>
                                    <td>
                                        <x-status-badge :status="$task->status" type="task" />
                                    </td>
                                    <td>{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: var(--secondary);">
                    <i class="fas fa-tasks" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <p>No recent tasks</p>
                </div>
            @endif
        </div>
    </div>
@endsection