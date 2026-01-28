@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Task</h1>
            <p class="page-subtitle">Update task details</p>
        </div>
        <a href="{{ route('tasks.show', $task ?? 1) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Task
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('tasks.update', $task ?? 1) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <!-- Left Column -->
                    <div>
                        <div class="form-group">
                            <label class="form-label">Project</label>
                            <div class="project-display">
                                <i class="fas fa-folder"></i>
                                <span>{{ $task->project->name }}</span>
                            </div>
                            <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                        </div>

                        <div class="form-group">
                            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title"
                                value="{{ old('title', $task->title ?? '') }}" required>
                            @error('title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Describe the task...">{{ old('description', $task->description ?? '') }}</textarea>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="form-group">
                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                            @php
                                $currentAssigneeIds = old('assignees', $task->assignees->pluck('id')->toArray());
                            @endphp
                            <div class="assignee-selector">
                                @foreach($users ?? [] as $user)
                                    <label class="assignee-option">
                                        <input type="checkbox" name="assignees[]" value="{{ $user->id }}" 
                                            {{ in_array($user->id, $currentAssigneeIds) ? 'checked' : '' }}>
                                        <span class="assignee-info">
                                            <span class="assignee-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            <span class="assignee-name">{{ $user->name }}</span>
                                        </span>
                                        <span class="checkmark"><i class="fas fa-check"></i></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('assignees')
                                <span class="error-message" style="display: flex; align-items: center; gap: 0.5rem; color: #dc2626; background: #fef2f2; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fecaca; margin-top: 0.5rem;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Pilih satu atau lebih anggota tim</small>
                        </div>

                        <div class="grid grid-cols-2" style="gap: 1rem;">
                            <div class="form-group">
                                <label for="priority" class="form-label">Priority</label>
                                <select id="priority" name="priority" class="form-control">
                                    <option value="low" {{ old('priority', $task->priority?->value ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority?->value ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority?->value ?? '') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $task->priority?->value ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="todo" {{ old('status', $task->status?->value ?? '') == 'todo' ? 'selected' : '' }}>
                                        To Do</option>
                                    <option value="in_progress" {{ old('status', $task->status?->value ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="review" {{ old('status', $task->status?->value ?? '') == 'review' ? 'selected' : '' }}>Review</option>
                                    <option value="done" {{ old('status', $task->status?->value ?? '') == 'done' ? 'selected' : '' }}>
                                        Done</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control"
                                value="{{ old('due_date', $task->due_date?->format('Y-m-d') ?? '') }}"
                                min="{{ date('Y-m-d') }}" @if($task->project->end_date)
                                max="{{ $task->project->end_date->format('Y-m-d') }}" @endif>
                            @if($task->project->end_date)
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Deadline project:
                                    {{ $task->project->end_date->format('d M Y') }}
                                </small>
                            @endif
                            <small id="parent-deadline-info" class="text-muted" style="display: none; color: #f59e0b;">
                                <i class="fas fa-exclamation-triangle"></i> <span></span>
                            </small>
                            @error('due_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($parentTasks->count() > 0 && !$task->subtasks->count())
                            <div class="form-group">
                                <label for="parent_task_id" class="form-label">Sub-task dari</label>
                                <select id="parent_task_id" name="parent_task_id" class="form-control">
                                    <option value="" data-due-date="">-- Tidak ada (Task utama) --</option>
                                    @foreach($parentTasks as $parentTask)
                                        <option value="{{ $parentTask->id }}" 
                                                data-due-date="{{ $parentTask->due_date?->format('Y-m-d') }}"
                                                data-due-date-label="{{ $parentTask->due_date?->format('d M Y') }}"
                                                {{ old('parent_task_id', $task->parent_task_id) == $parentTask->id ? 'selected' : '' }}>
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
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('tasks.show', $task ?? 1) }}" class="btn btn-secondary">Cancel</a>
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

        /* Multi-select Assignee Styles */
        .assignee-selector {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .assignee-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0;
        }

        .assignee-option:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .assignee-option input[type="checkbox"] {
            display: none;
        }

        .assignee-option input[type="checkbox"]:checked + .assignee-info + .checkmark {
            background: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        .assignee-option input[type="checkbox"]:checked ~ .assignee-info .assignee-avatar {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .assignee-option:has(input:checked) {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .assignee-info {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex: 1;
        }

        .assignee-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s ease;
        }

        .assignee-name {
            font-weight: 500;
            color: #334155;
            font-size: 0.9rem;
        }

        .checkmark {
            width: 22px;
            height: 22px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: transparent;
            transition: all 0.2s ease;
        }

        .assignee-selector::-webkit-scrollbar {
            width: 6px;
        }

        .assignee-selector::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .assignee-selector::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .assignee-selector::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const parentSelect = document.getElementById('parent_task_id');
            const dueDateInput = document.getElementById('due_date');
            const parentDeadlineInfo = document.getElementById('parent-deadline-info');
            const projectEndDate = "{{ $task->project->end_date?->format('Y-m-d') }}";
            
            if (parentSelect && dueDateInput) {
                parentSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const parentDueDate = selectedOption.getAttribute('data-due-date');
                    const parentDueDateLabel = selectedOption.getAttribute('data-due-date-label');
                    
                    if (parentDueDate) {
                        // Set max to parent task's due date
                        dueDateInput.setAttribute('max', parentDueDate);
                        
                        // Show parent deadline info
                        if (parentDeadlineInfo) {
                            parentDeadlineInfo.style.display = 'block';
                            parentDeadlineInfo.querySelector('span').textContent = 
                                'Deadline tugas utama: ' + parentDueDateLabel;
                        }
                        
                        // Reset due_date if it exceeds parent's due_date
                        if (dueDateInput.value && dueDateInput.value > parentDueDate) {
                            dueDateInput.value = parentDueDate;
                        }
                    } else {
                        // Reset to project end date
                        if (projectEndDate) {
                            dueDateInput.setAttribute('max', projectEndDate);
                        } else {
                            dueDateInput.removeAttribute('max');
                        }
                        
                        // Hide parent deadline info
                        if (parentDeadlineInfo) {
                            parentDeadlineInfo.style.display = 'none';
                        }
                    }
                });
                
                // Trigger on page load if parent is pre-selected
                parentSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection