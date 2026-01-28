@extends('layouts.app')

@section('title', 'Semua Proyek')

@section('content')
    <div class="page-header">
        <div>
            <a href="{{ route('dashboard') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
            <h1 class="page-title">
                <i class="fas fa-folder-open" style="color: #6366f1;"></i>
                Semua Proyek
            </h1>
            <p class="page-subtitle">{{ $totalProjects }} proyek</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('projects.kanban') }}" class="btn btn-secondary">
                <i class="fas fa-columns"></i>
                Kanban Proyek
            </a>
            @if($canCreateProject)
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Proyek
                </a>
            @endif
        </div>
    </div>

    @if($projectsByYear->isEmpty())
        <div class="empty-projects-card">
            <div class="empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3>Belum Ada Proyek</h3>
            <p>Anda belum memiliki proyek. Mulai dengan membuat proyek baru!</p>
            @if($canCreateProject)
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Buat Proyek Pertama
                </a>
            @endif
        </div>
    @else
        <!-- Premium Year Filter -->
        <div class="filter-wrapper">
            <div class="year-filter-dropdown">
                <div class="year-filter-btn" onclick="toggleYearFilter(this)">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentYearDisplay">Semua Tahun</span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
                <div class="year-filter-menu">
                    <div class="filter-option active" data-year="all" onclick="selectYear('all', 'Semua Tahun')">
                        <i class="fas fa-layer-group"></i> Semua Tahun
                    </div>
                    @foreach($projectsByYear->keys() as $year)
                        <div class="filter-option" data-year="{{ $year }}" onclick="selectYear('{{ $year }}', '{{ $year }}')">
                            <i class="fas fa-calendar-day"></i> {{ $year }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="projects-content-container">
            @foreach($projectsByYear as $year => $projects)
                <div class="year-section" data-year="{{ $year }}">
                    <div class="year-section-header">
                        <span class="year-label">{{ $year }}</span>
                        <span class="year-projects-count">{{ $projects->count() }} proyek</span>
                    </div>
                    <div class="projects-grid">
                        @foreach($projects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="project-card">
                                <div class="project-card-top">
                                    <span class="status-dot" style="background: {{ $project->status->hexColor() }};"></span>
                                    <h3 class="project-title">{{ $project->name }}</h3>
                                </div>
                                <p class="project-desc">{{ Str::limit($project->description, 80) }}</p>
                                <div class="project-progress-container">
                                    <div class="progress-info">
                                        <span class="progress-label">Progress</span>
                                        <span class="progress-value">{{ $project->progress }}%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $project->progress }}%;"></div>
                                    </div>
                                </div>
                                <div class="project-footer">
                                    <span class="project-date">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ $project->created_at->format('d M Y') }}
                                    </span>
                                    <div class="project-members">
                                        <i class="fas fa-users"></i>
                                        {{ $project->members->count() }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6366f1;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: #4f46e5;
        transform: translateX(-3px);
    }

    .back-link i {
        font-size: 0.75rem;
    }

    .page-subtitle {
        color: #64748b;
        margin-top: 0.25rem;
    }

    .page-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Filter Styles */
    .filter-wrapper {
        margin-bottom: 2rem;
        display: flex;
        justify-content: flex-start;
    }

    .year-filter-dropdown {
        position: relative;
        z-index: 50;
    }

    .year-filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        background: white;
        color: #1e293b;
        padding: 0.625rem 1.25rem;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
        user-select: none;
    }

    .year-filter-btn:hover {
        border-color: #6366f1;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1);
    }

    .year-filter-btn i:first-child {
        color: #6366f1;
    }

    .year-filter-btn .dropdown-arrow {
        font-size: 0.8rem;
        color: #94a3b8;
        transition: transform 0.2s;
    }

    .year-filter-dropdown.active .year-filter-btn {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .year-filter-dropdown.active .dropdown-arrow {
        transform: rotate(180deg);
    }

    .year-filter-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        background: white;
        min-width: 220px;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        padding: 0.5rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .year-filter-dropdown.active .year-filter-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .filter-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.875rem;
        border-radius: 8px;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-option:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .filter-option.active {
        background: #f1f5f9;
        color: #6366f1;
        font-weight: 600;
    }

    .filter-option i {
        font-size: 0.85rem;
    }

    /* Section Styles */
    .year-section {
        margin-bottom: 3rem;
        transition: all 0.3s ease;
    }

    .year-section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .year-label {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.025em;
    }

    .year-projects-count {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Grid and Cards */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .project-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .project-card:hover {
        border-color: #6366f1;
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.1), 0 10px 10px -5px rgba(99, 102, 241, 0.04);
        transform: translateY(-4px);
    }

    .project-card-top {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .project-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-desc {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .project-progress-container {
        margin-bottom: 1.25rem;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .progress-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .progress-value {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6366f1;
    }

    .progress-bar {
        height: 6px;
        background: #f1f5f9;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #6366f1 0%, #a855f7 100%);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .project-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #f8fafc;
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .project-date {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .project-members {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        background: #f8fafc;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .page-actions {
            width: 100%;
        }
        
        .page-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .year-label {
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleYearFilter(element) {
        const dropdown = element.closest('.year-filter-dropdown');
        dropdown.classList.toggle('active');
    }

    function selectYear(year, label) {
        // Update display
        document.getElementById('currentYearDisplay').textContent = label;
        
        // Update active option
        document.querySelectorAll('.filter-option').forEach(opt => {
            opt.classList.toggle('active', opt.getAttribute('data-year') === year);
        });

        // Filter sections
        const sections = document.querySelectorAll('.year-section');
        sections.forEach(section => {
            if (year === 'all' || section.getAttribute('data-year') === year) {
                section.style.display = 'block';
                setTimeout(() => section.style.opacity = '1', 10);
            } else {
                section.style.opacity = '0';
                setTimeout(() => section.style.display = 'none', 300);
            }
        });

        // Close dropdown
        document.querySelector('.year-filter-dropdown').classList.remove('active');
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.year-filter-dropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Project Index initialized');
    });
</script>
@endpush
