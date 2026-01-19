@extends('layouts.app')

@section('title', 'Upload Dokumen - ' . $project->name)

@section('content')
    <div class="upload-page-container">
        <!-- Breadcrumb -->
        @if(!empty($fromOverview))
            <a href="{{ route('projects.show', $project) }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Overview Proyek
            </a>
        @else
            <a href="{{ route('projects.documents.index', $project) }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Dokumen
            </a>
        @endif

        <!-- Main Card -->
        <div class="upload-card">
            <div class="upload-card-header">
                <div class="upload-header-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div>
                    <h1 class="upload-title">Upload Dokumen Baru</h1>
                    <p class="upload-subtitle">Tambahkan file ke proyek {{ $project->name }}</p>
                </div>
            </div>

            <form action="{{ route('projects.documents.store', $project) }}" method="POST" enctype="multipart/form-data"
                class="upload-form">
                @csrf
                @if(!empty($fromOverview))
                    <input type="hidden" name="from_overview" value="1">
                @endif

                <!-- Title Input -->
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i> Judul Dokumen
                    </label>
                    <input type="text" name="title" id="title" required class="form-input"
                        placeholder="Contoh: Spesifikasi Sistem v1.0" value="{{ old('title') }}">
                    @error('title')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-file-upload"></i> Pilih File
                    </label>
                    <div class="file-drop-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                        <input type="file" name="file" id="file-input" class="hidden-input" required
                            onchange="handleFileSelect(this)"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.txt,.zip,.rar,.sql,.js,.php,.html,.css,.json,.py">
                        <div class="drop-zone-content" id="drop-content">
                            <div class="drop-zone-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <p class="drop-zone-text">
                                <span class="drop-zone-link">Klik untuk pilih file</span> atau drag & drop di sini
                            </p>
                            <p class="drop-zone-hint">Dokumen, Gambar, Arsip, atau Kode (SQL, JS, PHP, HTML, CSS, JSON, PY) — Maksimal 10MB</p>
                        </div>
                        <div class="file-preview hidden" id="file-preview">
                            <div class="file-preview-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="file-preview-info">
                                <span class="file-preview-name" id="file-name"></span>
                                <span class="file-preview-size" id="file-size"></span>
                            </div>
                            <button type="button" class="file-remove-btn" onclick="removeFile(event)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('file')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- Changelog -->
                <div class="form-group">
                    <label for="changelog" class="form-label">
                        <i class="fas fa-sticky-note"></i> Catatan Versi (Changelog)
                    </label>
                    <textarea name="changelog" id="changelog" rows="3" class="form-input form-textarea"
                        placeholder="Apa isi dokumen ini? (Opsional) - Default: Initial upload">{{ old('changelog') }}</textarea>
                    <span class="form-hint">Catatan ini akan membantu tim memahami isi dokumen.</span>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    @if(!empty($fromOverview))
                        <a href="{{ route('projects.show', $project) }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    @else
                        <a href="{{ route('projects.documents.index', $project) }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    @endif
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .upload-page-container {
            max-width: 640px;
            margin: 0 auto;
        }

        .back-link {
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

        .back-link:hover {
            color: #6366f1;
        }

        .upload-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .upload-card-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .upload-header-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .upload-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .upload-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            margin: 0.25rem 0 0;
        }

        .upload-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: #1e293b;
            margin-bottom: 0.625rem;
        }

        .form-label i {
            color: #6366f1;
            font-size: 0.8rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-hint {
            display: block;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.5rem;
        }

        .form-error {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8rem;
            color: #ef4444;
            margin-top: 0.5rem;
        }

        /* File Drop Zone */
        .file-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafbfc;
        }

        .file-drop-zone:hover,
        .file-drop-zone.dragover {
            border-color: #6366f1;
            background: #eef2ff;
        }

        .hidden-input {
            display: none;
        }

        .drop-zone-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #6366f1;
        }

        .drop-zone-text {
            color: #475569;
            font-size: 0.9rem;
            margin: 0 0 0.5rem;
        }

        .drop-zone-link {
            color: #6366f1;
            font-weight: 600;
        }

        .drop-zone-hint {
            color: #94a3b8;
            font-size: 0.8rem;
            margin: 0;
        }

        /* File Preview */
        .file-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .file-preview.hidden {
            display: none;
        }

        .file-preview-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #d97706;
        }

        .file-preview-info {
            flex: 1;
            text-align: left;
        }

        .file-preview-name {
            display: block;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .file-preview-size {
            font-size: 0.8rem;
            color: #64748b;
        }

        .file-remove-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-remove-btn:hover {
            background: #fecaca;
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #64748b;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #475569;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
    </style>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const dropContent = document.getElementById('drop-content');
        const filePreview = document.getElementById('file-preview');
        const fileInput = document.getElementById('file-input');

        // Drag and drop handlers
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            fileInput.files = dt.files;
            handleFileSelect(fileInput);
        });

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-size').textContent = formatFileSize(file.size);

                dropContent.classList.add('hidden');
                filePreview.classList.remove('hidden');

                // Update icon based on file type
                const ext = file.name.split('.').pop().toLowerCase();
                const iconEl = filePreview.querySelector('.file-preview-icon i');
                const previewIcon = filePreview.querySelector('.file-preview-icon');

                if (['pdf'].includes(ext)) {
                    iconEl.className = 'fas fa-file-pdf';
                    previewIcon.style.background = 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)';
                    previewIcon.style.color = '#ef4444';
                } else if (['doc', 'docx'].includes(ext)) {
                    iconEl.className = 'fas fa-file-word';
                    previewIcon.style.background = 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)';
                    previewIcon.style.color = '#3b82f6';
                } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                    iconEl.className = 'fas fa-file-excel';
                    previewIcon.style.background = 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)';
                    previewIcon.style.color = '#22c55e';
                } else if (['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(ext)) {
                    iconEl.className = 'fas fa-file-image';
                    previewIcon.style.background = 'linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%)';
                    previewIcon.style.color = '#ec4899';
                } else if (['zip', 'rar', '7z'].includes(ext)) {
                    iconEl.className = 'fas fa-file-archive';
                    previewIcon.style.background = 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)';
                    previewIcon.style.color = '#d97706';
                } else {
                    iconEl.className = 'fas fa-file';
                    previewIcon.style.background = 'linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)';
                    previewIcon.style.color = '#64748b';
                }
            }
        }

        function removeFile(e) {
            e.stopPropagation();
            fileInput.value = '';
            dropContent.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
@endsection