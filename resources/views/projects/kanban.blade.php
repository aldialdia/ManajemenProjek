@extends('layouts.app')

@section('title', 'Kanban Proyek')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kanban Proyek</h1>
            <p class="page-subtitle">Drag and drop proyek untuk mengubah status</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    @php
        $userCanManage = auth()->user()->isAdmin() || auth()->user()->projects()
            ->wherePivot('role', 'manager')
            ->exists();
    @endphp

    @if(!$userCanManage)
        <div class="alert alert-info" style="margin-bottom: 1rem; padding: 1rem; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; color: #1e40af;">
            <i class="fas fa-info-circle"></i>
            <strong>Info:</strong> Anda dapat melihat kanban proyek, namun hanya Manager atau Admin yang dapat memindahkan proyek antar status.
        </div>
    @endif

    <div class="kanban-wrapper">
        <div class="kanban-board">
            @foreach($projectStatuses as $statusKey => $statusConfig)
                <div class="kanban-column" data-status="{{ $statusKey }}">
                    <div class="kanban-column-header {{ $statusKey }}">
                        <span class="column-title">
                            <i class="fas {{ $statusConfig['icon'] }}"></i>
                            {{ $statusConfig['label'] }}
                        </span>
                        <span class="column-count" id="count-{{ $statusKey }}">{{ ($projects[$statusKey] ?? collect())->count() }}</span>
                    </div>
                    <div class="kanban-cards" id="column-{{ $statusKey }}" data-status="{{ $statusKey }}">
                        @foreach($projects[$statusKey] ?? [] as $project)
                            @php
                                $canMoveProject = auth()->user()->isManagerInProject($project) || auth()->user()->isAdmin();
                                $statusLog = $project->latestStatusLog;
                            @endphp
                            <div class="kanban-card {{ $canMoveProject ? '' : 'not-draggable' }}" 
                                 data-project-id="{{ $project->id }}"
                                 data-project-name="{{ $project->name }}"
                                 {{ $canMoveProject ? 'draggable=true' : '' }}>
                                <div class="card-header-row">
                                    <div class="card-title">
                                        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                    </div>
                                    @if(!$canMoveProject)
                                        <i class="fas fa-lock" style="color: #94a3b8; font-size: 0.7rem;" title="Hanya manager/admin yang bisa memindahkan"></i>
                                    @endif
                                </div>
                                <div class="card-progress">
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill" style="width: {{ $project->progress }}%;"></div>
                                    </div>
                                    <span class="progress-text">{{ $project->progress }}%</span>
                                </div>
                                <div class="card-meta">
                                    <span><i class="fas fa-tasks"></i> {{ $project->tasks->count() }} tugas</span>
                                    @if($project->end_date)
                                        <span><i class="fas fa-calendar"></i> {{ $project->end_date->format('d M') }}</span>
                                    @endif
                                </div>
                                @if($statusLog)
                                    <div class="card-footer">
                                        <span class="card-date">
                                            <i class="fas fa-clock"></i>
                                            {{ $statusLog->created_at->format('d M Y, H:i') }}
                                            @if($statusLog->changedBy)
                                                <span style="font-style: italic;">oleh {{ $statusLog->changedBy->name }}</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if(($projects[$statusKey] ?? collect())->isEmpty())
                            <div class="kanban-empty">
                                <i class="fas fa-inbox"></i>
                                <span>Tidak ada proyek</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div class="confirm-modal-overlay" id="confirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirm-modal-title">Konfirmasi Perubahan Status</h3>
            <p class="confirm-modal-message" id="confirmModalMessage">
                Proyek ini masih memiliki tugas yang belum selesai. Yakin ingin menandai sebagai selesai?
            </p>
            <div class="confirm-modal-info" id="confirmModalInfo">
                <i class="fas fa-info-circle"></i>
                <span id="confirmModalTaskCount">5 tugas belum selesai</span>
            </div>
            <div class="confirm-modal-actions">
                <button class="btn-confirm-cancel" id="confirmCancel">
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <button class="btn-confirm-ok" id="confirmOk">
                    <i class="fas fa-check"></i>
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <style>
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
            color: white;
        }

        .kanban-column-header.new {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        .kanban-column-header.in_progress {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .kanban-column-header.on_hold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .kanban-column-header.done {
            background: linear-gradient(135deg, #10b981, #059669);
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

        .kanban-card.not-draggable {
            cursor: default;
            opacity: 0.85;
        }

        .kanban-card.not-draggable:hover {
            transform: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .kanban-cards.drag-over {
            background: rgba(99, 102, 241, 0.1);
            border-radius: 0 0 16px 16px;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
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

        .card-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .progress-bar-mini {
            flex: 1;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border-radius: 3px;
        }

        .progress-text {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            min-width: 35px;
        }

        .card-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .card-meta i {
            margin-right: 0.25rem;
        }

        .card-footer {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }

        .card-date {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .kanban-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .kanban-empty i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Custom Confirmation Modal */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.2s ease;
        }

        .confirm-modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .confirm-modal {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
        }

        .confirm-modal-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .confirm-modal-icon i {
            font-size: 2rem;
            color: #d97706;
        }

        .confirm-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .confirm-modal-message {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .confirm-modal-info {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .confirm-modal-info i {
            color: #d97706;
        }

        .confirm-modal-info span {
            color: #92400e;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .confirm-modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-confirm-cancel {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-confirm-cancel:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-confirm-ok {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-confirm-ok:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.kanban-card:not(.not-draggable)');
            const columns = document.querySelectorAll('.kanban-cards');
            
            // Modal elements
            const modal = document.getElementById('confirmModal');
            const modalMessage = document.getElementById('confirmModalMessage');
            const modalTaskCount = document.getElementById('confirmModalTaskCount');
            const confirmOk = document.getElementById('confirmOk');
            const confirmCancel = document.getElementById('confirmCancel');
            
            // Store pending action for modal confirmation
            let pendingAction = null;

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
                    if (!card) return;

                    const projectId = card.dataset.projectId;
                    const projectName = card.dataset.projectName;
                    const newStatus = column.dataset.status;
                    const oldColumn = card.closest('.kanban-cards');
                    const oldStatus = oldColumn.dataset.status;

                    if (newStatus === oldStatus) return;

                    // Optimistic UI update
                    column.appendChild(card);
                    updateCounts();

                    // Send AJAX request
                    updateProjectStatus(projectId, projectName, newStatus, oldColumn, card, false);
                });
            });

            function updateProjectStatus(projectId, projectName, newStatus, oldColumn, card, confirmed) {
                fetch(`/projects/${projectId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus, confirmed: confirmed })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.changed) {
                        showToast(`Proyek "${projectName}" dipindahkan ke ${getStatusLabel(newStatus)}`, 'success');
                    } else if (data.needs_confirmation) {
                        // Store pending action and show modal
                        pendingAction = {
                            projectId,
                            projectName,
                            newStatus,
                            oldColumn,
                            card,
                            incompleteTasks: data.incomplete_tasks
                        };
                        showConfirmModal(data.message, data.incomplete_tasks);
                    } else if (!data.success) {
                        oldColumn.appendChild(card);
                        updateCounts();
                        showToast(data.message || 'Gagal memindahkan proyek', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    oldColumn.appendChild(card);
                    updateCounts();
                    showToast('Terjadi kesalahan', 'error');
                });
            }

            function showConfirmModal(message, taskCount) {
                modalMessage.textContent = message;
                modalTaskCount.textContent = `${taskCount} tugas belum selesai`;
                modal.classList.add('show');
            }

            function hideConfirmModal() {
                modal.classList.remove('show');
            }

            // Modal button handlers
            confirmOk.addEventListener('click', function() {
                hideConfirmModal();
                if (pendingAction) {
                    updateProjectStatus(
                        pendingAction.projectId,
                        pendingAction.projectName,
                        pendingAction.newStatus,
                        pendingAction.oldColumn,
                        pendingAction.card,
                        true
                    );
                    pendingAction = null;
                }
            });

            confirmCancel.addEventListener('click', function() {
                hideConfirmModal();
                if (pendingAction) {
                    pendingAction.oldColumn.appendChild(pendingAction.card);
                    updateCounts();
                    showToast('Pemindahan dibatalkan', 'info');
                    pendingAction = null;
                }
            });

            // Close modal on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    confirmCancel.click();
                }
            });

            function updateCounts() {
                const statuses = ['new', 'in_progress', 'on_hold', 'done'];
                statuses.forEach(status => {
                    const count = document.querySelectorAll(`#column-${status} .kanban-card`).length;
                    document.getElementById(`count-${status}`).textContent = count;
                });
            }

            function getStatusLabel(status) {
                const labels = {
                    'new': 'Baru',
                    'in_progress': 'Berjalan',
                    'on_hold': 'Ditunda',
                    'done': 'Selesai'
                };
                return labels[status] || status;
            }
        });
    </script>
    @endpush
@endsection
