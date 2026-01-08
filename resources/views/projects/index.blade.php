@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Projects</h1>
            <p class="page-subtitle">Manage all your projects in one place</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Project
        </a>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form action="{{ route('projects.index') }}" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="search" class="form-control" placeholder="Search projects..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="client_id" class="form-control">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'client_id']))
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-3">
        @forelse($projects as $project)
            <div class="card project-card">
                <div class="card-body">
                    <div class="project-card-header">
                        <x-status-badge :status="$project->status" type="project" />
                        <div class="dropdown" id="projectMenu{{ $project->id }}">
                            <button class="btn-icon" onclick="toggleDropdown('projectMenu{{ $project->id }}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('projects.show', $project) }}" class="dropdown-item">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('projects.edit', $project) }}" class="dropdown-item">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <h3 class="project-card-title">
                        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                    </h3>

                    @if($project->client)
                        <p class="project-card-client">
                            <i class="fas fa-building"></i>
                            {{ $project->client->name }}
                        </p>
                    @endif

                    @if($project->description)
                        <p class="project-card-desc">{{ Str::limit($project->description, 80) }}</p>
                    @endif

                    <div class="project-card-progress">
                        <div class="progress-header">
                            <span>Progress</span>
                            <span class="font-bold">{{ $project->progress }}%</span>
                        </div>
                        <div class="progress-bar-lg">
                            <div class="progress-fill" style="width: {{ $project->progress }}%;"></div>
                        </div>
                    </div>

                    <div class="project-card-footer">
                        <div class="project-stats">
                            <span><i class="fas fa-tasks"></i> {{ $project->tasks->count() }} tasks</span>
                            <span><i class="fas fa-users"></i> {{ $project->users->count() }} members</span>
                        </div>
                        @if($project->end_date)
                            <span class="project-deadline {{ $project->end_date->isPast() ? 'text-danger' : '' }}">
                                <i class="fas fa-calendar"></i>
                                {{ $project->end_date->format('M d, Y') }}
                            </span>
                        @endif
                    </div>

                    <div class="project-card-team">
                        @foreach($project->users->take(4) as $user)
                            <div class="avatar avatar-sm" title="{{ $user->name }}">
                                {{ $user->initials }}
                            </div>
                        @endforeach
                        @if($project->users->count() > 4)
                            <div class="avatar avatar-sm avatar-more">
                                +{{ $project->users->count() - 4 }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: span 3;">
                <i class="fas fa-folder-open"></i>
                <h3>No Projects Found</h3>
                <p>Get started by creating your first project</p>
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Project
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $projects->withQueryString()->links() }}
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
            min-width: 150px;
        }

        .project-card {
            display: flex;
            flex-direction: column;
        }

        .project-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .project-card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .project-card-title a {
            color: #1e293b;
            text-decoration: none;
        }

        .project-card-title a:hover {
            color: #6366f1;
        }

        .project-card-client {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .project-card-client i {
            margin-right: 0.5rem;
        }

        .project-card-desc {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .project-card-progress {
            margin-bottom: 1rem;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .progress-bar-lg {
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-lg .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border-radius: 999px;
        }

        .project-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .project-stats {
            display: flex;
            gap: 1rem;
        }

        .project-stats i {
            margin-right: 0.25rem;
        }

        .project-card-team {
            display: flex;
            gap: -8px;
        }

        .project-card-team .avatar {
            margin-left: -8px;
            border: 2px solid white;
        }

        .project-card-team .avatar:first-child {
            margin-left: 0;
        }

        .avatar-more {
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.65rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection