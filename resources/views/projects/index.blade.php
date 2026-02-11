@extends('layouts.app')

@section('title', 'Semua Proyek')

@section('content')
    <div class="page-header">
        <div>
            <a href="{{ route('dashboard') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>
        <div class="page-actions">
            <a href="{{ route('projects.kanban') }}" id="kanbanLink" class="btn btn-secondary">
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
        <div class="no-results-message">
            <i class="fas fa-folder-open"></i>
            <p>Belum ada proyek</p>
        </div>
    @else
        <!-- Filter Card -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <div class="filter-row">
                    <div class="filter-group" style="flex: 2;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari proyek..."
                            onkeyup="filterProjects()">
                    </div>
                    <div class="filter-group">
                        <select id="yearFilter" class="form-control" onchange="filterProjects()">
                            <option value="">Semua Tahun</option>
                            @foreach($projectsByYear->keys() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="typeFilter" class="form-control" onchange="filterProjects()">
                            <option value="">Semua Tipe</option>
                            <option value="rbb">RBB</option>
                            <option value="non_rbb">Non-RBB</option>
                        </select>
                    </div>
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
                            <a href="{{ route('projects.show', $project) }}" class="project-card"
                                data-name="{{ strtolower($project->name) }}" data-type="{{ $project->type?->value ?? 'non_rbb' }}">
                                <div class="project-card-top">
                                    <span class="status-dot" style="background: {{ $project->status->hexColor() }};"></span>
                                    <h3 class="project-title">{{ $project->name }}</h3>
                                    @if($project->type)
                                        <span class="project-type-badge {{ $project->type->value }}">
                                            {{ $project->type->label() }}
                                        </span>
                                    @endif
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
                                        {{ $project->users->count() }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- No Results Message (hidden by default) -->
        <div id="noResultsMessage" class="no-results-message" style="display: none;">
            <i class="fas fa-search"></i>
            <p>Tidak ada proyek yang cocok dengan filter</p>
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

        /* Inline Filter Styles */
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 120px;
        }

        /* Type Badge on Cards */
        .project-type-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-left: auto;
        }

        .project-type-badge.rbb {
            background: #eef2ff;
            color: #4f46e5;
        }

        .project-type-badge.non_rbb {
            background: #f1f5f9;
            color: #64748b;
        }

        /* No Results Message */
        .no-results-message {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .no-results-message i {
            font-size: 2.5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .no-results-message p {
            color: #64748b;
            font-size: 1rem;
            margin: 0;
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
        function filterProjects() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const yearFilter = document.getElementById('yearFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const noResultsMessage = document.getElementById('noResultsMessage');
            const kanbanLink = document.getElementById('kanbanLink');

            // Update Kanban link with year and type parameters
            const baseKanbanUrl = '{{ route('projects.kanban') }}';
            if (kanbanLink) {
                let params = [];
                if (yearFilter) params.push('year=' + yearFilter);
                if (typeFilter) params.push('type=' + typeFilter);
                kanbanLink.href = params.length > 0 ? baseKanbanUrl + '?' + params.join('&') : baseKanbanUrl;
            }

            let totalVisibleProjects = 0;

            // Filter sections by year
            const sections = document.querySelectorAll('.year-section');
            sections.forEach(section => {
                const sectionYear = section.getAttribute('data-year');
                const yearMatch = !yearFilter || sectionYear === yearFilter;

                if (yearMatch) {
                    section.style.display = 'block';

                    // Filter cards within section
                    const cards = section.querySelectorAll('.project-card');
                    let visibleCards = 0;

                    cards.forEach(card => {
                        const name = card.getAttribute('data-name') || '';
                        const type = card.getAttribute('data-type') || '';

                        const searchMatch = !searchTerm || name.includes(searchTerm);
                        const typeMatch = !typeFilter || type === typeFilter;

                        if (searchMatch && typeMatch) {
                            card.style.display = 'flex';
                            visibleCards++;
                            totalVisibleProjects++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Hide section if no visible cards
                    if (visibleCards === 0) {
                        section.style.display = 'none';
                    }
                } else {
                    section.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (noResultsMessage) {
                noResultsMessage.style.display = totalVisibleProjects === 0 ? 'block' : 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Check if there are year/type parameters in the URL and pre-select them
            const urlParams = new URLSearchParams(window.location.search);
            const yearParam = urlParams.get('year');
            const typeParam = urlParams.get('type');
            let shouldFilter = false;

            if (yearParam) {
                const yearFilter = document.getElementById('yearFilter');
                if (yearFilter) {
                    yearFilter.value = yearParam;
                    shouldFilter = true;
                }
            }

            if (typeParam) {
                const typeFilter = document.getElementById('typeFilter');
                if (typeFilter) {
                    typeFilter.value = typeParam;
                    shouldFilter = true;
                }
            }

            if (shouldFilter) {
                filterProjects();
            }
        });
    </script>
@endpush