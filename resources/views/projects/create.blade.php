@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('projects.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Semua Proyek
        </a>
        <h1 class="page-title">Create New Project</h1>
        <p class="page-subtitle">Fill in the details to create a new project</p>
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
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6366f1;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
        cursor: pointer;
    }

    .back-link:hover {
        color: #4f46e5;
        transform: translateX(-3px);
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
