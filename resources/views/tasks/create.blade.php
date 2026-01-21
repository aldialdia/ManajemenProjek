@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create New Task</h1>
            <p class="page-subtitle">Add a new task to your project</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <!-- Left Column -->
                    <div>
                        <div class="form-group">
                            <label class="form-label">Project</label>
                            <div class="project-display">
                                <i class="fas fa-folder"></i>
                                <span>{{ $project->name }}</span>
                            </div>
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                        </div>

                        <div class="form-group">
                            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title"
                                value="{{ old('title') }}" required>
                            @error('title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Describe the task..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="form-group">
                            <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                            <select id="assigned_to" name="assigned_to" class="form-control" required>
                                <option value="">-- Pilih anggota --</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2" style="gap: 1rem;">
                            <div class="form-group">
                                <label for="priority" class="form-label">Priority</label>
                                <select id="priority" name="priority" class="form-control">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                        Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="todo" {{ old('status', 'todo') == 'todo' ? 'selected' : '' }}>To Do
                                    </option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="review" {{ old('status') == 'review' ? 'selected' : '' }}>Review</option>
                                    <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" id="due_date" name="due_date" class="form-control"
                                value="{{ old('due_date') }}" 
                                min="{{ date('Y-m-d') }}" 
                                @if($project->end_date) max="{{ $project->end_date->format('Y-m-d') }}" @endif
                                required>
                            @if($project->end_date)
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Deadline project: {{ $project->end_date->format('d M Y') }}
                                </small>
                            @endif
                            @error('due_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($parentTasks->count() > 0)
                        <div class="form-group">
                            <label for="parent_task_id" class="form-label">Sub-task dari</label>
                            <select id="parent_task_id" name="parent_task_id" class="form-control">
                                <option value="">-- Tidak ada (Task utama) --</option>
                                @foreach($parentTasks as $parentTask)
                                    <option value="{{ $parentTask->id }}" {{ old('parent_task_id') == $parentTask->id ? 'selected' : '' }}>
                                        {{ $parentTask->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika task ini merupakan bagian dari task lain</small>
                        </div>
                        @endif
                    </div>
                </div>

                <div
                    style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Task
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .project-display {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .project-display i {
            color: #6366f1;
            font-size: 1rem;
        }

        .project-display span {
            font-weight: 600;
            color: #1e293b;
        }
    </style>
@endsection