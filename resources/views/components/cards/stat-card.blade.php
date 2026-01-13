@props([
    'title' => '',
    'value' => 0,
    'icon' => 'chart-bar',
    'color' => 'primary',
    'trend' => null,
    'trendValue' => null,
])

@php
    $colorClasses = [
        'primary' => ['bg' => '#e0e7ff', 'icon' => '#6366f1', 'gradient' => 'linear-gradient(135deg, #6366f1, #8b5cf6)'],
        'success' => ['bg' => '#dcfce7', 'icon' => '#22c55e', 'gradient' => 'linear-gradient(135deg, #22c55e, #16a34a)'],
        'warning' => ['bg' => '#fef3c7', 'icon' => '#f59e0b', 'gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)'],
        'danger' => ['bg' => '#fee2e2', 'icon' => '#ef4444', 'gradient' => 'linear-gradient(135deg, #ef4444, #dc2626)'],
        'info' => ['bg' => '#dbeafe', 'icon' => '#3b82f6', 'gradient' => 'linear-gradient(135deg, #3b82f6, #2563eb)'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="stat-card card">
    <div class="stat-card-content">
        <div class="stat-icon" style="background: {{ $colors['bg'] }}; color: {{ $colors['icon'] }};">
            <i class="fas fa-{{ $icon }}"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">{{ $title }}</span>
            <span class="stat-value">{{ $value }}</span>
            @if($trend !== null)
                <span class="stat-trend {{ $trend === 'up' ? 'trend-up' : 'trend-down' }}">
                    <i class="fas fa-arrow-{{ $trend }}"></i>
                    {{ $trendValue }}
                </span>
            @endif
        </div>
    </div>
    <div class="stat-bar" style="background: {{ $colors['gradient'] }};"></div>
</div>

<style>
    .stat-card {
        position: relative;
        overflow: hidden;
    }

    .stat-card-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-title {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
        margin-top: 0.25rem;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .trend-up {
        color: #22c55e;
    }

    .trend-down {
        color: #ef4444;
    }

    .stat-bar {
        height: 4px;
        width: 100%;
    }
</style>
