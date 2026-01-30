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
                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                            <div class="multi-select-dropdown">
                                <div class="dropdown-trigger" id="assigneeDropdownTrigger">
                                    <div class="selected-items" id="selectedAssignees">
                                        <span class="placeholder">Pilih anggota tim...</span>
                                    </div>
                                    <i class="fas fa-chevron-down dropdown-icon"></i>
                                </div>
                                <div class="dropdown-menu" id="assigneeDropdownMenu">
                                    <div class="dropdown-search">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" id="assigneeSearch" class="search-input" placeholder="Cari anggota tim...">
                                    </div>
                                    <div class="dropdown-items-container">
                                        @foreach($users ?? [] as $user)
                                            <label class="dropdown-item" data-name="{{ strtolower($user->name) }}">
                                                <input type="checkbox" name="assignees[]" value="{{ $user->id }}" 
                                                    data-name="{{ $user->name }}"
                                                    {{ in_array($user->id, old('assignees', [])) ? 'checked' : '' }}>
                                                <span class="assignee-info">
                                                    <span class="assignee-avatar">
                                                        @if($user->avatar)
                                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="avatar-img">
                                                        @else
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        @endif
                                                    </span>
                                                    <span class="assignee-name">{{ $user->name }}</span>
                                                </span>
                                                <span class="checkmark"><i class="fas fa-check"></i></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
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
                            <small id="parent-deadline-info" class="text-muted" style="display: none; color: #f59e0b;">
                                <i class="fas fa-exclamation-triangle"></i> <span></span>
                            </small>
                            @error('due_date')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($parentTasks->count() > 0)
                        <div class="form-group">
                            <label for="parent_task_id" class="form-label">Sub-task dari</label>
                            <select id="parent_task_id" name="parent_task_id" class="form-control">
                                <option value="" data-due-date="">-- Tidak ada (Task utama) --</option>
                                @foreach($parentTasks as $parentTask)
                                    <option value="{{ $parentTask->id }}" 
                                            data-due-date="{{ $parentTask->due_date?->format('Y-m-d') }}"
                                            data-due-date-label="{{ $parentTask->due_date?->format('d M Y') }}"
                                            {{ old('parent_task_id') == $parentTask->id ? 'selected' : '' }}>
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

        /* Multi-select Dropdown Styles */
        .multi-select-dropdown {
            position: relative;
            width: 100%;
        }

        .dropdown-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 45px;
        }

        .dropdown-trigger:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .dropdown-trigger.active {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .selected-items {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            align-items: center;
        }

        .selected-items .placeholder {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .selected-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.5rem;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .selected-tag .remove-tag {
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .selected-tag .remove-tag:hover {
            opacity: 1;
        }

        .dropdown-icon {
            color: #64748b;
            font-size: 0.875rem;
            transition: transform 0.2s ease;
        }

        .dropdown-trigger.active .dropdown-icon {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow: hidden;
            z-index: 1000;
            display: none;
        }

        .dropdown-menu.show {
            display: flex;
            flex-direction: column;
        }

        .dropdown-search {
            position: relative;
            padding: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.875rem;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .dropdown-items-container {
            overflow-y: auto;
            max-height: 250px;
            padding: 0.5rem;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.375rem;
        }

        .dropdown-item:last-child {
            margin-bottom: 0;
        }

        .dropdown-item:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .dropdown-item input[type="checkbox"] {
            display: none;
        }

        .dropdown-item input[type="checkbox"]:checked + .assignee-info + .checkmark {
            background: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        .dropdown-item input[type="checkbox"]:checked ~ .assignee-info .assignee-avatar {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .dropdown-item:has(input:checked) {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .assignee-info {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex: 1;
        }

        .no-results-message {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
            text-align: center;
        }

        .no-results-message i {
            font-size: 1rem;
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
            overflow: hidden;
            position: relative;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
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

        .dropdown-items-container::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-items-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .dropdown-items-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dropdown-items-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Multi-select dropdown functionality
            const dropdownTrigger = document.getElementById('assigneeDropdownTrigger');
            const dropdownMenu = document.getElementById('assigneeDropdownMenu');
            const selectedAssignees = document.getElementById('selectedAssignees');
            const searchInput = document.getElementById('assigneeSearch');
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
            const checkboxes = dropdownMenu.querySelectorAll('input[type="checkbox"]');

            // Toggle dropdown
            dropdownTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                dropdownTrigger.classList.toggle('active');
                
                // Focus search input when dropdown opens
                if (dropdownMenu.classList.contains('show')) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownTrigger.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    dropdownTrigger.classList.remove('active');
                    searchInput.value = ''; // Clear search on close
                    filterItems(''); // Reset filter
                }
            });

            // Search functionality
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterItems(searchTerm);
            });

            // Prevent dropdown from closing when clicking on search input
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            function filterItems(searchTerm) {
                let visibleCount = 0;
                
                dropdownItems.forEach(item => {
                    const name = item.getAttribute('data-name');
                    if (name.includes(searchTerm)) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show "no results" message if needed
                let noResultsMsg = dropdownMenu.querySelector('.no-results-message');
                if (visibleCount === 0) {
                    if (!noResultsMsg) {
                        noResultsMsg = document.createElement('div');
                        noResultsMsg.className = 'no-results-message';
                        noResultsMsg.innerHTML = '<i class="fas fa-search"></i> Tidak ada anggota yang ditemukan';
                        dropdownMenu.querySelector('.dropdown-items-container').appendChild(noResultsMsg);
                    }
                    noResultsMsg.style.display = 'flex';
                } else {
                    if (noResultsMsg) {
                        noResultsMsg.style.display = 'none';
                    }
                }
            }

            // Update selected items display
            function updateSelectedDisplay() {
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                
                if (selected.length === 0) {
                    selectedAssignees.innerHTML = '<span class="placeholder">Pilih anggota tim...</span>';
                } else {
                    selectedAssignees.innerHTML = selected.map(cb => {
                        const name = cb.getAttribute('data-name');
                        const id = cb.value;
                        return `
                            <span class="selected-tag">
                                ${name}
                                <i class="fas fa-times remove-tag" data-id="${id}"></i>
                            </span>
                        `;
                    }).join('');

                    // Add event listeners to remove buttons
                    selectedAssignees.querySelectorAll('.remove-tag').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const id = this.getAttribute('data-id');
                            const checkbox = dropdownMenu.querySelector(`input[value="${id}"]`);
                            if (checkbox) {
                                checkbox.checked = false;
                                updateSelectedDisplay();
                            }
                        });
                    });
                }
            }

            // Handle checkbox changes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedDisplay);
            });

            // Initialize display on page load
            updateSelectedDisplay();

            // Parent task deadline functionality
            const parentSelect = document.getElementById('parent_task_id');
            const dueDateInput = document.getElementById('due_date');
            const parentDeadlineInfo = document.getElementById('parent-deadline-info');
            const projectEndDate = "{{ $project->end_date?->format('Y-m-d') }}";
            
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