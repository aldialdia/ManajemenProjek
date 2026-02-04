{{-- Floating Timer Widget --}}
{{-- This component displays a fixed floating timer in the bottom-right corner when time tracking is active --}}

@auth
    <div id="floatingTimerWidget" class="floating-timer-widget" style="display: none;">
        <div class="floating-timer-header">
            <div class="floating-timer-indicator">
                <span class="pulse-dot"></span>
                <span class="timer-label">Timer Aktif</span>
            </div>
            <div class="floating-timer-actions">
                <a href="#" id="floatingTimerTaskLink" class="floating-timer-task-link" title="Lihat detail tugas">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <button type="button" class="floating-timer-close-btn" onclick="minimizeFloatingTimer()"
                    title="Sembunyikan sementara">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="floating-timer-body">
            <div class="floating-timer-project" id="floatingTimerProject">-</div>
            <div class="floating-timer-task" id="floatingTimerTask">-</div>
            <div class="floating-timer-time" id="floatingTimerTime">00:00:00</div>
        </div>
        <div class="floating-timer-footer">
            <form id="floatingTimerStopForm" method="POST" style="width: 100%;">
                @csrf
                <button type="submit" class="floating-timer-stop-btn">
                    <i class="fas fa-stop"></i> Berhenti
                </button>
            </form>
        </div>
    </div>

    {{-- Timer Close Warning Modal (Calendar-style popup) --}}
    <div id="timerCloseWarningModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 10001; align-items: center; justify-content: center;">
        <div
            style="background: white; border-radius: 16px; padding: 2rem; max-width: 420px; width: 90%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: popIn 0.3s ease-out;">
            <div
                style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-stopwatch" style="font-size: 2rem; color: #d97706;"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">
                Timer Masih Berjalan!
            </h3>
            <p style="color: #64748b; margin-bottom: 0.5rem; line-height: 1.6;">
                <strong style="color: #d97706;">Anda sedang mengerjakan tugas dan timer masih aktif.</strong>
            </p>
            <p style="color: #64748b; margin-bottom: 0.25rem; font-size: 0.875rem;">
                <span id="warningModalTaskName" style="font-weight: 600; color: #1e293b;">-</span>
            </p>
            <p style="margin-bottom: 1.5rem;">
                <span id="warningModalTimer"
                    style="font-size: 1.5rem; font-weight: 700; color: #22c55e; font-variant-numeric: tabular-nums;">00:00:00</span>
            </p>
            <p style="color: #64748b; margin-bottom: 1.5rem; font-size: 0.875rem;">
                Jika Anda menutup tab, timer akan tetap berjalan. Apakah Anda ingin menghentikan timer terlebih dahulu?
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button onclick="closeTimerWarningModal()"
                    style="background: #f1f5f9; color: #64748b; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-times" style="margin-right: 0.5rem;"></i>Batal
                </button>
                <button onclick="stopTimerFromWarning()"
                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-stop" style="margin-right: 0.5rem;"></i>Stop Timer
                </button>
                <button onclick="forceCloseTab()"
                    style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-sign-out-alt" style="margin-right: 0.5rem;"></i>Tutup Saja
                </button>
            </div>
        </div>
    </div>

    <style>
        .floating-timer-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 280px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow:
                0 10px 40px -10px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(0, 0, 0, 0.05);
            z-index: 9999;
            overflow: hidden;
            animation: slideInUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes popIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .floating-timer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }

        .floating-timer-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.8);
            }
        }

        .timer-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .floating-timer-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .floating-timer-task-link {
            color: white;
            opacity: 0.8;
            transition: opacity 0.2s;
            padding: 0.25rem;
        }

        .floating-timer-task-link:hover {
            opacity: 1;
        }

        .floating-timer-close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s;
            opacity: 0.8;
        }

        .floating-timer-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            opacity: 1;
        }

        .floating-timer-body {
            padding: 1rem;
            text-align: center;
        }

        .floating-timer-project {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .floating-timer-task {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .floating-timer-time {
            font-size: 1.75rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #1e293b;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .floating-timer-footer {
            padding: 0 1rem 1rem;
        }

        .floating-timer-stop-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .floating-timer-stop-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .floating-timer-stop-btn:active {
            transform: translateY(0);
        }

        /* Modal button hover effects */
        #timerCloseWarningModal button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .floating-timer-widget {
                right: 10px;
                bottom: 10px;
                width: calc(100% - 20px);
                max-width: 280px;
            }

            #timerCloseWarningModal>div {
                padding: 1.5rem;
            }

            #timerCloseWarningModal>div>div:last-child {
                flex-direction: column;
            }
        }
    </style>

    <script>
        // Global timer state
        window.floatingTimerData = {
            running: false,
            entryId: null,
            taskId: null,
            taskTitle: null,
            startedAt: null,
            intervalId: null,
            isInternalNavigation: false,
            allowClose: false // Flag to allow closing tab
        };

        // Check for active timer on page load
        document.addEventListener('DOMContentLoaded', function () {
            checkGlobalTimerStatus();
            // Check timer status every 30 seconds (in case user starts/stops timer in another tab)
            setInterval(checkGlobalTimerStatus, 30000);

            // Track internal link clicks to allow navigation within the app
            setupInternalLinkTracking();

            // Listen for keyboard shortcut to close (Ctrl+W, Alt+F4)
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey && e.key === 'w') || (e.altKey && e.key === 'F4')) {
                    if (window.floatingTimerData.running && !window.floatingTimerData.allowClose) {
                        e.preventDefault();
                        showTimerWarningModal();
                    }
                }
            });
        });

        // Setup click tracking for internal links to allow navigation without warning
        function setupInternalLinkTracking() {
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && link.href) {
                    const currentHost = window.location.host;
                    const linkUrl = new URL(link.href, window.location.origin);

                    // Check if it's an internal link (same host)
                    if (linkUrl.host === currentHost) {
                        window.floatingTimerData.isInternalNavigation = true;
                    }
                }
            });

            // Also track form submissions as internal navigation
            document.addEventListener('submit', function (e) {
                window.floatingTimerData.isInternalNavigation = true;
            });
        }

        function checkGlobalTimerStatus() {
            fetch('{{ route("time-tracking.global-status") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.running) {
                        showFloatingTimer(data);
                    } else {
                        hideFloatingTimer();
                    }
                })
                .catch(error => {
                    console.error('Error checking timer status:', error);
                });
        }

        function showFloatingTimer(data) {
            const widget = document.getElementById('floatingTimerWidget');
            const projectEl = document.getElementById('floatingTimerProject');
            const taskEl = document.getElementById('floatingTimerTask');
            const timeEl = document.getElementById('floatingTimerTime');
            const taskLink = document.getElementById('floatingTimerTaskLink');
            const stopForm = document.getElementById('floatingTimerStopForm');

            // Update display
            projectEl.textContent = data.project_name;
            taskEl.textContent = data.task_title;
            taskLink.href = '/tasks/' + data.task_id;
            stopForm.action = '/time-tracking/' + data.entry_id + '/stop';

            // Store timer data
            window.floatingTimerData.running = true;
            window.floatingTimerData.entryId = data.entry_id;
            window.floatingTimerData.taskId = data.task_id;
            window.floatingTimerData.taskTitle = data.task_title;
            window.floatingTimerData.startedAt = new Date(data.started_at);

            // Start updating timer display
            if (window.floatingTimerData.intervalId) {
                clearInterval(window.floatingTimerData.intervalId);
            }

            updateFloatingTimerDisplay();
            window.floatingTimerData.intervalId = setInterval(updateFloatingTimerDisplay, 1000);

            // Show widget
            widget.style.display = 'block';

            // Add beforeunload listener for tab close prevention
            window.addEventListener('beforeunload', handleBeforeUnload);
        }

        function hideFloatingTimer() {
            const widget = document.getElementById('floatingTimerWidget');
            widget.style.display = 'none';

            // Clear interval
            if (window.floatingTimerData.intervalId) {
                clearInterval(window.floatingTimerData.intervalId);
                window.floatingTimerData.intervalId = null;
            }

            // Reset state
            window.floatingTimerData.running = false;
            window.floatingTimerData.entryId = null;

            // Remove beforeunload listener
            window.removeEventListener('beforeunload', handleBeforeUnload);
        }

        function updateFloatingTimerDisplay() {
            const timeEl = document.getElementById('floatingTimerTime');
            const startedAt = window.floatingTimerData.startedAt;

            if (!startedAt) return;

            const now = new Date();
            let diff = Math.floor((now - startedAt) / 1000);
            if (diff < 0) diff = 0;

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            const timeStr = String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');

            timeEl.textContent = timeStr;

            // Also update warning modal timer if visible
            const warningTimer = document.getElementById('warningModalTimer');
            if (warningTimer) {
                warningTimer.textContent = timeStr;
            }
        }

        function handleBeforeUnload(e) {
            // Allow close if flag is set
            if (window.floatingTimerData.allowClose) {
                return;
            }

            // Only show warning if timer is running AND it's NOT internal navigation
            if (window.floatingTimerData.running && !window.floatingTimerData.isInternalNavigation) {
                const message = 'Timer sedang berjalan! Apakah Anda yakin ingin menutup tab ini?';
                e.preventDefault();
                e.returnValue = message;
                return message;
            }
            // Reset the flag after checking (in case navigation is cancelled)
            window.floatingTimerData.isInternalNavigation = false;
        }

        // Show custom warning modal
        function showTimerWarningModal() {
            const modal = document.getElementById('timerCloseWarningModal');
            const taskNameEl = document.getElementById('warningModalTaskName');

            if (taskNameEl && window.floatingTimerData.taskTitle) {
                taskNameEl.textContent = window.floatingTimerData.taskTitle;
            }

            modal.style.display = 'flex';
        }

        // Close warning modal (cancel)
        function closeTimerWarningModal() {
            const modal = document.getElementById('timerCloseWarningModal');
            modal.style.display = 'none';
        }

        // Stop timer from warning modal
        function stopTimerFromWarning() {
            const stopForm = document.getElementById('floatingTimerStopForm');
            if (stopForm) {
                stopForm.submit();
            }
            closeTimerWarningModal();
        }

        // Force close tab (allow closing)
        function forceCloseTab() {
            window.floatingTimerData.allowClose = true;
            closeTimerWarningModal();
            // Try to close the tab/window
            window.close();
            // If window.close() doesn't work (browser restriction), show a message
            setTimeout(function () {
                alert('Browser tidak mengizinkan menutup tab secara otomatis. Silakan tutup tab secara manual.');
            }, 100);
        }

        // Minimize/hide floating timer temporarily (will reappear on next page load)
        function minimizeFloatingTimer() {
            const widget = document.getElementById('floatingTimerWidget');
            widget.style.display = 'none';
            // Store minimized state in sessionStorage (resets when browser is closed)
            sessionStorage.setItem('floatingTimerMinimized', 'true');
        }
    </script>
@endauth