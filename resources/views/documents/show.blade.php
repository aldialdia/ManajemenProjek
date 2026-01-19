@extends('layouts.app')

@section('title', $document->title . ' - Detail Dokumen')

@section('content')
    <div class="doc-detail-container">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="doc-back-link">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        <!-- Main Card -->
        <div class="doc-detail-card">
            <div class="doc-detail-header">
                <div class="doc-detail-icon">
                    @php
                        $ext = strtolower(pathinfo($document->latestVersion->file_path ?? '', PATHINFO_EXTENSION));
                        $iconClass = 'fa-file';
                        if (in_array($ext, ['pdf']))
                            $iconClass = 'fa-file-pdf';
                        elseif (in_array($ext, ['doc', 'docx']))
                            $iconClass = 'fa-file-word';
                        elseif (in_array($ext, ['xls', 'xlsx']))
                            $iconClass = 'fa-file-excel';
                        elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif']))
                            $iconClass = 'fa-file-image';
                        elseif (in_array($ext, ['zip', 'rar']))
                            $iconClass = 'fa-file-archive';
                    @endphp
                    <i class="fas {{ $iconClass }}"></i>
                </div>
                <div class="doc-detail-info">
                    <h1 class="doc-detail-title">{{ $document->title }}</h1>
                    <div class="doc-detail-meta">
                        <span class="doc-version-tag">v{{ $document->latestVersion->version_number ?? 1 }}</span>
                        <span class="doc-meta-separator">•</span>
                        <span><i class="far fa-clock"></i> {{ $document->updated_at->diffForHumans() }}</span>
                        <span class="doc-meta-separator">•</span>
                        <span><i class="far fa-folder"></i> {{ $document->project->name }}</span>
                    </div>
                </div>
            </div>

            <div class="doc-detail-actions">
                @if($document->latestVersion)
                    <a href="{{ route('documents.download', $document->latestVersion) }}" class="doc-btn doc-btn-secondary">
                        <i class="fas fa-download"></i> Download Terbaru
                    </a>
                @endif
                <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                    class="doc-btn doc-btn-primary">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Versi Baru
                </button>
            </div>
        </div>

        <!-- Version History -->
        <div class="doc-history-section">
            <div class="doc-history-header">
                <h2 class="doc-history-title">
                    <i class="fas fa-history"></i> Riwayat Versi
                </h2>
                <form action="{{ route('documents.destroy', $document) }}" method="POST"
                    onsubmit="return confirmSubmit(this, 'Yakin ingin menghapus dokumen ini beserta SEMUA versinya? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="doc-btn doc-btn-danger">
                        <i class="fas fa-trash"></i> Hapus Semua
                    </button>
                </form>
            </div>

            <div class="doc-history-list">
                @foreach($document->versions as $version)
                    <div class="doc-history-item {{ $loop->first ? 'latest' : '' }}">
                        <div class="doc-history-left">
                            <div class="doc-history-version">
                                <span class="version-number">v{{ $version->version_number }}</span>
                                @if($loop->first)
                                    <span class="version-latest">TERBARU</span>
                                @endif
                            </div>
                            <div class="doc-history-changelog">
                                {{ $version->changelog ?? 'Tidak ada catatan' }}
                            </div>
                        </div>
                        <div class="doc-history-center">
                            <div class="doc-history-user">
                                <div class="user-avatar">{{ $version->uploader->initials ?? '?' }}</div>
                                <span class="user-name">{{ $version->uploader->name ?? 'Unknown' }}</span>
                            </div>
                        </div>
                        <div class="doc-history-right">
                            <span class="doc-history-date">{{ $version->created_at->format('d M Y, H:i') }}</span>
                            <div class="doc-history-actions">
                                <a href="{{ route('documents.download', $version) }}" class="doc-download-link"
                                    title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($document->versions->count() > 1 || !$loop->first)
                                    <form action="{{ route('document-versions.destroy', $version) }}" method="POST"
                                        onsubmit="return confirmSubmit(this, 'Hapus versi {{ $version->version_number }}?')"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="doc-delete-link" title="Hapus versi ini">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="doc-modal hidden">
        <div class="doc-modal-backdrop" onclick="document.getElementById('upload-modal').classList.add('hidden')"></div>
        <div class="doc-modal-content">
            <div class="doc-modal-header">
                <h3>Upload Versi Baru</h3>
                <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="doc-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('documents.add-version', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="doc-modal-body">
                    <div class="doc-modal-notice">
                        <i class="fas fa-info-circle"></i>
                        <p>Anda akan mengupload <strong>Versi
                                {{ ($document->latestVersion->version_number ?? 0) + 1 }}</strong>. File lama tidak akan
                            tertimpa dan tetap bisa diakses.</p>
                    </div>

                    <div class="doc-form-group">
                        <label>File Versi Baru</label>
                        <div class="doc-file-input">
                            <input type="file" name="file" id="version-file" required>
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Pilih file atau drag & drop</span>
                            </div>
                        </div>
                    </div>

                    <div class="doc-form-group">
                        <label>Apa yang berubah?</label>
                        <textarea name="changelog" required rows="3"
                            placeholder="Contoh: Perbaikan typo di halaman 5..."></textarea>
                    </div>
                </div>

                <div class="doc-modal-footer">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"
                        class="doc-btn doc-btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="doc-btn doc-btn-primary">
                        <i class="fas fa-upload"></i> Upload Versi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .doc-detail-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .doc-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }

        .doc-back-link:hover {
            color: #6366f1;
        }

        /* Main Card */
        .doc-detail-card {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .doc-detail-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .doc-detail-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }

        .doc-detail-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin: 0 0 0.5rem;
        }

        .doc-detail-meta {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex-wrap: wrap;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
        }

        .doc-version-tag {
            background: rgba(255, 255, 255, 0.25);
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .doc-meta-separator {
            opacity: 0.5;
        }

        .doc-detail-actions {
            display: flex;
            gap: 0.75rem;
        }

        .doc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .doc-btn-primary {
            background: white;
            color: #4f46e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .doc-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .doc-btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .doc-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .doc-btn-cancel {
            background: #f1f5f9;
            color: #64748b;
        }

        .doc-btn-cancel:hover {
            background: #e2e8f0;
        }

        /* History Section */
        .doc-history-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .doc-history-title {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 1.5rem;
        }

        .doc-history-title i {
            color: #6366f1;
        }

        .doc-history-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .doc-history-item {
            display: flex;
            align-items: center;
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .doc-history-item:last-child {
            border-bottom: none;
        }

        .doc-history-item:hover {
            background: #fafafa;
        }

        .doc-history-item.latest {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border-radius: 12px;
            margin-bottom: 0.5rem;
        }

        .doc-history-left {
            flex: 1;
        }

        .doc-history-version {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.375rem;
        }

        .version-number {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .version-latest {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .doc-history-changelog {
            color: #64748b;
            font-size: 0.8rem;
        }

        .doc-history-center {
            width: 180px;
        }

        .doc-history-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .user-name {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 500;
        }

        .doc-history-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.375rem;
        }

        .doc-history-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .doc-download-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: #6366f1;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
        }

        .doc-download-link:hover {
            text-decoration: underline;
        }

        /* Modal */
        .doc-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doc-modal.hidden {
            display: none;
        }

        .doc-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .doc-modal-content {
            position: relative;
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            margin: 1rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .doc-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .doc-modal-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .doc-modal-close {
            width: 36px;
            height: 36px;
            border: none;
            background: #f1f5f9;
            border-radius: 10px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .doc-modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .doc-modal-body {
            padding: 1.5rem;
        }

        .doc-modal-notice {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            background: #fef3c7;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #92400e;
        }

        .doc-modal-notice i {
            font-size: 1rem;
            margin-top: 0.125rem;
        }

        .doc-form-group {
            margin-bottom: 1.25rem;
        }

        .doc-form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .doc-form-group textarea {
            width: 100%;
            padding: 0.875rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.875rem;
            resize: vertical;
            transition: all 0.2s;
        }

        .doc-form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .doc-file-input {
            position: relative;
        }

        .doc-file-input input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 2rem;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            color: #64748b;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .file-input-label i {
            font-size: 1.5rem;
            color: #6366f1;
        }

        .doc-file-input:hover .file-input-label {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .doc-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
        }

        @media (max-width: 640px) {
            .doc-detail-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .doc-history-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .doc-history-center,
            .doc-history-right {
                width: 100%;
                text-align: left;
                align-items: flex-start;
            }
        }

        .file-input-label.has-file {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .file-input-label.has-file i {
            color: #22c55e;
        }

        .selected-file-name {
            font-weight: 600;
            color: #1e293b;
            word-break: break-all;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('version-file');
            const fileLabel = fileInput.closest('.doc-file-input').querySelector('.file-input-label');
            const originalContent = fileLabel.innerHTML;

            fileInput.addEventListener('change', function (e) {
                if (this.files && this.files.length > 0) {
                    const fileName = this.files[0].name;
                    const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                    fileLabel.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <span class="selected-file-name">${fileName}</span>
                    <span style="font-size: 0.75rem; color: #64748b;">${fileSize} MB</span>
                `;
                    fileLabel.classList.add('has-file');
                } else {
                    fileLabel.innerHTML = originalContent;
                    fileLabel.classList.remove('has-file');
                }
            });

            // Drag and drop support
            const dropZone = fileInput.closest('.doc-file-input');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    fileLabel.style.borderColor = '#6366f1';
                    fileLabel.style.background = '#f5f3ff';
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    if (!fileInput.files || fileInput.files.length === 0) {
                        fileLabel.style.borderColor = '#cbd5e1';
                        fileLabel.style.background = 'transparent';
                    }
                });
            });

            dropZone.addEventListener('drop', function (e) {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
@endsection