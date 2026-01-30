<header class="top-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="search-box" id="globalSearchBox">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search projects, tasks..." class="search-input" id="globalSearchInput" autocomplete="off">
            <div class="search-results" id="searchResults" style="display: none;">
                <div class="search-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Mencari...
                </div>
                <div class="search-content"></div>
                <div class="search-empty" style="display: none;">
                    <i class="fas fa-search"></i>
                    <p>Tidak ada hasil ditemukan</p>
                </div>
            </div>
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
                    <div class="avatar avatar-sm" style="overflow: hidden;">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ auth()->user()->initials }}
                        @endif
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
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        position: sticky;
        top: 0;
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

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 0.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
    }

    .search-loading, .search-empty {
        padding: 1.5rem;
        text-align: center;
        color: #64748b;
    }

    .search-empty i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .search-empty p {
        margin: 0;
    }

    .search-section {
        padding: 0.5rem 0;
    }

    .search-section-title {
        padding: 0.5rem 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .search-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        text-decoration: none;
        transition: background 0.15s;
    }

    .search-item:hover {
        background: #f1f5f9;
    }

    .search-item-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        color: white;
    }

    .search-item-icon.project {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .search-item-icon.task {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .search-item-icon.document {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .search-item-info {
        flex: 1;
        min-width: 0;
    }

    .search-item-title {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.125rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .search-item-meta {
        font-size: 0.75rem;
        color: #94a3b8;
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

    .notification-dropdown-icon.project_invitation {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .notification-dropdown-icon.default {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .notification-dropdown-icon.deadline_warning {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .notification-dropdown-icon.project_deadline_warning {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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
        
        // Close search results when clicking outside
        const searchBox = document.getElementById('globalSearchBox');
        if (searchBox && !searchBox.contains(e.target)) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });

    // Global Search Functionality
    (function() {
        const searchInput = document.getElementById('globalSearchInput');
        const searchResults = document.getElementById('searchResults');
        if (!searchInput || !searchResults) return;

        const searchLoading = searchResults.querySelector('.search-loading');
        const searchContent = searchResults.querySelector('.search-content');
        const searchEmpty = searchResults.querySelector('.search-empty');
        
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchResults.style.display = 'block';
            searchLoading.style.display = 'block';
            searchContent.innerHTML = '';
            searchEmpty.style.display = 'none';
            
            searchTimeout = setTimeout(() => {
                fetch(`/search?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    searchLoading.style.display = 'none';
                    
                    if (data.projects.length === 0 && data.tasks.length === 0 && data.documents.length === 0) {
                        searchEmpty.style.display = 'block';
                        return;
                    }
                    
                    let html = '';
                    
                    if (data.projects.length > 0) {
                        html += '<div class="search-section"><div class="search-section-title">📁 Proyek</div>';
                        data.projects.forEach(project => {
                            html += `
                                <a href="/projects/${project.id}" class="search-item">
                                    <div class="search-item-icon project"><i class="fas fa-folder"></i></div>
                                    <div class="search-item-info">
                                        <div class="search-item-title">${escapeHtml(project.name)}</div>
                                        <div class="search-item-meta">${project.tasks_count} tugas</div>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    if (data.tasks.length > 0) {
                        html += '<div class="search-section"><div class="search-section-title">✅ Tugas</div>';
                        data.tasks.forEach(task => {
                            html += `
                                <a href="/tasks/${task.id}" class="search-item">
                                    <div class="search-item-icon task"><i class="fas fa-check-circle"></i></div>
                                    <div class="search-item-info">
                                        <div class="search-item-title">${escapeHtml(task.title)}</div>
                                        <div class="search-item-meta">${escapeHtml(task.project_name)}</div>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    if (data.documents.length > 0) {
                        html += '<div class="search-section"><div class="search-section-title">📄 Dokumen</div>';
                        data.documents.forEach(doc => {
                            html += `
                                <a href="/documents/${doc.id}" class="search-item">
                                    <div class="search-item-icon document"><i class="fas fa-file-alt"></i></div>
                                    <div class="search-item-info">
                                        <div class="search-item-title">${escapeHtml(doc.title)}</div>
                                        <div class="search-item-meta">${escapeHtml(doc.project_name)}</div>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    searchContent.innerHTML = html;
                })
                .catch(error => {
                    searchLoading.style.display = 'none';
                    searchEmpty.style.display = 'block';
                });
            }, 300);
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchResults.style.display = 'block';
            }
        });
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    })();
</script>