@extends('layouts.app')

@section('title', 'Dokumen - ' . $project->name)

@section('content')
    <div class="doc-page-container">
        <!-- Header Row -->
        <div class="doc-header-row">
            <div class="doc-header-left">
                <h1 class="doc-main-title">Manajemen Dokumen</h1>
                <p class="doc-subtitle">Kelola semua file dan dokumen proyek</p>
            </div>
            @php
                $canUpload = !$project->isOnHold();
            @endphp
            @if($canUpload)
                <a href="{{ route('projects.documents.create', $project) }}" class="doc-upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload File
                </a>
            @else
                <button class="doc-upload-btn" disabled title="Project sedang ditunda" style="background: #94a3b8; cursor: not-allowed;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload File
                </button>
            @endif
        </div>

        <!-- Search Bar -->
        <div class="doc-search-wrapper">
            <i class="fas fa-search doc-search-icon"></i>
            <input type="text" id="doc-search" class="doc-search-input" placeholder="Cari dokumen atau proyek...">
        </div>

        <!-- Stats Row -->
        @php
            // Helper function to get extension from a document (handles both Documents and Task Attachments)
            $getExt = function ($d) {
                $isAttachment = isset($d->is_attachment) && $d->is_attachment;
                if ($isAttachment) {
                    // For task attachments, use title which contains the original filename
                    return strtolower(pathinfo($d->title, PATHINFO_EXTENSION));
                }
                // For regular documents, try file_path first, fallback to title
                $filePath = $d->latestVersion->file_path ?? '';
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                return $ext ?: strtolower(pathinfo($d->title ?? '', PATHINFO_EXTENSION));
            };

            $totalFiles = $documents->count();
            $docCount = $documents->filter(fn($d) => in_array($getExt($d), ['pdf', 'doc', 'docx', 'txt']))->count();
            $imageCount = $documents->filter(fn($d) => in_array($getExt($d), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp']))->count();
            $spreadsheetCount = $documents->filter(fn($d) => in_array($getExt($d), ['xls', 'xlsx', 'csv']))->count();
            $codeCount = $documents->filter(fn($d) => in_array($getExt($d), ['zip', 'sql', 'js', 'php', 'html', 'css', 'json', 'py']))->count();
        @endphp
        <div class="doc-stats-row">
            <div class="doc-stat-card">
                <span class="doc-stat-number">{{ $totalFiles }}</span>
                <span class="doc-stat-label">Total File</span>
            </div>
            <div class="doc-stat-card stat-dokumen">
                <span class="doc-stat-number">{{ $docCount }}</span>
                <span class="doc-stat-label">Dokumen</span>
            </div>
            <div class="doc-stat-card stat-gambar">
                <span class="doc-stat-number">{{ $imageCount }}</span>
                <span class="doc-stat-label">Gambar</span>
            </div>
            <div class="doc-stat-card stat-spreadsheet">
                <span class="doc-stat-number">{{ $spreadsheetCount }}</span>
                <span class="doc-stat-label">Spreadsheet</span>
            </div>
            <div class="doc-stat-card stat-kode">
                <span class="doc-stat-number">{{ $codeCount }}</span>
                <span class="doc-stat-label">Kode</span>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="doc-filter-tabs">
            <button class="doc-filter-tab active" data-filter="all">Semua ({{ $totalFiles }})</button>
            <button class="doc-filter-tab" data-filter="dokumen">Dokumen ({{ $docCount }})</button>
            <button class="doc-filter-tab" data-filter="gambar">Gambar ({{ $imageCount }})</button>
            <button class="doc-filter-tab" data-filter="spreadsheet">Spreadsheet ({{ $spreadsheetCount }})</button>
            <button class="doc-filter-tab" data-filter="kode">Kode ({{ $codeCount }})</button>
        </div>

        <!-- Documents List -->
        <div class="doc-list-container">
            @forelse($documents as $doc)
                @php
                    // Use the same helper function to get extension
                    $ext = $getExt($doc);
                    $iconClass = 'fa-file';
                    $iconColor = '#64748b';
                    $category = 'other';

                    if (in_array($ext, ['pdf', 'doc', 'docx', 'txt'])) {
                        $iconClass = 'fa-file-alt';
                        $iconColor = '#ef4444';
                        $category = 'dokumen';
                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
                        $iconClass = 'fa-image';
                        $iconColor = '#ec4899';
                        $category = 'gambar';
                    } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                        $iconClass = 'fa-file-excel';
                        $iconColor = '#22c55e';
                        $category = 'spreadsheet';
                    } elseif (in_array($ext, ['zip', 'sql', 'js', 'php', 'html', 'css', 'json', 'py'])) {
                        $iconClass = 'fa-file-code';
                        $iconColor = '#3b82f6';
                        $category = 'kode';
                    }
                @endphp
                <div class="doc-list-item" data-category="{{ $category }}" data-name="{{ strtolower($doc->title) }}">
                    <div class="doc-item-left">
                        <div class="doc-file-icon" style="background: {{ $iconColor }}15; color: {{ $iconColor }};">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        <div class="doc-item-info">
                            @php
                                $isAttachment = isset($doc->is_attachment) && $doc->is_attachment;
                                $fileSize = 'N/A';
                                if ($isAttachment && isset($doc->latestVersion->getSizeAttribute)) {
                                    $fileSize = ($doc->latestVersion->getSizeAttribute)();
                                } elseif (method_exists($doc->latestVersion ?? null, 'getSizeAttribute')) {
                                    $fileSize = $doc->latestVersion->getSizeAttribute();
                                }
                            @endphp
                            @if($isAttachment)
                                <span class="doc-item-title">{{ $doc->title }}</span>
                            @else
                                <a href="{{ route('documents.show', ['document' => $doc->id]) }}"
                                    class="doc-item-title">{{ $doc->title }}</a>
                            @endif
                            <div class="doc-item-meta">
                                <span>{{ $doc->project->name }}</span>
                                <span class="doc-meta-dot">•</span>
                                <span>{{ $fileSize }}</span>
                                <span class="doc-meta-dot">•</span>
                                <span class="doc-version-badge">v{{ $doc->latestVersion->version_number ?? 1 }}</span>
                                @if($isAttachment && isset($doc->source_task))
                                    <span class="doc-meta-dot">•</span>
                                    <span class="doc-source-badge">dari Tugas:
                                        {{ Str::limit($doc->source_task->title ?? '', 20) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="doc-item-right">
                        <div class="doc-user-info">
                            <span class="doc-user-name">{{ $doc->latestVersion->uploader->name ?? 'Unknown' }}</span>
                            <span class="doc-user-date"><i class="far fa-clock"></i>
                                {{ ($doc->latestVersion->created_at ?? $doc->updated_at)->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="doc-actions-dropdown">
                            <button class="doc-action-btn" onclick="toggleDocMenu(this)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="doc-dropdown-menu">
                                @php $isAttachment = isset($doc->is_attachment) && $doc->is_attachment; @endphp
                                @if($isAttachment)
                                    <a href="{{ route('attachments.download', $doc->attachment_id) }}" class="doc-dropdown-item">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    @if(isset($doc->source_task) && $doc->source_task)
                                        <a href="{{ route('tasks.show', ['task' => $doc->source_task->id]) }}"
                                            class="doc-dropdown-item">
                                            <i class="fas fa-tasks"></i> Lihat Tugas
                                        </a>
                                    @endif
                                    <button class="doc-dropdown-item doc-dropdown-danger"
                                        onclick="showInfoModal('Untuk menghapus, buka halaman detail tugas dan hapus dari sana.')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                @elseif($doc->latestVersion && isset($doc->latestVersion->id))
                                    <a href="{{ route('documents.download', ['version' => $doc->latestVersion->id]) }}"
                                        class="doc-dropdown-item">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="{{ route('documents.show', ['document' => $doc->id]) }}" class="doc-dropdown-item">
                                        <i class="fas fa-history"></i> Lihat Versi
                                    </a>
                                    <button type="button" class="doc-dropdown-item doc-dropdown-danger btn-delete-doc"
                                        data-action="{{ route('documents.destroy', ['document' => $doc->id]) }}"
                                        data-message="Yakin ingin menghapus dokumen ini beserta semua versinya?">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="doc-empty-state">
                    <div class="doc-empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>Belum ada dokumen</h3>
                    <p>Mulai kelola dokumen proyek Anda dengan mengupload file pertama.</p>
                    <a href="{{ route('projects.documents.create', $project) }}" class="doc-upload-btn">
                        <i class="fas fa-plus"></i> Upload File Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteDocumentForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <style>
        .doc-page-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .doc-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .doc-main-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .doc-subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .doc-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .doc-upload-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Search */
        .doc-search-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .doc-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .doc-search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.875rem;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .doc-search-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Stats Row */
        .doc-stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .doc-stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s;
        }

        .doc-stat-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .doc-stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .doc-stat-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .stat-dokumen {
            border-left: 3px solid #ef4444;
        }

        .stat-gambar {
            border-left: 3px solid #ec4899;
        }

        .stat-spreadsheet {
            border-left: 3px solid #22c55e;
        }

        .stat-kode {
            border-left: 3px solid #3b82f6;
        }

        /* Filter Tabs */
        .doc-filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .doc-filter-tab {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .doc-filter-tab:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .doc-filter-tab.active {
            background: #eef2ff;
            color: #6366f1;
            font-weight: 600;
        }

        /* Document List */
        .doc-list-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: visible;
        }

        .doc-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .doc-list-item:last-child {
            border-bottom: none;
        }

        .doc-list-item:hover {
            background: #fafafa;
        }

        .doc-item-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .doc-file-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .doc-item-info {
            min-width: 0;
        }

        .doc-item-title {
            font-weight: 600;
            color: #1e293b;
            text-decoration: none;
            display: block;
            margin-bottom: 0.25rem;
        }

        .doc-item-title:hover {
            color: #6366f1;
        }

        .doc-item-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .doc-meta-dot {
            color: #cbd5e1;
        }

        .doc-version-badge {
            background: #f1f5f9;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
            color: #64748b;
        }

        .doc-item-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .doc-user-info {
            text-align: right;
        }

        .doc-user-name {
            display: block;
            font-weight: 500;
            color: #475569;
            font-size: 0.875rem;
        }

        .doc-user-date {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Dropdown */
        .doc-actions-dropdown {
            position: relative;
        }

        .doc-action-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.15s;
        }

        .doc-action-btn:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .doc-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.25rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            padding: 0.5rem;
            z-index: 9999;
            display: none;
        }

        .doc-dropdown-menu.show {
            display: block;
        }

        .doc-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            color: #475569;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
            transition: background 0.15s;
        }

        .doc-dropdown-item:hover {
            background: #f8fafc;
        }

        .doc-dropdown-danger {
            color: #ef4444;
        }

        .doc-dropdown-danger:hover {
            background: #fef2f2;
        }

        /* Empty State */
        .doc-empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .doc-empty-icon {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #cbd5e1;
        }

        .doc-empty-state h3 {
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .doc-empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .doc-stats-row {
                grid-template-columns: repeat(3, 1fr);
            }

            .doc-filter-tabs {
                overflow-x: auto;
            }

            .doc-item-right {
                flex-direction: column;
                align-items: flex-end;
                gap: 0.5rem;
            }
        }
    </style>

    <script>
        // Dropdown Toggle
        function toggleDocMenu(btn) {
            const menu = btn.nextElementSibling;
            document.querySelectorAll('.doc-dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.doc-actions-dropdown')) {
                document.querySelectorAll('.doc-dropdown-menu').forEach(m => m.classList.remove('show'));
            }
        });

        // Search
        document.getElementById('doc-search').addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.doc-list-item').forEach(item => {
                const name = item.dataset.name;
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });

        // Filter Tabs
        document.querySelectorAll('.doc-filter-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.doc-filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.doc-list-item').forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Delete document handler - uses hidden form and custom modal
        document.querySelectorAll('.btn-delete-doc').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const actionUrl = this.dataset.action;
                const message = this.dataset.message || 'Yakin ingin menghapus?';
                const deleteForm = document.getElementById('deleteDocumentForm');

                showConfirmModal(message, function () {
                    // Set form action and submit
                    deleteForm.action = actionUrl;
                    deleteForm.submit();
                });
            });
        });


    </script>

    <style>
        .doc-upload-btn.disabled {
            background: #94a3b8;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.7;
        }
    </style>
@endsection