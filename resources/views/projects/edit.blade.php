@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Project</h1>
        <p class="page-subtitle">Update project details</p>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Project
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')

            <x-forms.input-label 
                label="Project Name" 
                name="name" 
                :value="$project->name"
                placeholder="Enter project name"
                required
            />

            <x-forms.input-label 
                label="Description" 
                name="description" 
                type="textarea"
                :value="$project->description"
                placeholder="Describe the project scope and objectives..."
            />

            <x-forms.input-label 
                label="Tujuan Proyek" 
                name="goals" 
                type="textarea"
                :value="$project->goals"
                placeholder="Jelaskan tujuan yang ingin dicapai dari proyek ini..."
            />

            <div class="grid grid-cols-3">
                <x-forms.input-label 
                    label="Status" 
                    name="status" 
                    type="select"
                    required
                >
                    @php $currentStatus = old('status', $project->status->value ?? $project->status); @endphp
                    <option value="new" {{ $currentStatus === 'new' ? 'selected' : '' }}>Baru</option>
                    <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="on_hold" {{ $currentStatus === 'on_hold' ? 'selected' : '' }}>Ditunda</option>
                    <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </x-forms.input-label>

                <x-forms.input-label 
                    label="Start Date" 
                    name="start_date" 
                    type="date"
                    :value="old('start_date', $project->start_date?->format('Y-m-d'))"
                />

                <x-forms.input-label 
                    label="End Date" 
                    name="end_date" 
                    type="date"
                    :value="old('end_date', $project->end_date?->format('Y-m-d'))"
                />
            </div>



            <div class="form-group">
                <label class="form-label">Team Members</label>
                <div class="team-select">
                    @php $selectedUsers = old('users', $project->users->pluck('id')->toArray()); @endphp
                    @foreach($users as $user)
                        <label class="team-member-option">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" 
                                {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }}>
                            <div class="avatar avatar-sm">{{ $user->initials }}</div>
                            <span>{{ $user->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Project
                </button>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .team-select {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .team-member-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .team-member-option:hover {
        border-color: #6366f1;
    }

    .team-member-option input {
        display: none;
    }

    .team-member-option:has(input:checked) {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
    }
</style>
@endsection
