<header class="top-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search projects, tasks..." class="search-input">
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-date">
            <i class="fas fa-calendar-alt"></i>
            <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
        </div>

        <!-- Notifications Dropdown -->
        @auth
        <div class="dropdown" id="notificationDropdown">
            <button class="navbar-btn" title="Notifikasi" onclick="toggleDropdown('notificationDropdown')">
                <i class="fas fa-bell"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </button>
            <div class="dropdown-menu notification-dropdown">
                <div class="notification-dropdown-header">
                    <span class="notification-dropdown-title">Notifikasi</span>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllRead') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="notification-mark-all">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
                <div class="notification-dropdown-list">
                    @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                        <a href="{{ route('notifications.read', $notification->id) }}" 
                           class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}"
                           onclick="event.preventDefault(); document.getElementById('notif-form-{{ $notification->id }}').submit();">
                            <div class="notification-dropdown-icon {{ $notification->data['type'] ?? 'default' }}">
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
                                    @case('deadline_warning')
                                        <i class="fas fa-clock"></i>
                                        @break
                                    @default
                                        <i class="fas fa-bell"></i>
                                @endswitch
                            </div>
                            <div class="notification-dropdown-content">
                                <span class="notification-dropdown-title-text">{{ Str::limit($notification->data['title'] ?? 'Notifikasi', 40) }}</span>
                                <span class="notification-dropdown-message">{{ Str::limit($notification->data['message'] ?? 'Notifikasi baru', 60) }}</span>
                                <span class="notification-dropdown-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                        <form id="notif-form-{{ $notification->id }}" action="{{ route('notifications.read', $notification->id) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @empty
                        <div class="notification-dropdown-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
                <a href="{{ route('notifications.index') }}" class="notification-dropdown-footer">
                    Lihat Semua Notifikasi
                </a>
            </div>
        </div>
        @endauth

        <div class="dropdown" id="userDropdown">
            <button class="user-menu" onclick="toggleDropdown('userDropdown')">
                @auth
                    <div class="avatar avatar-sm">
                        {{ auth()->user()->initials }}
                    </div>
                    <span class="user-menu-name">{{ auth()->user()->name }}</span>
                @else
                    <div class="avatar avatar-sm">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-menu-name">Guest</span>
                @endauth
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="dropdown-menu">
                @auth
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"
                            style="width: 100%; background: none; border: none; cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="dropdown-item">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="dropdown-item">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<style>
    .top-navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        position: relative;
        z-index: 100;
    }

    .navbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sidebar-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--dark);
        cursor: pointer;
        padding: 0.5rem;
    }

    .search-box {
        position: relative;
        width: 320px;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        background: white;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f8fafc;
        border-radius: 10px;
        margin-right: 0.5rem;
    }

    .navbar-date i {
        color: #6366f1;
        font-size: 0.875rem;
    }

    .navbar-date span {
        font-size: 0.8rem;
        font-weight: 500;
        color: #475569;
    }

    .navbar-btn {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: none;
        background: white;
        color: var(--secondary);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .navbar-btn:hover {
        background: #f1f5f9;
        color: var(--primary);
    }

    .notification-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 18px;
        height: 18px;
        background: var(--danger);
        color: white;
        font-size: 0.65rem;
        font-weight: 600;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        border: none;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .user-menu:hover {
        background: #f1f5f9;
    }

    .user-menu-name {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--dark);
    }

    .user-menu i.fa-chevron-down {
        font-size: 0.75rem;
        color: var(--secondary);
    }

    @media (max-width: 768px) {
        .sidebar-toggle {
            display: block;
        }

        .search-box {
            display: none;
        }
    }

    /* Notification Dropdown */
    .notification-dropdown {
        width: 360px;
        padding: 0;
        right: 0;
        z-index: 9999;
    }

    .notification-dropdown-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .notification-dropdown-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }

    .notification-mark-all {
        background: none;
        border: none;
        color: #6366f1;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
    }

    .notification-mark-all:hover {
        text-decoration: underline;
    }

    .notification-dropdown-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .notification-dropdown-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        text-decoration: none;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .notification-dropdown-item:hover {
        background: #f8fafc;
    }

    .notification-dropdown-item.unread {
        background: #eef2ff;
    }

    .notification-dropdown-item.unread:hover {
        background: #e0e7ff;
    }

    .notification-dropdown-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .notification-dropdown-icon.task_assigned {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .notification-dropdown-icon.task_completed {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .notification-dropdown-icon.new_comment {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .notification-dropdown-icon.user_mentioned {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .notification-dropdown-icon.default {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .notification-dropdown-icon.deadline_warning {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .notification-dropdown-content {
        flex: 1;
        min-width: 0;
    }

    .notification-dropdown-title-text {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.125rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notification-dropdown-message {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 0.25rem;
    }

    .notification-dropdown-time {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .notification-dropdown-empty {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
    }

    .notification-dropdown-empty i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .notification-dropdown-empty p {
        margin: 0;
        font-size: 0.8rem;
    }

    .notification-dropdown-footer {
        display: block;
        padding: 0.875rem;
        text-align: center;
        background: #f8fafc;
        color: #6366f1;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        border-top: 1px solid #e2e8f0;
    }

    .notification-dropdown-footer:hover {
        background: #f1f5f9;
    }
</style>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.classList.toggle('active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
</script>