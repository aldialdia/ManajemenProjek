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
                            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title"
                                value="{{ old('title') }}" required>
                            @error('title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Describe the task...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                            <select id="project_id" name="project_id" class="form-control" required>
                                <option value="">Select a project</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id', request('project_id')) == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="form-group">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <select id="assigned_to" name="assigned_to" class="form-control">
                                <option value="">Unassigned</option>
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
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control"
                                value="{{ old('due_date') }}">
                            @error('due_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
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
@endsection