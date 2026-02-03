## Perbaikan Dashboard Super Admin

Karena file terlalu besar dan kompleks untuk diedit langsung, berikut adalah kode lengkap untuk section yang perlu diganti:

### Ganti section dari line 171-262 dengan kode berikut:

```blade
    <!-- Bottom Row: Project Type Distribution & Task Distribution -->
    <div class="grid grid-cols-2" style="margin-bottom: 1.5rem;">
        <!-- Project Type Distribution (RBB vs Non-RBB) -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-chart-pie text-primary"></i> Distribusi Tipe Project</span>
                <span class="text-muted text-sm">RBB vs Non-RBB</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="projectTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Task Distribution per Member -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-user-check text-success"></i> Distribusi Tugas per Anggota</span>
                <span class="text-muted text-sm">Top 10 Members</span>
            </div>
            <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                @forelse($taskDistribution as $index => $member)
                    <div class="admin-list-item">
                        <div class="rank-badge-small">
                            #{{ $index + 1 }}
                        </div>
                        <div class="item-content">
                            <div class="item-title">{{ $member->name }}</div>
                            <div class="item-meta">
                                <span class="meta-item">
                                    <i class="fas fa-tasks"></i>
                                    {{ $member->total_tasks }} tugas
                                </span>
                                <span class="meta-item text-success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $member->completed_tasks }} selesai
                                </span>
                                <span class="meta-item text-warning">
                                    <i class="fas fa-clock"></i>
                                    {{ $member->pending_tasks }} pending
                                </span>
                            </div>
                        </div>
                        <div class="task-progress-bar">
                            @php
                                $completionRate = $member->total_tasks > 0 ? round(($member->completed_tasks / $member->total_tasks) * 100) : 0;
                            @endphp
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: {{ $completionRate }}%;"></div>
                            </div>
                            <span class="progress-text">{{ $completionRate }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>Belum ada distribusi tugas</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
```

### Tambahkan JavaScript untuk chart RBB/Non-RBB setelah chart projectStatusChart (sekitar line 375):

```javascript
        // Project Type Distribution Chart (RBB vs Non-RBB)
        const typeCtx = document.getElementById('projectTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['RBB', 'Non-RBB'],
                datasets: [{
                    data: [
                        {{ $projectsByType['rbb'] }},
                        {{ $projectsByType['non_rbb'] }}
                    ],
                    backgroundColor: ['#6366f1', '#64748b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
```

### Tambahkan CSS untuk styling baru (sebelum </style>, sekitar line 787):

```css
        /* Task Distribution Styles */
        .rank-badge-small {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            background: #f1f5f9;
            color: #64748b;
        }

        .task-progress-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 100px;
        }

        .progress-bar-container {
            flex: 1;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            min-width: 35px;
            text-align: right;
        }
```
