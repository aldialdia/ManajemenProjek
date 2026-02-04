@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Notifikasi</h1>
        <p class="page-subtitle">Semua notifikasi Anda</p>
    </div>
    <div class="header-actions">
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-check-double"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Filter Tabs -->
<div class="notification-tabs">
    <a href="{{ route('notifications.index') }}" class="notification-tab {{ !request('filter') ? 'active' : '' }}">
        Semua
    </a>
    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="notification-tab {{ request('filter') === 'unread' ? 'active' : '' }}">
        Belum Dibaca
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="tab-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
        @endif
    </a>
</div>

<!-- Notifications List -->
<div class="notifications-list">
    @forelse($notifications as $notification)
        <div class="notification-item {{ $notification->read_at ? 'read' : 'unread' }}">
            <div class="notification-icon {{ $notification->data['type'] ?? 'default' }}">
                @switch($notification->data['type'] ?? '')
                    @case('task_assigned')
                        <i class="fas fa-user-plus"></i>
                        @break
                    @case('task_completed')
                        <i class="fas fa-check-circle"></i>
                        @break
                    @case('new_comment')
                        <i class="fas fa-comment"></i>
                        @break
                    @case('user_mentioned')
                        <i class="fas fa-at"></i>
                        @break
                    @case('project_invitation')
                        <i class="fas fa-envelope-open-text"></i>
                        @break
                    @case('deadline_warning')
                        <i class="fas fa-clock"></i>
                        @break
                    @case('project_deadline_warning')
                        <i class="fas fa-calendar-times"></i>
                        @break
                    @default
                        <i class="fas fa-bell"></i>
                @endswitch
            </div>
            <div class="notification-content">
                <p class="notification-title">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                <p class="notification-message">{{ $notification->data['message'] ?? 'Notifikasi baru' }}</p>
                <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
            </div>
            <div class="notification-actions">
                @if(!$notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-notification-action" title="Tandai dibaca & lihat">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </form>
                @else
                    @if(isset($notification->data['type']) && $notification->data['type'] === 'project_invitation' && isset($notification->data['invitation_token']))
                        <a href="{{ route('invitations.show', $notification->data['invitation_token']) }}" class="btn-notification-action" title="Lihat undangan">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @elseif(isset($notification->data['task_id']))
                        <a href="{{ route('tasks.show', $notification->data['task_id']) }}" class="btn-notification-action" title="Lihat tugas">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @elseif(isset($notification->data['target_id']) && $notification->data['target_type'] === 'task')
                        <a href="{{ route('tasks.show', $notification->data['target_id']) }}" class="btn-notification-action" title="Lihat tugas">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @elseif(isset($notification->data['target_id']) && $notification->data['target_type'] === 'project')
                        <a href="{{ route('projects.show', $notification->data['target_id']) }}" class="btn-notification-action" title="Lihat proyek">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @elseif(isset($notification->data['project_id']))
                        <a href="{{ route('projects.show', $notification->data['project_id']) }}" class="btn-notification-action" title="Lihat proyek">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                @endif
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" style="display: inline;"
                    onsubmit="return confirmSubmit(this, 'Hapus notifikasi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-notification-action delete" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="notifications-empty">
            <i class="fas fa-bell-slash"></i>
            <h3>Tidak ada notifikasi</h3>
            <p>Anda akan menerima notifikasi ketika ada tugas baru, komentar, atau update lainnya.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($notifications->hasPages() || $notifications->total() > 0)
    <div class="pagination-wrapper">
        <div class="pagination-left">
            <div class="per-page-selector">
                <span class="per-page-label">Results per page:</span>
                <div class="per-page-dropdown">
                    <button type="button" class="per-page-btn" onclick="togglePerPageDropdown()">
                        {{ $perPage }} <i class="fas fa-chevron-up"></i>
                    </button>
                    <div class="per-page-menu" id="perPageMenu">
                        @foreach([8, 12, 16, 20] as $option)
                            <a href="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" 
                               class="per-page-option {{ $perPage == $option ? 'active' : '' }}">
                                {{ $option }}
                                @if($perPage == $option) <i class="fas fa-check"></i> @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pagination-right">
            @if($notifications->onFirstPage())
                <span class="page-nav disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}" class="page-nav"><i class="fas fa-chevron-left"></i></a>
            @endif
            
            @php
                $currentPage = $notifications->currentPage();
                $lastPage = $notifications->lastPage();
                $maxVisible = 4;
                
                // Calculate window start and end
                if ($lastPage <= $maxVisible) {
                    $start = 1;
                    $end = $lastPage;
                } else {
                    // Center current page in window when possible
                    $start = max(1, $currentPage - floor($maxVisible / 2));
                    $end = min($lastPage, $start + $maxVisible - 1);
                    
                    // Adjust if we're near the end
                    if ($end == $lastPage) {
                        $start = max(1, $lastPage - $maxVisible + 1);
                    }
                }
            @endphp
            
            @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                    <span class="page-num active">{{ $page }}</span>
                @else
                    <a href="{{ $notifications->url($page) }}" class="page-num">{{ $page }}</a>
                @endif
            @endfor
            
            @if($end < $lastPage)
                <span class="page-ellipsis">...</span>
            @endif
            
            @if($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}" class="page-nav"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="page-nav disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
@endif

<style>
    .notification-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        background: white;
        padding: 0.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .notification-tab {
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        text-decoration: none;
        color: #64748b;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-tab:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .notification-tab.active {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .tab-badge {
        background: #ef4444;
        color: white;
        font-size: 0.7rem;
        padding: 0.125rem 0.5rem;
        border-radius: 999px;
        font-weight: 600;
    }

    .notification-tab.active .tab-badge {
        background: white;
        color: #6366f1;
    }

    .notifications-list {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .notification-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: #f8fafc;
    }

    .notification-item.unread {
        background: #eef2ff;
    }

    .notification-item.unread:hover {
        background: #e0e7ff;
    }

    .notification-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .notification-icon.task_assigned {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .notification-icon.task_completed {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .notification-icon.new_comment {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .notification-icon.user_mentioned {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .notification-icon.project_invitation {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .notification-icon.default {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .notification-icon.deadline_warning {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .notification-icon.project_deadline_warning {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
        line-height: 1.4;
    }

    .notification-message {
        color: #64748b;
        font-size: 0.85rem;
        margin: 0 0 0.25rem 0;
        line-height: 1.4;
    }

    .notification-time {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-notification-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-notification-action:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-notification-action.delete:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .notifications-empty {
        padding: 4rem 2rem;
        text-align: center;
        color: #94a3b8;
    }

    .notifications-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .notifications-empty h3 {
        color: #64748b;
        font-size: 1.125rem;
        margin: 0 0 0.5rem 0;
    }

    .notifications-empty p {
        font-size: 0.875rem;
        margin: 0;
    }

    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .pagination-left {
        display: flex;
        align-items: center;
    }

    .per-page-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .per-page-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    .per-page-dropdown {
        position: relative;
    }

    .per-page-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .per-page-btn:hover {
        border-color: #cbd5e1;
    }

    .per-page-btn i {
        font-size: 0.7rem;
        color: #94a3b8;
        transition: transform 0.2s;
    }

    .per-page-menu {
        position: absolute;
        bottom: 100%;
        left: 0;
        margin-bottom: 0.5rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        min-width: 100px;
        display: none;
        z-index: 100;
        overflow: hidden;
    }

    .per-page-menu.show {
        display: block;
    }

    .per-page-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        color: #1e293b;
        text-decoration: none;
        transition: background 0.15s;
    }

    .per-page-option:hover {
        background: #f1f5f9;
    }

    .per-page-option.active {
        color: #6366f1;
        font-weight: 500;
    }

    .per-page-option i {
        color: #6366f1;
        font-size: 0.75rem;
    }

    .pagination-right {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .page-nav, .page-num {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 0.5rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        text-decoration: none;
        transition: all 0.15s;
    }

    .page-nav:hover:not(.disabled), .page-num:hover:not(.active) {
        background: #f1f5f9;
        color: #1e293b;
    }

    .page-nav.disabled {
        color: #cbd5e1;
        cursor: not-allowed;
    }

    .page-num.active {
        color: #1e293b;
        font-weight: 600;
        position: relative;
    }

    .page-num.active::after {
        content: '';
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        width: 16px;
        height: 2px;
        background: #1e293b;
        border-radius: 1px;
    }

    .page-ellipsis {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 32px;
        font-size: 0.875rem;
        color: #94a3b8;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }
</style>

<script>
    function togglePerPageDropdown() {
        const menu = document.getElementById('perPageMenu');
        menu.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.per-page-dropdown');
        const menu = document.getElementById('perPageMenu');
        if (dropdown && menu && !dropdown.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
</script>
@endsection
