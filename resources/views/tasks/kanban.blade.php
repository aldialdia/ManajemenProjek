@extends('layouts.app')

@section('title', $project ? 'Kanban Board - ' . $project->name : 'Kanban Board')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kanban Board</h1>
            <p class="page-subtitle">
                @if($project)
                    Drag and drop tasks untuk project <strong>{{ $project->name }}</strong>
                @else
                    Drag and drop tasks to update status
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if($project)
                <label class="subtask-toggle">
                    <input type="checkbox" id="showSubtasksToggle" {{ $showSubtasks ? 'checked' : '' }}
                        onchange="toggleSubtasks()">
                    <span>Tampilkan Sub-task</span>
                </label>
                <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    List View
                </a>
                @if(auth()->user()->isManagerInProject($project))
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        New Task
                    </a>
                @endif
            @else
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    List View
                </a>
            @endif
        </div>
    </div>


    <div class="kanban-wrapper">
        <div class="kanban-board">
            <!-- To Do Column -->
            <div class="kanban-column" data-status="todo">
                <div class="kanban-column-header todo">
                    <span class="column-title">
                        <i class="fas fa-circle"></i>
                        To Do
                    </span>
                    <span class="column-count" id="count-todo">0</span>
                </div>
                <div class="kanban-cards" id="column-todo">
                    <!-- Cards will be loaded here -->
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="kanban-column" data-status="in_progress">
                <div class="kanban-column-header in-progress">
                    <span class="column-title">
                        <i class="fas fa-spinner"></i>
                        In Progress
                    </span>
                    <span class="column-count" id="count-in_progress">0</span>
                </div>
                <div class="kanban-cards" id="column-in_progress">
                    <!-- Cards will be loaded here -->
                </div>
            </div>

            <!-- Review Column -->
            <div class="kanban-column" data-status="review">
                <div class="kanban-column-header review">
                    <span class="column-title">
                        <i class="fas fa-eye"></i>
                        Review
                    </span>
                    <span class="column-count" id="count-review">0</span>
                </div>
                <div class="kanban-cards" id="column-review">
                    <!-- Cards will be loaded here -->
                </div>
            </div>

            <!-- Done Column -->
            <div class="kanban-column" data-status="done">
                <div class="kanban-column-header done">
                    <span class="column-title">
                        <i class="fas fa-check-circle"></i>
                        Done
                    </span>
                    <span class="column-count" id="count-done">0</span>
                </div>
                <div class="kanban-cards" id="column-done">
                    <!-- Cards will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <style>
        .subtask-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            color: #64748b;
            transition: all 0.2s;
        }

        .subtask-toggle:hover {
            background: #e2e8f0;
        }

        .subtask-toggle input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .subtask-indicator {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .subtask-indicator i {
            font-size: 0.6rem;
        }

        .kanban-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 180px);
            overflow: hidden;
        }

        .kanban-board {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 1rem;
            flex: 1;
            min-height: 0;
        }

        .kanban-column {
            flex: 1;
            min-width: 280px;
            max-width: 320px;
            background: #f1f5f9;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-radius: 16px 16px 0 0;
            font-weight: 600;
        }

        .kanban-column-header.todo {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
        }

        .kanban-column-header.in-progress {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .kanban-column-header.review {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .kanban-column-header.done {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .column-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }

        .kanban-cards {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            min-height: 200px;
        }

        .kanban-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            cursor: grab;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .kanban-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }

        .kanban-card.drag-over {
            border-color: #6366f1;
        }

        .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .card-title a {
            color: inherit;
            text-decoration: none;
        }

        .card-title a:hover {
            color: #6366f1;
        }

        .card-project {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-priority {
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-low {
            background: #f1f5f9;
            color: #475569;
        }

        .priority-medium {
            background: #dbeafe;
            color: #1e40af;
        }

        .priority-high {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }

        .card-due {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .card-due.overdue {
            color: #ef4444;
            font-weight: 600;
        }

        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: #94a3b8;
            display: none;
        }

        .kanban-cards.drag-over .drop-zone {
            display: block;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .card-header-row .card-title {
            flex: 1;
            margin-bottom: 0.5rem;
        }

        .approval-badge {
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .approval-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .approval-badge.approved {
            background: #dcfce7;
            color: #166534;
        }

        .kanban-card.not-draggable {
            cursor: default;
            opacity: 0.85;
        }

        .kanban-card.not-draggable:hover {
            transform: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
    </style>

    @push('scripts')
        <script>
            // Task data from backend
            const tasks = @json($tasks);

            function renderTasks() {
                const columns = ['todo', 'in_progress', 'review', 'done'];

                columns.forEach(status => {
                    const column = document.getElementById(`column-${status}`);
                    const statusTasks = tasks.filter(t => t.status === status);

                    document.getElementById(`count-${status}`).textContent = statusTasks.length;

                    column.innerHTML = statusTasks.map(task => {
                        // Check if user can drag this card
                        const canDrag = task.can_update_status;
                        const draggableAttr = canDrag ? 'draggable="true"' : '';
                        const notDraggableClass = canDrag ? '' : 'not-draggable';

                        return `
                                                    <div class="kanban-card ${notDraggableClass}" ${draggableAttr} data-task-id="${task.id}">
                                                        <div class="card-title">
                                                            <a href="/tasks/${task.id}">${task.title}</a>
                                                        </div>
                                                                                ${task.parent ? `
                                                                                    <div class="subtask-indicator">
                                                                                        <i class="fas fa-level-up-alt fa-rotate-90"></i>
                                                                                        Sub-task dari: ${task.parent.title}
                                                                                    </div>
                                                                                ` : ''}
                                                                                <div class="card-project">
                                                                                    <i class="fas fa-folder"></i>
                                                                                    ${task.project?.name || 'No Project'}
                                                                                </div>
                                                                                <div class="card-meta">
                                                                                    <span class="card-priority priority-${task.priority}">
                                                                                        ${task.priority}
                                                                                    </span>
                                                                                    ${task.assignee ? `
                                                                                        <div class="avatar avatar-sm" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                                                                            ${getInitials(task.assignee.name)}
                                                                                        </div>
                                                                                    ` : ''}
                                                                                </div>
                                                                                ${task.due_date ? `
                                                                                    <div class="card-footer">
                                                                                        <span class="card-due ${isOverdue(task.due_date) ? 'overdue' : ''}">
                                                                                            <i class="fas fa-calendar"></i>
                                                                                            ${formatDate(task.due_date)}
                                                                                        </span>
                                                                                    </div>
                                                                                ` : ''}
                                                                            </div>
                                                                        `;
                    }).join('');
                });

                initDragAndDrop();
            }

            function getInitials(name) {
                return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
            }

            function formatDate(date) {
                return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }

            function isOverdue(date) {
                return new Date(date) < new Date() && new Date(date).toDateString() !== new Date().toDateString();
            }

            function initDragAndDrop() {
                const cards = document.querySelectorAll('.kanban-card');
                const columns = document.querySelectorAll('.kanban-cards');

                cards.forEach(card => {
                    card.addEventListener('dragstart', () => {
                        card.classList.add('dragging');
                    });

                    card.addEventListener('dragend', () => {
                        card.classList.remove('dragging');
                    });
                });

                columns.forEach(column => {
                    column.addEventListener('dragover', e => {
                        e.preventDefault();
                        column.classList.add('drag-over');
                    });

                    column.addEventListener('dragleave', () => {
                        column.classList.remove('drag-over');
                    });

                    column.addEventListener('drop', e => {
                        e.preventDefault();
                        column.classList.remove('drag-over');

                        const card = document.querySelector('.dragging');
                        const taskId = card.dataset.taskId;
                        const newStatus = column.id.replace('column-', '');

                        // Update task status via API
                        updateTaskStatus(taskId, newStatus);

                        column.appendChild(card);
                        updateCounts();
                    });
                });
            }

            function updateTaskStatus(taskId, status) {
                fetch(`/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status })
                });
            }

            function updateCounts() {
                const columns = ['todo', 'in_progress', 'review', 'done'];
                columns.forEach(status => {
                    const count = document.querySelectorAll(`#column-${status} .kanban-card`).length;
                    document.getElementById(`count-${status}`).textContent = count;
                });
            }

            function toggleSubtasks() {
                const checkbox = document.getElementById('showSubtasksToggle');
                const url = new URL(window.location.href);
                if (checkbox.checked) {
                    url.searchParams.set('show_subtasks', '1');
                } else {
                    url.searchParams.delete('show_subtasks');
                }
                window.location.href = url.toString();
            }

            document.addEventListener('DOMContentLoaded', function () {
                renderTasks();

                // Show warning popup for members when project is on hold
                @if($project && $project->isOnHold() && !auth()->user()->isManagerInProject($project))
                    showProjectOnHoldModal('Project "{{ $project->name }}" sedang ditunda. Anda tidak dapat memindahkan task.');
                @endif
                            });
        </script>
    @endpush
@endsection