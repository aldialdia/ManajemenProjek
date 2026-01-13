@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Project</h1>
        <p class="page-subtitle">Fill in the details to create a new project</p>
    </div>
    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Projects
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('projects.store') }}" method="POST" id="createProjectForm">
            @csrf

            <x-forms.input-label 
                label="Project Name" 
                name="name" 
                placeholder="Enter project name"
                required
            />

            <x-forms.input-label 
                label="Description" 
                name="description" 
                type="textarea"
                placeholder="Describe the project scope and objectives..."
            />

            <x-forms.input-label 
                label="Tujuan Proyek" 
                name="goals" 
                type="textarea"
                placeholder="Jelaskan tujuan yang ingin dicapai dari proyek ini..."
            />

            <div class="grid grid-cols-2">
                <x-forms.input-label 
                    label="Start Date" 
                    name="start_date" 
                    type="date"
                    :value="old('start_date', date('Y-m-d'))"
                    min="{{ date('Y-m-d') }}"
                />

                <x-forms.input-label 
                    label="End Date" 
                    name="end_date" 
                    type="date"
                />
            </div>



            <div class="form-group">
                <label class="form-label">Team Members</label>
                <div class="team-select">
                    @foreach($users as $user)
                        <label class="team-member-option">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" 
                                {{ in_array($user->id, old('users', [])) ? 'checked' : '' }}>
                            <div class="avatar avatar-sm">{{ $user->initials }}</div>
                            <span>{{ $user->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fas fa-save"></i>
                    Create Project
                </button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
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

    .team-member-option input:checked + .avatar {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
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

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createProjectForm');
        const submitBtn = document.getElementById('submitBtn');
        
        const nameInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const teamCheckboxes = document.querySelectorAll('input[name="users[]"]');
        
        function validateForm() {
            const nameValid = nameInput.value.trim() !== '';
            const descriptionValid = descriptionInput.value.trim() !== '';
            const startDateValid = startDateInput.value !== '';
            const endDateValid = endDateInput.value !== '';
            const teamSelected = Array.from(teamCheckboxes).some(cb => cb.checked);
            
            const allValid = nameValid && descriptionValid && startDateValid && endDateValid && teamSelected;
            
            submitBtn.disabled = !allValid;
        }
        
        // Add event listeners
        nameInput.addEventListener('input', validateForm);
        descriptionInput.addEventListener('input', validateForm);
        startDateInput.addEventListener('change', validateForm);
        endDateInput.addEventListener('change', validateForm);
        teamCheckboxes.forEach(cb => cb.addEventListener('change', validateForm));
        
        // Initial validation
        validateForm();
    });
</script>
@endsection
