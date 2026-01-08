<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <i class="fas fa-th-large"></i>
            </div>
            <div class="logo-text">
                <span class="logo-title">Project Manager</span>
                <span class="logo-subtitle">Professional Edition</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="nav-item-main {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>

        <!-- Recent Projects Section -->
        <div class="nav-section">
            <div class="nav-section-header">
                <span class="nav-section-title">Recent Projects</span>
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
                        <span class="project-dot" style="background: {{ $project->status->value === 'active' ? '#22c55e' : ($project->status->value === 'completed' ? '#3b82f6' : '#f59e0b') }};"></span>
                        <span class="project-name">{{ Str::limit($project->name, 16) }}</span>
                        <i class="fas fa-chevron-down project-arrow"></i>
                    </button>
                    
                    <div class="project-submenu" id="project-menu-{{ $project->id }}">
                        <a href="{{ route('projects.show', $project) }}" class="submenu-item {{ request()->is('projects/'.$project->id) && !request()->is('projects/'.$project->id.'/edit') ? 'active' : '' }}">
                            <i class="fas fa-eye"></i>
                            <span>Overview Proyek</span>
                        </a>
                        <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="submenu-item {{ request()->is('tasks*') && request('project_id') == $project->id ? 'active' : '' }}">
                            <i class="fas fa-check-square"></i>
                            <span>Tugas</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-calendar"></i>
                            <span>Kalender</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="submenu-item">
                            <i class="fas fa-users"></i>
                            <span>Tim</span>
                        </a>
                        <a href="{{ route('reports.index', ['project_id' => $project->id]) }}" class="submenu-item {{ request()->routeIs('reports.*') && request('project_id') == $project->id ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i>
                            <span>Laporan</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-clock"></i>
                            <span>Time Tracking</span>
                        </a>
                        <a href="#" class="submenu-item">
                            <i class="fas fa-file-alt"></i>
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
        background: linear-gradient(180deg, #1a2332 0%, #0f1419 100%);
        color: white;
        display: flex;
        flex-direction: column;
        z-index: 100;
    }

    .sidebar-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .logo-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: white;
    }

    .logo-text {
        display: flex;
        flex-direction: column;
    }

    .logo-title {
        font-size: 1rem;
        font-weight: 700;
        color: white;
    }

    .logo-subtitle {
        font-size: 0.7rem;
        color: #64748b;
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
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s;
        margin-bottom: 1rem;
    }

    .nav-item-main:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .nav-item-main.active {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        color: #64748b;
    }

    .nav-add-btn {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #64748b;
        transition: color 0.2s;
    }

    .nav-add-btn:hover {
        color: var(--primary);
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
        color: #94a3b8;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .project-toggle:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .project-group.expanded .project-toggle {
        background: rgba(255, 255, 255, 0.05);
        color: white;
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
        color: #64748b;
    }

    .project-group.expanded .project-arrow {
        transform: rotate(180deg);
    }

    /* Submenu Styles */
    .project-submenu {
        display: none;
        margin-left: 1.25rem;
        padding-left: 0.75rem;
        border-left: 2px solid rgba(249, 115, 22, 0.3);
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
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 0.125rem;
    }

    .submenu-item:hover {
        color: white;
        background: rgba(255, 255, 255, 0.03);
    }

    .submenu-item.active {
        color: var(--primary);
        background: rgba(249, 115, 22, 0.1);
    }

    .submenu-item i {
        width: 16px;
        font-size: 0.75rem;
        text-align: center;
        color: inherit;
    }

    /* Footer */
    .sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #94a3b8;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
        border-color: rgba(255, 255, 255, 0.15);
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
    document.addEventListener('DOMContentLoaded', function() {
        const expandedGroups = document.querySelectorAll('.project-group.expanded');
        expandedGroups.forEach(group => {
            group.classList.add('expanded');
        });
    });
</script>