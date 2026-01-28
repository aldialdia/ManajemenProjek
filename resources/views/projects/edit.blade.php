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

                <x-forms.input-label label="Project Name" name="name" :value="$project->name"
                    placeholder="Enter project name" required />

                <x-forms.input-label label="Description" name="description" type="textarea" :value="$project->description"
                    placeholder="Describe the project scope and objectives..." />

                <x-forms.input-label label="Tujuan Proyek" name="goals" type="textarea" :value="$project->goals"
                    placeholder="Jelaskan tujuan yang ingin dicapai dari proyek ini..." />

                <div class="form-group">
                    <label class="form-label">Tipe Proyek <span class="text-danger">*</span></label>
                    @php
                        $currentType = old('type', $project->type?->value ?? 'non_rbb');
                    @endphp
                    <div class="type-selector">
                        <label class="type-option">
                            <input type="radio" name="type" value="rbb" {{ $currentType == 'rbb' ? 'checked' : '' }} required>
                            <span class="type-info">
                                <span class="type-icon rbb"><i class="fas fa-building"></i></span>
                                <span class="type-label">RBB</span>
                            </span>
                            <span class="checkmark"><i class="fas fa-check"></i></span>
                        </label>
                        <label class="type-option">
                            <input type="radio" name="type" value="non_rbb" {{ $currentType == 'non_rbb' ? 'checked' : '' }}
                                required>
                            <span class="type-info">
                                <span class="type-icon non-rbb"><i class="fas fa-folder"></i></span>
                                <span class="type-label">Non-RBB</span>
                            </span>
                            <span class="checkmark"><i class="fas fa-check"></i></span>
                        </label>
                    </div>
                    @error('type')
                        <span class="error-message"
                            style="display: flex; align-items: center; gap: 0.5rem; color: #dc2626; background: #fef2f2; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fecaca; margin-top: 0.5rem;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="grid grid-cols-2">
                    <x-forms.input-label label="Start Date" name="start_date" type="date" :value="old('start_date', $project->start_date?->format('Y-m-d'))" />

                    <x-forms.input-label label="End Date" name="end_date" type="date" :value="old('end_date', $project->end_date?->format('Y-m-d'))" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');

            if (startDateInput && endDateInput) {
                // Date Validation Logic
                function updateEndDateMin() {
                    if (startDateInput.value) {
                        endDateInput.min = startDateInput.value;
                        if (endDateInput.value && endDateInput.value < startDateInput.value) {
                            endDateInput.value = startDateInput.value;
                        }
                    }
                }

                startDateInput.addEventListener('change', function () {
                    updateEndDateMin();
                });

                // Initialize min date
                updateEndDateMin();
            }
        });
    </script>
    <style>
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
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

        .type-option input[type="radio"]:checked+.type-info+.checkmark {
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
@endsection