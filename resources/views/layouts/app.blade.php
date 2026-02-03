<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Project Manager') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Prevent FOUC - Critical CSS that must load before body -->
    <style>
        /* Hide body until ready to prevent FOUC */
        html:not(.loaded) body {
            visibility: hidden;
            opacity: 0;
        }

        html.loaded body {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.2s ease-in, visibility 0s;
        }

        /* Ensure app-container is also hidden initially */
        html:not(.loaded) .app-container {
            visibility: hidden;
        }
    </style>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #f97316;
            --primary-dark: #ea580c;
            --secondary: #64748b;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f4f8 100%);
            min-height: 100vh;
            color: var(--dark);
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .content-wrapper {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--secondary);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        /* Tables */
        .table-container {
            overflow-x: hidden;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--secondary);
        }

        tr:hover {
            background: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-primary {
            background: #e0e7ff;
            color: #3730a3;
        }

        /* Task Status Badges - Matching Kanban Colors */
        .badge-todo {
            background: #f1f5f9;
            color: #64748b;
        }

        .badge-inprogress {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-review {
            background: #ffedd5;
            color: #ea580c;
        }

        .badge-done {
            background: #d1fae5;
            color: #059669;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        .page-subtitle {
            color: var(--secondary);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Grid System */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .grid-cols-2,
            .grid-cols-3,
            .grid-cols-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Avatar */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .avatar-lg {
            width: 56px;
            height: 56px;
            font-size: 1.25rem;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            padding: 0.5rem;
            display: none;
            z-index: 9999;
        }

        .dropdown.active .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--dark);
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f1f5f9;
        }

        .dropdown-item.text-danger {
            color: var(--danger);
        }

        /* Utilities */
        .text-muted {
            color: var(--secondary);
        }

        .text-success {
            color: var(--success);
        }

        .text-danger {
            color: var(--danger);
        }

        .text-primary {
            color: var(--primary);
        }

        .font-bold {
            font-weight: 700;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-4 {
            gap: 1rem;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="app-container">
        @include('layouts.sidebar')

        <div class="main-content">
            @include('layouts.navigation')

            <main class="content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

    <!-- Global Custom Confirm Modal -->
    <div id="confirmModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal-box">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="confirm-modal-title">Konfirmasi</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="confirmModalMessage">Apakah Anda yakin?</p>
            </div>
            <div class="confirm-modal-footer">
                <button type="button" class="confirm-modal-btn confirm-modal-btn-cancel" onclick="closeConfirmModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="confirm-modal-btn confirm-modal-btn-confirm" id="confirmModalOkBtn">
                    <i class="fas fa-check"></i> Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Global Info Modal -->
    <div id="infoModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal-box">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon info">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 class="confirm-modal-title">Informasi</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="infoModalMessage">Pesan informasi</p>
            </div>
            <div class="confirm-modal-footer" style="justify-content: center;">
                <button type="button" class="confirm-modal-btn confirm-modal-btn-confirm" onclick="closeInfoModal()">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>

    <!-- Project On Hold Warning Modal -->
    <div id="projectOnHoldModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal-box">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon warning-hold">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <h3 class="confirm-modal-title">Project Ditunda</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="projectOnHoldMessage">Project ini sedang ditunda. Anda hanya dapat melihat data project.</p>
            </div>
            <div class="confirm-modal-footer" style="justify-content: center;">
                <button type="button" class="confirm-modal-btn confirm-modal-btn-confirm"
                    onclick="closeProjectOnHoldModal()">
                    <i class="fas fa-check"></i> Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Project Deadline Warning Modal -->
    <div id="projectDeadlineModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal-box" style="max-width: 450px;">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-color: #fca5a5; color: #dc2626; box-shadow: 0 8px 24px -4px rgba(220, 38, 38, 0.25);">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="confirm-modal-title">Deadline Project Terlewati</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="deadlineModalMessage">Project ini telah melewati deadline. Anda tidak dapat menambahkan tugas baru.</p>
                <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 12px; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <i class="fas fa-info-circle" style="color: #64748b;"></i>
                        <span style="font-size: 0.875rem; color: #64748b;">Deadline saat ini:</span>
                    </div>
                    <div style="font-weight: 600; color: #dc2626;" id="currentDeadlineDisplay">-</div>
                </div>
                <div style="margin-top: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">
                        <i class="fas fa-calendar-plus" style="color: #16a34a; margin-right: 0.25rem;"></i>
                        Perpanjang deadline ke:
                    </label>
                    <input type="date" id="newDeadlineInput" class="form-control" style="width: 100%;">
                </div>
            </div>
            <div class="confirm-modal-footer">
                <button type="button" class="confirm-modal-btn confirm-modal-btn-cancel" onclick="closeDeadlineModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="confirm-modal-btn confirm-modal-btn-confirm" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); box-shadow: 0 4px 14px -2px rgba(22, 163, 74, 0.4);" id="extendDeadlineBtn">
                    <i class="fas fa-calendar-check"></i> Perpanjang Deadline
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Custom Confirm Modal - Premium Design */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .confirm-modal-box {
            background: white;
            border-radius: 24px;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, 0.03),
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 20px 40px -10px rgba(0, 0, 0, 0.15),
                0 40px 80px -20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 1rem;
            overflow: hidden;
            animation: modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .confirm-modal-header {
            background: linear-gradient(180deg, #fafafa 0%, white 100%);
            padding: 2.5rem 2rem 1.25rem;
            text-align: center;
            position: relative;
        }

        .confirm-modal-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 3px solid #fed7aa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem;
            color: #ea580c;
            box-shadow: 0 8px 24px -4px rgba(249, 115, 22, 0.25);
            animation: iconPulse 2s ease-in-out infinite;
        }

        .confirm-modal-icon.info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #93c5fd;
            color: #2563eb;
            box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.2);
        }

        .confirm-modal-icon.success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #86efac;
            color: #16a34a;
            box-shadow: 0 8px 24px -4px rgba(22, 163, 74, 0.2);
        }

        .confirm-modal-icon.warning-hold {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border-color: #fde047;
            color: #ca8a04;
            box-shadow: 0 8px 24px -4px rgba(202, 138, 4, 0.25);
        }

        .confirm-modal-title {
            font-size: 1.375rem;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .confirm-modal-body {
            padding: 0.75rem 2rem 1.75rem;
            text-align: center;
        }

        .confirm-modal-body p {
            color: #64748b;
            font-size: 0.9375rem;
            line-height: 1.7;
            margin: 0;
        }

        .confirm-modal-footer {
            padding: 0 1.5rem 1.75rem;
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .confirm-modal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 120px;
        }

        .confirm-modal-btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .confirm-modal-btn-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .confirm-modal-btn-cancel:active {
            transform: translateY(0);
        }

        .confirm-modal-btn-confirm {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            box-shadow: 0 4px 14px -2px rgba(249, 115, 22, 0.4);
        }

        .confirm-modal-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(249, 115, 22, 0.5);
        }

        .confirm-modal-btn-confirm:active {
            transform: translateY(0);
        }

        .confirm-modal-btn-confirm.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 14px -2px rgba(239, 68, 68, 0.4);
        }

        .confirm-modal-btn-confirm.danger:hover {
            box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.5);
        }
    </style>

    <script>
        // Global confirm modal handler
        let confirmModalCallback = null;

        function showConfirmModal(message, onConfirm) {
            const modal = document.getElementById('confirmModal');
            const messageEl = document.getElementById('confirmModalMessage');
            const okBtn = document.getElementById('confirmModalOkBtn');

            messageEl.textContent = message;
            confirmModalCallback = onConfirm;

            // Remove old listener and add new one
            const newOkBtn = okBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOkBtn, okBtn);
            newOkBtn.id = 'confirmModalOkBtn';
            newOkBtn.innerHTML = '<i class="fas fa-check"></i> Ya, Lanjutkan';

            newOkBtn.addEventListener('click', function () {
                // Store callback before closing (which nullifies it)
                const callback = confirmModalCallback;
                closeConfirmModal();
                if (callback) {
                    callback();
                }
            });

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            confirmModalCallback = null;
        }

        // Close on overlay click
        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
            }
        });

        // Helper function for form submission with confirmation
        function confirmSubmit(form, message) {
            // Check if already confirmed (bypass second call)
            if (form.dataset.confirmed === 'true') {
                return true; // Allow form submission
            }

            showConfirmModal(message, function () {
                form.dataset.confirmed = 'true';
                // Use requestSubmit if available (works with submit event handlers)
                // Otherwise use the native submit which bypasses handlers
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    // Fallback: directly call native submit
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
            return false;
        }

        // Info modal functions
        function showInfoModal(message) {
            const modal = document.getElementById('infoModal');
            const messageEl = document.getElementById('infoModalMessage');
            messageEl.textContent = message;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeInfoModal() {
            const modal = document.getElementById('infoModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close info modal on overlay click
        document.getElementById('infoModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeInfoModal();
            }
        });

        // Project On Hold Modal Functions
        function showProjectOnHoldModal(message) {
            const modal = document.getElementById('projectOnHoldModal');
            const messageEl = document.getElementById('projectOnHoldMessage');
            if (message) {
                messageEl.textContent = message;
            }
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeProjectOnHoldModal() {
            const modal = document.getElementById('projectOnHoldModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close project on hold modal on overlay click
        document.getElementById('projectOnHoldModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeProjectOnHoldModal();
            }
        });

        // Project Deadline Modal Functions
        let deadlineModalProjectId = null;
        let deadlineModalRedirectUrl = null;

        function showDeadlineModal(projectId, currentDeadline, redirectUrl) {
            const modal = document.getElementById('projectDeadlineModal');
            const currentDisplay = document.getElementById('currentDeadlineDisplay');
            const newDeadlineInput = document.getElementById('newDeadlineInput');
            
            deadlineModalProjectId = projectId;
            deadlineModalRedirectUrl = redirectUrl;
            
            // Display current deadline
            if (currentDeadline) {
                const deadlineDate = new Date(currentDeadline);
                currentDisplay.textContent = deadlineDate.toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            } else {
                currentDisplay.textContent = 'Tidak ditentukan';
            }
            
            // Set min date for new deadline input (tomorrow)
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            newDeadlineInput.min = tomorrow.toISOString().split('T')[0];
            newDeadlineInput.value = '';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDeadlineModal() {
            const modal = document.getElementById('projectDeadlineModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            deadlineModalProjectId = null;
            deadlineModalRedirectUrl = null;
        }

        // Close deadline modal on overlay click
        document.getElementById('projectDeadlineModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeadlineModal();
            }
        });

        // Extend deadline button click handler
        document.getElementById('extendDeadlineBtn').addEventListener('click', function() {
            const newDeadline = document.getElementById('newDeadlineInput').value;
            
            if (!newDeadline) {
                alert('Silakan pilih tanggal deadline baru.');
                return;
            }
            
            if (!deadlineModalProjectId) {
                closeDeadlineModal();
                return;
            }
            
            // Disable button during request
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            // Call the updateEndDate API
            fetch(`/projects/${deadlineModalProjectId}/update-end-date`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    end_date: newDeadline,
                    confirmed: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success and redirect
                    closeDeadlineModal();
                    if (deadlineModalRedirectUrl) {
                        window.location.href = deadlineModalRedirectUrl;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Gagal memperpanjang deadline: ' + (data.message || 'Terjadi kesalahan'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-calendar-check"></i> Perpanjang Deadline';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memperpanjang deadline. Silakan coba lagi.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-calendar-check"></i> Perpanjang Deadline';
            });
        });

        // Function to check deadline before navigating to task creation
        function checkDeadlineBeforeCreateTask(event, projectId, endDate, createTaskUrl) {
            // Check if deadline has passed (today or before)
            if (endDate) {
                const deadline = new Date(endDate);
                deadline.setHours(0, 0, 0, 0);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (deadline <= today) {
                    event.preventDefault();
                    showDeadlineModal(projectId, endDate, createTaskUrl);
                    return false;
                }
            }
            return true;
        }

        // Mark page as loaded after stylesheets are ready
        // This prevents FOUC when navigating between pages
        function markAsLoaded() {
            document.documentElement.classList.add('loaded');
        }

        // Check if document is already loaded
        if (document.readyState === 'complete') {
            markAsLoaded();
        } else {
            window.addEventListener('load', markAsLoaded);
        }

        // Also mark as loaded on DOMContentLoaded as fallback with slight delay
        // This ensures styles have been applied
        document.addEventListener('DOMContentLoaded', function () {
            // Small delay to ensure CSS is parsed
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    markAsLoaded();
                });
            });
        });
    </script>
</body>

</html>