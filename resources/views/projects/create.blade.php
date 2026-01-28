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

            <div class="form-group">
                <label class="form-label">Tipe Proyek <span class="text-danger">*</span></label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="type" value="rbb" {{ old('type', 'rbb') == 'rbb' ? 'checked' : '' }} required>
                        <span class="type-info">
                            <span class="type-icon rbb"><i class="fas fa-building"></i></span>
                            <span class="type-label">RBB</span>
                        </span>
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="non_rbb" {{ old('type') == 'non_rbb' ? 'checked' : '' }} required>
                        <span class="type-info">
                            <span class="type-icon non-rbb"><i class="fas fa-folder"></i></span>
                            <span class="type-label">Non-RBB</span>
                        </span>
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                    </label>
                </div>
                @error('type')
                    <span class="error-message" style="display: flex; align-items: center; gap: 0.5rem; color: #dc2626; background: #fef2f2; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fecaca; margin-top: 0.5rem;">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </span>
                @enderror
            </div>

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

    /* Type Selector Styles */
    .type-selector {
        display: flex;
        gap: 1rem;
    }

    .type-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
    }

    .type-option:hover {
        border-color: #6366f1;
        background: #f5f3ff;
    }

    .type-option input[type="radio"] {
        display: none;
    }

    .type-option input[type="radio"]:checked + .type-info + .checkmark {
        background: #6366f1;
        border-color: #6366f1;
        color: white;
    }

    .type-option:has(input:checked) {
        border-color: #6366f1;
        background: #f5f3ff;
    }

    .type-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .type-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: white;
    }

    .type-icon.rbb {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .type-icon.non-rbb {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .type-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.95rem;
    }

    .type-option .checkmark {
        width: 22px;
        height: 22px;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: transparent;
        transition: all 0.2s ease;
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
