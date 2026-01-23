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

                <div class="grid grid-cols-3">
                    <x-forms.input-label label="Status" name="status" type="select" required>
                        @php $currentStatus = old('status', $project->status->value ?? $project->status); @endphp
                        <option value="new" {{ $currentStatus === 'new' ? 'selected' : '' }}>Baru</option>
                        <option value="in_progress" {{ $currentStatus === 'in_progress' ? 'selected' : '' }}>Sedang Berjalan
                        </option>
                        <option value="done" {{ $currentStatus === 'done' ? 'selected' : '' }}>Selesai</option>
                        <option value="on_hold" {{ $currentStatus === 'on_hold' ? 'selected' : '' }}>Ditunda</option>
                    </x-forms.input-label>

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
    </style>
@endsection