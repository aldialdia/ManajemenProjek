@extends('layouts.app')

@section('title', 'Kalender & Timeline')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kalender & Timeline</h1>
            <p class="page-subtitle">Lihat jadwal, deadline, dan timeline proyek</p>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <!-- Prev/Next Buttons -->
                    <button id="cal-prev" class="btn btn-secondary" style="padding: 0.5rem 0.75rem;" title="Bulan Sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div style="position: relative;">
                        <div id="calendar-title-btn" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.125rem; font-weight: 600; color: #1e293b; cursor: pointer;">
                            <i class="far fa-calendar-alt"></i>
                            <span id="calendar-title">Loading...</span>
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: #64748b;"></i>
                        </div>
                        <!-- Dropdown Menu -->
                        <div id="month-dropdown" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 0.5rem; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 100; min-width: 200px; padding: 0.5rem 0;">
                            <div style="padding: 0.5rem 1rem; border-bottom: 1px solid #e2e8f0;">
                                <select id="cal-year" class="form-control" style="width: 100%;">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 3; $y++)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div id="month-list" style="max-height: 250px; overflow-y: auto;">
                                <div class="month-option" data-month="0" style="padding: 0.5rem 1rem; cursor: pointer;">Januari</div>
                                <div class="month-option" data-month="1" style="padding: 0.5rem 1rem; cursor: pointer;">Februari</div>
                                <div class="month-option" data-month="2" style="padding: 0.5rem 1rem; cursor: pointer;">Maret</div>
                                <div class="month-option" data-month="3" style="padding: 0.5rem 1rem; cursor: pointer;">April</div>
                                <div class="month-option" data-month="4" style="padding: 0.5rem 1rem; cursor: pointer;">Mei</div>
                                <div class="month-option" data-month="5" style="padding: 0.5rem 1rem; cursor: pointer;">Juni</div>
                                <div class="month-option" data-month="6" style="padding: 0.5rem 1rem; cursor: pointer;">Juli</div>
                                <div class="month-option" data-month="7" style="padding: 0.5rem 1rem; cursor: pointer;">Agustus</div>
                                <div class="month-option" data-month="8" style="padding: 0.5rem 1rem; cursor: pointer;">September</div>
                                <div class="month-option" data-month="9" style="padding: 0.5rem 1rem; cursor: pointer;">Oktober</div>
                                <div class="month-option" data-month="10" style="padding: 0.5rem 1rem; cursor: pointer;">November</div>
                                <div class="month-option" data-month="11" style="padding: 0.5rem 1rem; cursor: pointer;">Desember</div>
                            </div>
                        </div>
                    </div>
                    
                    <button id="cal-next" class="btn btn-secondary" style="padding: 0.5rem 0.75rem;" title="Bulan Berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <button id="cal-today" class="btn btn-secondary">
                    Hari Ini
                </button>
            </div>

            <div id="calendar"></div>
        </div>
    </div>

    <!-- Gantt Chart Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1e293b; margin-bottom: 1.5rem;">Gantt Chart</h3>
            
            @if($ganttTasks->count() > 0)
                <div style="overflow-x: auto; position: relative;">
                    <svg id="gantt"></svg>
                    @if(!$isManager)
                    <div id="gantt-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 50; cursor: pointer;"></div>
                    @endif
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: #64748b;">
                    <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                    <p>Belum ada tugas dengan tanggal mulai dan selesai untuk ditampilkan.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Legend Section (Dynamic) -->
    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                <span style="font-size: 0.875rem; font-weight: 600; color: #475569;">Keterangan Status:</span>
                @foreach(\App\Enums\TaskStatus::cases() as $status)
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 1rem; height: 1rem; border-radius: 4px; background-color: {{ $status->hexColor() }};"></div>
                    <span style="font-size: 0.875rem; color: #64748b;">{{ $status->label() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Custom Popup Modal -->
    <div id="deadline-popup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: popIn 0.3s ease-out;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-times" style="font-size: 2rem; color: #ef4444;"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Tanggal Tidak Valid</h3>
            <p style="color: #64748b; margin-bottom: 1.5rem; line-height: 1.6;">
                <strong style="color: #ef4444;">Deadline tidak bisa diubah ke tanggal yang sudah lewat.</strong><br>
                <span style="font-size: 0.875rem;">Silakan pilih tanggal hari ini atau setelahnya.</span>
            </p>
            <button onclick="closeDeadlinePopup()" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                <i class="fas fa-check" style="margin-right: 0.5rem;"></i>Mengerti
            </button>
        </div>
    </div>

    <!-- Project Deadline Popup Modal -->
    <div id="project-deadline-popup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: popIn 0.3s ease-out;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-project-diagram" style="font-size: 2rem; color: #dc2626;"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Melebihi Deadline Project</h3>
            <p style="color: #64748b; margin-bottom: 1.5rem; line-height: 1.6;">
                <strong style="color: #dc2626;">Deadline tugas tidak boleh melebihi deadline project.</strong><br>
                <span style="font-size: 0.875rem;">Deadline project: {{ $projectEndDate ?? '-' }}</span>
            </p>
            <button onclick="closeProjectDeadlinePopup()" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                <i class="fas fa-check" style="margin-right: 0.5rem;"></i>Mengerti
            </button>
        </div>
    </div>

    @push('styles')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css" />
        <style>
            /* Calendar Customization */
            .fc-header-toolbar { display: none !important; } /* Hide default toolbar */
            .fc-theme-standard td, .fc-theme-standard th { border-color: #f3f4f6; }
            .fc-col-header-cell { padding: 12px 0; background: #fff; }
            .fc-daygrid-day-frame { padding: 8px; }
            .fc-event { border-radius: 4px; border: none; font-size: 11px; padding: 2px 4px; margin-bottom: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            
            /* Make dragged event follow cursor - hide default mirror and ghost */
            .fc-event-mirror,
            .fc-event-dragging {
                display: none !important;
            }
            
            /* Custom drag helper */
            .fc-drag-helper {
                position: fixed !important;
                z-index: 99999 !important;
                pointer-events: none !important;
                background: #3b82f6;
                color: white;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                white-space: nowrap;
            }
            
            /* Gantt Customization */
            .bar-wrapper { cursor: pointer; }
            .bar-progress { fill: #6366f1 !important; }
            
            /* Disable drag for non-managers - block all SVG interactions */
            @if(!$isManager)
            #gantt svg * {
                pointer-events: none !important;
                cursor: default !important;
                user-select: none;
                -webkit-user-drag: none;
            }
            #gantt svg .bar-wrapper {
                pointer-events: auto !important;
                cursor: pointer !important;
            }
            @endif
            
            /* Hide default popup, show on bar hover */
            .gantt .popup-wrapper { 
                display: none !important;
            }
            
            /* Custom tooltip on hover */
            .bar-wrapper {
                position: relative;
            }
            
            .gantt-tooltip {
                position: fixed;
                background: white;
                border-radius: 8px;
                padding: 0.75rem 1rem;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 1000;
                font-size: 0.8rem;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s;
                min-width: 150px;
            }
            
            .gantt-tooltip.show {
                opacity: 1;
            }
            
            .gantt-tooltip-title {
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.25rem;
            }
            
            .gantt-tooltip-dates {
                color: #64748b;
                font-size: 0.75rem;
            }
            
            /* Gantt Header - Month sections more visible */
            .gantt .grid-header { background: #f8fafc; }
            .gantt .upper-header rect { fill: #e0e7ff !important; }
            .gantt .upper-header text { fill: #4338ca !important; font-weight: 600 !important; font-size: 14px !important; }
            .gantt .lower-header rect { fill: #f1f5f9 !important; }
            .gantt .lower-header text { fill: #64748b !important; }
            .gantt .grid-row { fill: #ffffff; }
            .gantt .grid-row:nth-child(even) { fill: #fafafa; }
            .gantt .row-line { stroke: #e2e8f0; }
            .gantt .tick { stroke: #e2e8f0; stroke-dasharray: 5,5; }
            .gantt .today-highlight { fill: #dbeafe !important; opacity: 0.5; }
            
            /* Gantt Status Colors (Dynamic) */
            @foreach(\App\Enums\TaskStatus::cases() as $status)
            .bar-{{ $status->value }} .bar { fill: {{ $status->ganttColors()['bar'] }} !important; }
            .bar-{{ $status->value }} .bar-progress { fill: {{ $status->ganttColors()['progress'] }} !important; }
            @endforeach
            
            /* Subtask bar styling */
            .subtask-bar .bar {
                opacity: 0.85;
            }
            .subtask-bar .bar-label {
                font-style: italic;
            }
            
            /* Popup Animation */
            @keyframes popIn {
                0% { transform: scale(0.8); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
            
            #deadline-popup button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var titleEl = document.getElementById('calendar-title');
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    contentHeight: 'auto',
                    aspectRatio: 1.6,
                    headerToolbar: false, // We use custom toolbar
                    events: [
                        ...@json($calendarTasks),
                        @if($projectEndDate && $project)
                        {
                            id: 'project-deadline-{{ $project->id }}',
                            title: '📌 Deadline Project',
                            start: '{{ $projectEndDate }}',
                            backgroundColor: '#dc2626',
                            borderColor: '#dc2626',
                            textColor: '#ffffff',
                            allDay: true,
                            editable: {{ $isManager ? 'true' : 'false' }},
                            classNames: ['project-deadline-event']
                        }
                        @endif
                    ],
                    editable: {{ $isManager ? 'true' : 'false' }},
                    eventStartEditable: {{ $isManager ? 'true' : 'false' }},
                    eventDurationEditable: false, // No resize in calendar
                    droppable: {{ $isManager ? 'true' : 'false' }},
                    dragScroll: false,
                    dayMaxEvents: 3,
                    eventContent: function(arg) {
                         let title = arg.event.title;
                         return { html: `<div class="font-medium truncate">${title}</div>` };
                    },
                    datesSet: function(info) {
                        titleEl.textContent = info.view.title;
                    },
                    eventClick: function(info) {
                        if (info.event.url) {
                            window.location.href = info.event.url;
                            info.jsEvent.preventDefault();
                        }
                    },
                    eventDragStart: function(info) {
                        // Create custom drag helper that follows cursor
                        const helper = document.createElement('div');
                        helper.className = 'fc-drag-helper';
                        helper.textContent = info.event.title;
                        // Use event's background color (status-based)
                        helper.style.background = info.event.backgroundColor || '#3b82f6';
                        document.body.appendChild(helper);
                        
                        function moveHandler(e) {
                            helper.style.left = (e.clientX + 15) + 'px';
                            helper.style.top = (e.clientY + 15) + 'px';
                        }
                        moveHandler(info.jsEvent); // Initial position
                        document.addEventListener('mousemove', moveHandler);
                        
                        function stopHandler() {
                            helper.remove();
                            document.removeEventListener('mousemove', moveHandler);
                            document.removeEventListener('mouseup', stopHandler);
                        }
                        document.addEventListener('mouseup', stopHandler);
                    },
                    eventDrop: handleDateUpdate
                    // eventResize disabled - calendar only shows due date marker
                });
                calendar.render();

                // Dropdown elements
                const titleBtn = document.getElementById('calendar-title-btn');
                const dropdown = document.getElementById('month-dropdown');
                const yearSelect = document.getElementById('cal-year');
                const monthOptions = document.querySelectorAll('.month-option');
                
                // Toggle dropdown on title click
                titleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    dropdown.style.display = 'none';
                });
                
                // Prevent dropdown close when clicking inside
                dropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Month option click handler
                monthOptions.forEach(function(option) {
                    option.addEventListener('mouseover', function() {
                        this.style.background = '#f1f5f9';
                    });
                    option.addEventListener('mouseout', function() {
                        this.style.background = 'transparent';
                    });
                    option.addEventListener('click', function() {
                        const month = parseInt(this.dataset.month);
                        const year = parseInt(yearSelect.value);
                        calendar.gotoDate(new Date(year, month, 1));
                        dropdown.style.display = 'none';
                    });
                });
                
                // Year select change
                yearSelect.addEventListener('change', function() {
                    const year = parseInt(this.value);
                    const currentDate = calendar.getDate();
                    calendar.gotoDate(new Date(year, currentDate.getMonth(), 1));
                });
                
                // Today button
                document.getElementById('cal-today').addEventListener('click', function() { 
                    calendar.today();
                    yearSelect.value = new Date().getFullYear();
                });
                
                // Prev button
                document.getElementById('cal-prev').addEventListener('click', function() {
                    calendar.prev();
                });
                
                // Next button
                document.getElementById('cal-next').addEventListener('click', function() {
                    calendar.next();
                });

                // Gantt Initialization
                const ganttTasks = @json($ganttTasks);
                
                if (ganttTasks.length > 0) {
                    const isManager = {{ $isManager ? 'true' : 'false' }};
                    
                    new Gantt("#gantt", ganttTasks, {
                        header_height: 50,
                        column_width: 30,
                        step: 24,
                        view_modes: ['Day', 'Week', 'Month'],
                        bar_height: 25,
                        bar_corner_radius: 4,
                        arrow_curve: 5,
                        padding: 18,
                        view_mode: 'Day',
                        date_format: 'YYYY-MM-DD',
                        readonly: !isManager, // Disable editing for non-managers
                        on_date_change: isManager ? function(task, start, end) {
                            updateTaskDates(task.id, moment(start).format('YYYY-MM-DD'), moment(end).format('YYYY-MM-DD'), null, true);
                        } : null,
                        on_click: function (task) {
                            // Navigate to task detail (popup disabled)
                            window.location.href = `/tasks/${task.id}`;
                        },
                        popup_trigger: 'manual', // Disable auto popup
                    });
                    
                    // Remove default popup completely
                    setInterval(function() {
                        document.querySelectorAll('.popup-wrapper').forEach(p => p.remove());
                    }, 100);
                    
                    // Completely disable drag for non-managers
                    if (!isManager) {
                        setTimeout(function() {
                            const ganttEl = document.querySelector('#gantt');
                            if (!ganttEl) return;
                            
                            // Block all drag-related events on bars
                            ganttEl.addEventListener('mousedown', function(e) {
                                const bar = e.target.closest('.bar-wrapper .bar, .handle-group');
                                if (bar) {
                                    e.stopPropagation();
                                    e.preventDefault();
                                }
                            }, true);
                            
                            ganttEl.addEventListener('touchstart', function(e) {
                                const bar = e.target.closest('.bar-wrapper .bar, .handle-group');
                                if (bar) {
                                    e.stopPropagation();
                                    e.preventDefault();
                                }
                            }, true);
                            
                            // Remove drag handles completely
                            document.querySelectorAll('.handle-group').forEach(h => h.remove());
                            
                            // Add click handler on overlay to detect bar clicks
                            const overlay = document.getElementById('gantt-overlay');
                            if (overlay) {
                                overlay.addEventListener('click', function(e) {
                                    const rect = overlay.getBoundingClientRect();
                                    const x = e.clientX;
                                    const y = e.clientY;
                                    
                                    // Find which bar was clicked based on position
                                    document.querySelectorAll('.bar-wrapper').forEach(wrapper => {
                                        const barRect = wrapper.getBoundingClientRect();
                                        if (x >= barRect.left && x <= barRect.right && 
                                            y >= barRect.top && y <= barRect.bottom) {
                                            const taskId = wrapper.getAttribute('data-id');
                                            if (taskId) window.location.href = '/tasks/' + taskId;
                                        }
                                    });
                                });
                            }
                        }, 300);
                    }
                    
                    // Add month start markers
                    setTimeout(function() {
                        const svg = document.querySelector('#gantt');
                        if (svg) {
                            const dates = svg.querySelectorAll('.lower-text');
                            dates.forEach(function(dateText) {
                                const text = dateText.textContent;
                                // Check if it's the 1st of a month
                                if (text === '1' || text === '01') {
                                    const x = parseFloat(dateText.getAttribute('x'));
                                    const gridHeight = svg.querySelector('.grid-background').getBBox().height;
                                    const headerHeight = 50;
                                    
                                    // Create vertical line
                                    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                                    line.setAttribute('x1', x);
                                    line.setAttribute('y1', headerHeight);
                                    line.setAttribute('x2', x);
                                    line.setAttribute('y2', gridHeight + headerHeight);
                                    line.setAttribute('stroke', '#d1d5db');
                                    line.setAttribute('stroke-width', '1');
                                    line.setAttribute('stroke-dasharray', '4,4');
                                    line.classList.add('month-start-line');
                                    svg.appendChild(line);
                                }
                            });
                        }
                    }, 500);
                    
                    // Add project deadline marker on Gantt
                    @if($projectEndDate)
                    setTimeout(function() {
                        const svg = document.querySelector('#gantt');
                        if (svg && ganttTasks.length > 0) {
                            const projectEndDate = moment('{{ $projectEndDate }}');
                            const today = moment().startOf('day');
                            
                            // Find today-highlight element to get reference position
                            const todayHighlight = svg.querySelector('.today-highlight');
                            if (!todayHighlight) {
                                console.log('Today highlight not found');
                                return;
                            }
                            
                            const todayX = parseFloat(todayHighlight.getAttribute('x'));
                            const columnWidth = parseFloat(todayHighlight.getAttribute('width')) || 30;
                            
                            // Calculate days from today to project end date
                            const daysDiff = projectEndDate.diff(today, 'days');
                            const x = todayX + (daysDiff * columnWidth) + columnWidth; // right edge - where bar ends
                            
                            const gridBg = svg.querySelector('.grid-background');
                            if (gridBg) {
                                const gridHeight = gridBg.getBBox().height;
                                const headerHeight = 50;
                                
                                // Create vertical line for project deadline
                                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                                line.setAttribute('x1', x);
                                line.setAttribute('y1', 0);
                                line.setAttribute('x2', x);
                                line.setAttribute('y2', gridHeight + headerHeight);
                                line.setAttribute('stroke', '#dc2626');
                                line.setAttribute('stroke-width', '3');
                                line.classList.add('project-deadline-line');
                                svg.appendChild(line);
                                
                                // Add label at top
                                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                                label.setAttribute('x', x + 5);
                                label.setAttribute('y', 35);
                                label.setAttribute('fill', '#dc2626');
                                label.setAttribute('font-size', '11');
                                label.setAttribute('font-weight', 'bold');
                                label.textContent = '📌 Deadline Project ({{ $projectEndDate }})';
                                svg.appendChild(label);
                            }
                        }
                    }, 800);
                    @endif
                    
                    // Add custom hover tooltip functionality
                    setTimeout(function() {
                        // Create tooltip element
                        const tooltip = document.createElement('div');
                        tooltip.className = 'gantt-tooltip';
                        tooltip.innerHTML = '<div class="gantt-tooltip-title"></div><div class="gantt-tooltip-dates"></div>';
                        document.body.appendChild(tooltip);
                        
                        const bars = document.querySelectorAll('.bar-wrapper');
                        bars.forEach(function(bar) {
                            bar.addEventListener('mouseenter', function(e) {
                                const barLabel = bar.querySelector('.bar-label');
                                const taskName = barLabel ? barLabel.textContent : 'Task';
                                
                                // Get task data from ganttTasks array
                                const taskId = bar.getAttribute('data-id');
                                const task = ganttTasks.find(t => t.id == taskId);
                                
                                tooltip.querySelector('.gantt-tooltip-title').textContent = taskName;
                                tooltip.querySelector('.gantt-tooltip-dates').textContent = task 
                                    ? `${task.start} → ${task.end}` 
                                    : '';
                                
                                tooltip.classList.add('show');
                            });
                            
                            bar.addEventListener('mousemove', function(e) {
                                tooltip.style.left = (e.clientX + 15) + 'px';
                                tooltip.style.top = (e.clientY + 15) + 'px';
                            });
                            
                            bar.addEventListener('mouseleave', function() {
                                tooltip.classList.remove('show');
                            });
                        });
                    }, 600);
                }

                function handleDateUpdate(info) {
                    var newDueDate = info.event.startStr;
                    var today = moment().startOf('day');
                    
                    // Check if this is a project deadline event
                    if (info.event.id && info.event.id.startsWith('project-deadline-')) {
                        // Validate: project deadline cannot be in the past
                        if (moment(newDueDate).isBefore(today)) {
                            info.revert();
                            showDeadlinePopup();
                            return;
                        }
                        
                        // Update project end date
                        const projectId = info.event.id.replace('project-deadline-', '');
                        fetch(`/projects/${projectId}/update-end-date`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ end_date: newDueDate })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show message if tasks were adjusted
                                if (data.adjusted_tasks > 0) {
                                    alert(`Deadline project berhasil diubah. ${data.adjusted_tasks} tugas disesuaikan otomatis.`);
                                }
                                calendar.gotoDate(newDueDate);
                                setTimeout(() => window.location.reload(), 100);
                            } else {
                                info.revert();
                                alert('Gagal memperbarui deadline project.');
                            }
                        })
                        .catch(error => {
                            info.revert();
                            alert('Terjadi kesalahan.');
                        });
                        return;
                    }
                    
                    // Regular task date update
                    // Validate: due date cannot be in the past
                    if (moment(newDueDate).isBefore(today)) {
                        info.revert();
                        showDeadlinePopup();
                        return;
                    }
                    
                    // Validate: due date cannot exceed project end date
                    @if($projectEndDate)
                    var projectEnd = moment('{{ $projectEndDate }}');
                    if (moment(newDueDate).isAfter(projectEnd)) {
                        info.revert();
                        showProjectDeadlinePopup();
                        return;
                    }
                    @endif
                    fetch(`/tasks/${info.event.id}/dates`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ due_date: newDueDate })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Navigate calendar to the month where task was dropped first
                            calendar.gotoDate(newDueDate);
                            // Update year dropdown
                            const newDate = new Date(newDueDate);
                            const yearSelect = document.getElementById('cal-year');
                            if (yearSelect) {
                                yearSelect.value = newDate.getFullYear();
                            }
                            // Reload page to sync Gantt chart
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        } else {
                            info.revert();
                            alert('Gagal memperbarui tanggal: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        info.revert();
                        alert('Terjadi kesalahan: ' + error.message);
                    });
                }

                function updateTaskDates(taskId, start, end, info = null, reloadAfter = false) {
                    // Validate: due date (end) cannot be in the past
                    var today = moment().startOf('day');
                    if (moment(end).isBefore(today)) {
                        showDeadlinePopup();
                        if (reloadAfter) {
                            setTimeout(() => window.location.reload(), 2000); // Reload after popup
                        }
                        return;
                    }
                    
                    // Validate: due date cannot exceed project end date
                    @if($projectEndDate)
                    var projectEnd = moment('{{ $projectEndDate }}');
                    if (moment(end).isAfter(projectEnd)) {
                        showProjectDeadlinePopup();
                        if (reloadAfter) {
                            setTimeout(() => window.location.reload(), 2000);
                        }
                        return;
                    }
                    @endif
                    fetch(`/tasks/${taskId}/dates`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ start_date: start, due_date: end })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && reloadAfter) {
                            // Reload page to sync calendar with Gantt changes
                            window.location.reload();
                        } else if (!data.success && info) {
                            info.revert();
                            alert('Gagal memperbarui tanggal.');
                        }
                    })
                    .catch(error => {
                        if (info) info.revert();
                        alert('Terjadi kesalahan.');
                    });
                }
            });
            
            // Popup Functions
            function showDeadlinePopup() {
                document.getElementById('deadline-popup').style.display = 'flex';
            }
            
            function closeDeadlinePopup() {
                document.getElementById('deadline-popup').style.display = 'none';
            }
            
            function showProjectDeadlinePopup() {
                document.getElementById('project-deadline-popup').style.display = 'flex';
            }
            
            function closeProjectDeadlinePopup() {
                document.getElementById('project-deadline-popup').style.display = 'none';
            }
            
            // Close popup on background click
            document.getElementById('deadline-popup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeadlinePopup();
                }
            });
            
            document.getElementById('project-deadline-popup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeProjectDeadlinePopup();
                }
            });
            
            // Hide Gantt popup when clicking outside
            document.addEventListener('click', function(e) {
                // Check if click is NOT on a bar or the popup itself
                if (!e.target.closest('.bar-wrapper') && !e.target.closest('.popup-wrapper')) {
                    // Remove all Gantt popups
                    document.querySelectorAll('.popup-wrapper').forEach(function(popup) {
                        popup.remove();
                    });
                }
            });
        </script>
    @endpush
@endsection
