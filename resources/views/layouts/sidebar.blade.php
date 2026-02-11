<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="nav-item-main {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large icon-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <!-- Recent Projects Section -->
        <div class="nav-section">
            <div class="nav-section-header">
                <span class="nav-section-title">RECENT PROYEK</span>
            </div>

            <!-- Recent Projects Container (rendered by JavaScript) -->
            <div id="recentProjectsContainer">
                <!-- Projects will be rendered here by JavaScript -->
            </div>

            <!-- Empty State (shown when no recent projects) -->
            <div id="noRecentProjects" class="no-active-project" style="display: none;">
                <i class="fas fa-folder-open"></i>
                <span>Belum ada proyek yang dikunjungi</span>
            </div>
        </div>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

<style>
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: #ffffff;
        color: #1e293b;
        display: flex;
        flex-direction: column;
        z-index: 100;
        border-right: 1px solid #e2e8f0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.04);
    }

    .sidebar-header {
        padding: 1.25rem 2rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-image {
        max-width: 140px;
        height: auto;
    }

    .sidebar-nav {
        flex: 1;
        padding: 1rem 0.75rem;
        overflow-y: auto;
    }

    /* Dashboard Main Link */
    .nav-item-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        border-radius: 10px;
        color: #64748b;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s;
        margin-bottom: 1rem;
    }

    .nav-item-main:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .nav-item-main.active {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .nav-item-main i {
        font-size: 1rem;
    }

    .nav-section {
        margin: 0.5rem 0;
    }

    .nav-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 0.75rem;
        margin-bottom: 0.75rem;
    }

    .nav-section-title {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
    }

    .nav-add-btn {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #94a3b8;
        transition: color 0.2s;
    }

    .nav-add-btn:hover {
        color: #6366f1;
    }

    /* Project Group Styles */
    .project-group {
        margin-bottom: 0.25rem;
    }

    .project-header {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .project-toggle {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        flex: 1;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        background: transparent;
        border: none;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .remove-recent-btn {
        width: 14px;
        height: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.2s;
        font-size: 0.6rem;
    }

    .remove-recent-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .project-toggle:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .project-group.expanded .project-toggle {
        background: #f1f5f9;
        color: #1e293b;
    }

    .project-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .project-name {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-arrow {
        font-size: 0.65rem;
        transition: transform 0.2s;
        color: #94a3b8;
    }

    .project-group.expanded .project-arrow {
        transform: rotate(180deg);
    }

    /* Submenu Styles */
    .project-submenu {
        display: none;
        margin-left: 1.25rem;
        padding-left: 0.75rem;
        border-left: 2px solid #e2e8f0;
        margin-top: 0.25rem;
        margin-bottom: 0.5rem;
    }

    .project-group.expanded .project-submenu {
        display: block;
    }

    .submenu-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        color: #64748b;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 0.125rem;
    }

    .submenu-item:hover {
        color: #1e293b;
        background: #f8fafc;
    }

    .submenu-item.active {
        color: white;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .submenu-item i {
        width: 16px;
        font-size: 0.75rem;
        text-align: center;
    }

    /* Colored Icons */
    .icon-dashboard {
        color: #6366f1;
    }

    .nav-item-main.active .icon-dashboard {
        color: white;
    }

    .icon-overview {
        color: #3b82f6;
    }

    .icon-tugas {
        color: #f97316;
    }

    .icon-kalender {
        color: #22c55e;
    }

    .icon-tim {
        color: #ec4899;
    }

    .icon-laporan {
        color: #6366f1;
    }

    .icon-time {
        color: #06b6d4;
    }

    .icon-dokumen {
        color: #eab308;
    }

    .submenu-item.active i {
        color: white !important;
    }

    /* No Active Project State */
    .no-active-project {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1.5rem 1rem;
        color: #94a3b8;
        font-size: 0.8rem;
        text-align: center;
    }

    .no-active-project i {
        font-size: 1.5rem;
        color: #cbd5e1;
    }

    /* View All Projects Link */
    .view-all-projects-link {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 1rem;
        margin: 0.5rem 0.25rem;
        border-radius: 8px;
        color: #6366f1;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s;
        background: #f1f5ff;
        border: 1px dashed #c7d2fe;
    }

    .view-all-projects-link:hover {
        background: #e0e7ff;
        color: #4f46e5;
        border-color: #a5b4fc;
    }

    .view-all-projects-link i {
        font-size: 0.75rem;
    }

    /* Footer */
    .sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid #f1f5f9;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .logout-btn:hover {
        background: #f8fafc;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s;
        }

        .sidebar.active {
            transform: translateX(0);
        }
    }
</style>

<script>
    // Recent Projects localStorage functions
    const USER_ID = {{ auth()->id() ?? 0 }};
    const RECENT_PROJECTS_KEY = `recentProjects_user_${USER_ID}`;
    const MAX_RECENT_PROJECTS = 3;

    function getRecentProjects() {
        try {
            const stored = localStorage.getItem(RECENT_PROJECTS_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            return [];
        }
    }

    function addRecentProject(project) {
        let recentProjects = getRecentProjects();

        // Remove if already exists (to move to top)
        recentProjects = recentProjects.filter(p => p.id !== project.id);

        // Add to beginning
        recentProjects.unshift(project);

        // Limit to max
        if (recentProjects.length > MAX_RECENT_PROJECTS) {
            recentProjects = recentProjects.slice(0, MAX_RECENT_PROJECTS);
        }

        localStorage.setItem(RECENT_PROJECTS_KEY, JSON.stringify(recentProjects));
        renderRecentProjects();
    }

    function removeRecentProject(projectId) {
        let recentProjects = getRecentProjects();
        recentProjects = recentProjects.filter(p => p.id !== projectId);
        localStorage.setItem(RECENT_PROJECTS_KEY, JSON.stringify(recentProjects));

        // Hide the project group from DOM immediately
        const projectGroup = document.getElementById(`recent-project-${projectId}`);
        if (projectGroup) {
            projectGroup.style.display = 'none';
        }

        // Check if empty
        if (recentProjects.length === 0) {
            document.getElementById('noRecentProjects').style.display = 'flex';
        }
    }

    function toggleProject(projectId) {
        const group = document.getElementById(`recent-project-${projectId}`);
        if (group) {
            group.classList.toggle('expanded');
        }
    }

    function getStatusColor(status) {
        const colors = {
            'in_progress': '#3b82f6',
            'done': '#10b981',
            'on_hold': '#f59e0b',
            'planning': '#94a3b8'
        };
        return colors[status] || '#94a3b8';
    }

    function renderRecentProjects() {
        const container = document.getElementById('recentProjectsContainer');
        const emptyState = document.getElementById('noRecentProjects');
        let recentProjects = getRecentProjects();

        // Enforce max limit (in case limit was changed)
        if (recentProjects.length > MAX_RECENT_PROJECTS) {
            recentProjects = recentProjects.slice(0, MAX_RECENT_PROJECTS);
            localStorage.setItem(RECENT_PROJECTS_KEY, JSON.stringify(recentProjects));
        }

        if (!container) return;

        if (recentProjects.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.style.display = 'flex';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        // Get current project ID from URL
        const currentProjectId = getCurrentProjectId();

        let html = '';
        recentProjects.forEach(project => {
            const isExpanded = project.id == currentProjectId ? 'expanded' : '';
            const statusColor = getStatusColor(project.status);

            html += `
                <div class="project-group ${isExpanded}" id="recent-project-${project.id}">
                    <div class="project-header">
                        <button class="project-toggle" onclick="toggleProject(${project.id})">
                            <span class="project-dot" style="background: ${statusColor};"></span>
                            <span class="project-name">${truncateText(project.name, 20)}</span>
                            <i class="fas fa-chevron-down project-arrow"></i>
                        </button>
                        <button class="remove-recent-btn" onclick="event.stopPropagation(); removeRecentProject(${project.id})" title="Hapus dari recent">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="project-submenu" id="project-menu-${project.id}">
                        <a href="/projects/${project.id}" class="submenu-item">
                            <i class="fas fa-eye icon-overview"></i>
                            <span>Overview Proyek</span>
                        </a>
                        <a href="/tasks?project_id=${project.id}" class="submenu-item">
                            <i class="fas fa-check-square icon-tugas"></i>
                            <span>Tugas</span>
                        </a>
                        <a href="/tasks/calendar?project_id=${project.id}" class="submenu-item">
                            <i class="fas fa-calendar icon-kalender"></i>
                            <span>Kalender</span>
                        </a>
                        <a href="/projects/${project.id}/team" class="submenu-item">
                            <i class="fas fa-users icon-tim"></i>
                            <span>Tim</span>
                        </a>
                        <a href="/projects/${project.id}/reports" class="submenu-item">
                            <i class="fas fa-chart-bar icon-laporan"></i>
                            <span>Laporan</span>
                        </a>
                        <a href="/time-tracking?project_id=${project.id}" class="submenu-item">
                            <i class="fas fa-clock icon-time"></i>
                            <span>Time Tracking</span>
                        </a>
                        <a href="/projects/${project.id}/documents" class="submenu-item">
                            <i class="fas fa-file-alt icon-dokumen"></i>
                            <span>Dokumen</span>
                        </a>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        highlightActiveSubmenu();
    }

    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    function getCurrentProjectId() {
        // Check URL patterns
        const path = window.location.pathname;
        const search = window.location.search;

        // Pattern: /projects/{id}
        const projectMatch = path.match(/\/projects\/(\d+)/);
        if (projectMatch) return parseInt(projectMatch[1]);

        // Pattern: ?project_id={id}
        const params = new URLSearchParams(search);
        const projectId = params.get('project_id');
        if (projectId) return parseInt(projectId);

        // Pattern: window.currentProject (set by task detail, kanban, etc.)
        if (window.currentProject && window.currentProject.id) {
            return parseInt(window.currentProject.id);
        }

        return null;
    }

    function highlightActiveSubmenu() {
        const currentPath = window.location.pathname;
        const currentSearch = window.location.search;
        const fullPath = currentPath + currentSearch;
        const submenuItems = document.querySelectorAll('.submenu-item');

        // Check if we're on a task detail page (/tasks/{id})
        const taskDetailMatch = currentPath.match(/^\/tasks\/(\d+)$/);
        const currentProjectId = getCurrentProjectId();

        submenuItems.forEach(item => {
            const href = item.getAttribute('href');
            let isActive = false;

            // If on task detail page, highlight the Tugas submenu for the matching project
            if (taskDetailMatch && currentProjectId && href === `/tasks?project_id=${currentProjectId}`) {
                isActive = true;
            }
            // Exact match for full path (including query params)
            else if (href === fullPath) {
                isActive = true;
            }
            // For Overview: exact pathname match (no trailing paths)
            else if (href.match(/^\/projects\/\d+$/) && currentPath === href) {
                isActive = true;
            }
            // For other /projects/{id}/xxx paths: exact pathname match
            else if (href.includes('/projects/') && href.includes('/') && currentPath === href) {
                isActive = true;
            }
            // For query-based paths like /tasks?project_id=X: check both path and query
            else if (href.includes('?') && fullPath === href) {
                isActive = true;
            }

            if (isActive) {
                item.classList.add('active');
                // Also expand the parent project group
                const projectGroup = item.closest('.recent-project-group');
                if (projectGroup) {
                    projectGroup.classList.add('expanded');
                }
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Auto-detect and add current project on page load
    document.addEventListener('DOMContentLoaded', function () {
        // Check if a project was just deleted and remove from localStorage
        @if(session('deleted_project_id'))
            removeRecentProject({{ session('deleted_project_id') }});
        @endif

            // Check if we need to expand a specific project from notification
            @if(session('expand_project'))
                const expandProject = @json(session('expand_project'));
                // Add project to recent list first
                addRecentProject(expandProject);
                // After render, expand the project and highlight Tugas menu
                setTimeout(() => {
                    const projectGroup = document.getElementById(`recent-project-${expandProject.id}`);
                    if (projectGroup) {
                        projectGroup.classList.add('expanded');
                        // Highlight Tugas submenu
                        const tugasLink = projectGroup.querySelector('a[href*="/tasks?project_id="]');
                        if (tugasLink) {
                            tugasLink.classList.add('active');
                            tugasLink.style.background = 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)';
                            tugasLink.style.color = 'white';
                            tugasLink.style.borderRadius = '8px';
                        }
                    }
                }, 100);
            @endif

        // Render existing recent projects
        renderRecentProjects();

        // Check if we're on a project page and auto-add
        if (window.currentProject) {
            addRecentProject(window.currentProject);
        }
    });
</script>