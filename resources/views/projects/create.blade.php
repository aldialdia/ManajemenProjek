@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Project</h1>
        <p class="page-subtitle">Fill in the details to create a new project</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary" title="Kembali ke Dashboard">
            <i class="fas fa-home"></i>
            Dashboard
        </a>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary" title="Kembali ke Semua Proyek">
            <i class="fas fa-folder-open"></i>
            Semua Proyek
        </a>
    </div>
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
        
        // Date Validation Logic
        function updateEndDateMin() {
            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
                if (endDateInput.value && endDateInput.value < startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                }
            }
        }

        startDateInput.addEventListener('change', function() {
            updateEndDateMin();
            validateForm();
        });

        // Initialize min date
        updateEndDateMin();
        
        function validateForm() {
            const nameValid = nameInput.value.trim() !== '';
            const descriptionValid = descriptionInput.value.trim() !== '';
            const startDateValid = startDateInput.value !== '';
            const endDateValid = endDateInput.value !== '';
            
            const allValid = nameValid && descriptionValid && startDateValid && endDateValid;
            
            submitBtn.disabled = !allValid;
        }
        
        // Add event listeners
        nameInput.addEventListener('input', validateForm);
        descriptionInput.addEventListener('input', validateForm);
        endDateInput.addEventListener('change', validateForm);
        
        // Initial validation
        validateForm();
    });
</script>
@endsection
