@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $client->name ?? 'Client Name' }}</h1>
            <p class="page-subtitle">{{ $client->company ?? 'Company' }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('clients.edit', $client ?? 1) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Client
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3">
        <!-- Client Info Card -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-header">
                <i class="fas fa-building" style="margin-right: 0.5rem;"></i>
                Client Information
            </div>
            <div class="card-body">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div class="avatar avatar-lg" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto;">
                        {{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-top: 1rem;">
                        {{ $client->name ?? 'Client Name' }}
                    </h3>
                    @if($client->company ?? false)
                        <p class="text-muted text-sm">{{ $client->company }}</p>
                    @endif
                </div>

                <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    @if($client->email ?? false)
                        <div style="margin-bottom: 1rem;">
                            <label class="text-muted text-xs"
                                style="display: block; text-transform: uppercase; letter-spacing: 0.05em;">Email</label>
                            <a href="mailto:{{ $client->email }}" style="color: var(--primary); text-decoration: none;">
                                <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>
                                {{ $client->email }}
                            </a>
                        </div>
                    @endif

                    @if($client->phone ?? false)
                        <div style="margin-bottom: 1rem;">
                            <label class="text-muted text-xs"
                                style="display: block; text-transform: uppercase; letter-spacing: 0.05em;">Phone</label>
                            <a href="tel:{{ $client->phone }}" style="color: var(--dark); text-decoration: none;">
                                <i class="fas fa-phone" style="margin-right: 0.5rem;"></i>
                                {{ $client->phone }}
                            </a>
                        </div>
                    @endif

                    @if($client->address ?? false)
                        <div>
                            <label class="text-muted text-xs"
                                style="display: block; text-transform: uppercase; letter-spacing: 0.05em;">Address</label>
                            <p style="display: flex; gap: 0.5rem;">
                                <i class="fas fa-map-marker-alt" style="color: var(--secondary);"></i>
                                {{ $client->address }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Client Statistics & Projects -->
        <div style="grid-column: span 2;">
            <!-- Stats -->
            <div class="grid grid-cols-3" style="margin-bottom: 1.5rem;">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                            {{ $client->projects_count ?? $client->projects->count() ?? 0 }}
                        </div>
                        <p class="text-muted text-sm">Total Projects</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                            {{ $activeProjects ?? 0 }}
                        </div>
                        <p class="text-muted text-sm">Active Projects</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                            {{ $completedProjects ?? 0 }}
                        </div>
                        <p class="text-muted text-sm">Completed</p>
                    </div>
                </div>
            </div>

            <!-- Projects List -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        <i class="fas fa-folder" style="margin-right: 0.5rem;"></i>
                        Projects
                    </span>
                    <a href="{{ route('projects.create') }}?client_id={{ $client->id ?? '' }}" class="btn btn-primary"
                        style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($client->projects) && $client->projects->count() > 0)
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->projects as $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('projects.show', $project) }}"
                                                    style="color: var(--dark); text-decoration: none; font-weight: 500;">
                                                    {{ $project->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <x-status-badge :status="$project->status" type="project" />
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div
                                                        style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                                        <div
                                                            style="height: 100%; width: {{ $project->progress ?? 0 }}%; background: var(--primary); border-radius: 3px;">
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">{{ $project->progress ?? 0 }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-sm">{{ $project->end_date?->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary"
                                                    style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem; color: var(--secondary);">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No projects yet</p>
                            <a href="{{ route('projects.create') }}?client_id={{ $client->id ?? '' }}" class="btn btn-primary"
                                style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Create First Project
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection