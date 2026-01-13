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
                <span class="nav-section-title">RECENT PROJECTS</span>
                <a href="{{ route('projects.create') }}" class="nav-add-btn" title="Add Project">
                    <i class="fas fa-plus"></i>
                </a>
            </div>

            @php
                $allProjects = \App\Models\Project::latest()->get();
                $currentProjectId = request()->route('project')?->id ?? request('project_id') ?? (request()->is('projects/*') ? request()->segment(2) : null);
            @endphp

            @foreach($allProjects as $project)
                <div class="project-group {{ $currentProjectId == $project->id ? 'expanded' : '' }}">
                    <button class="project-toggle" onclick="toggleProject({{ $project->id }})">
                        <span class="project-dot"
                            style="background: {{ $project->status->value === 'active' ? '#22c55e' : ($project->status->value === 'completed' ? '#3b82f6' : '#f59e0b') }};"></span>
                        <span class="project-name">{{ Str::limit($project->name, 20) }}</span>
                        <i class="fas fa-chevron-down project-arrow"></i>
                    </button>

                    <div class="project-submenu" id="project-menu-{{ $project->id }}">
                        <a href="{{ route('projects.show', $project) }}"
                            class="submenu-item {{ request()->is('projects/' . $project->id) && !request()->is('projects/' . $project->id . '/edit') ? 'active' : '' }}">
                            <i class="fas fa-eye icon-overview"></i>
                            <span>Overview Proyek</span>
                        </a>
                        <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}"
                            class="submenu-item {{ request()->is('tasks*') && request('project_id') == $project->id ? 'active' : '' }}">
                            <i class="fas fa-check-square icon-tugas"></i>
                            <span>Tugas</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-calendar icon-kalender"></i>
                            <span>Kalender</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="submenu-item">
                            <i class="fas fa-users icon-tim"></i>
                            <span>Tim</span>
                        </a>
                        <a href="{{ route('reports.index', ['project_id' => $project->id]) }}"
                            class="submenu-item {{ request()->routeIs('reports.*') && request('project_id') == $project->id ? 'active' : '' }}">
                            <i class="fas fa-chart-bar icon-laporan"></i>
                            <span>Laporan</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-clock icon-time"></i>
                            <span>Time Tracking</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-file-alt icon-dokumen"></i>
                            <span>Dokumen</span>
                        </a>
                    </div>
                </div>
            @endforeach
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
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-image {
        max-width: 160px;
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

    .project-toggle {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        width: 100%;
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
    function toggleProject(projectId) {
        const group = document.querySelector(`#project-menu-${projectId}`).closest('.project-group');
        group.classList.toggle('expanded');
    }

    // Auto-expand current project on page load
    document.addEventListener('DOMContentLoaded', function () {
        const expandedGroups = document.querySelectorAll('.project-group.expanded');
        expandedGroups.forEach(group => {
            group.classList.add('expanded');
        });
    });
</script>