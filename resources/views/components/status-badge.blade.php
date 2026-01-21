@props([
    'status' => 'active',
    'type' => 'project', // project, task, priority
])
@php
    $badges = [
        'project' => [
            'active' => ['class' => 'badge-success', 'label' => 'Active'],
            'on_hold' => ['class' => 'badge-warning', 'label' => 'On Hold'],
            'completed' => ['class' => 'badge-info', 'label' => 'Completed'],
            'cancelled' => ['class' => 'badge-danger', 'label' => 'Cancelled'],
        ],
        'task' => [
            'todo' => ['class' => 'badge-secondary', 'label' => 'To Do'],
            'in_progress' => ['class' => 'badge-primary', 'label' => 'In Progress'],
            'review' => ['class' => 'badge-warning', 'label' => 'Review'],
            'done' => ['class' => 'badge-info', 'label' => 'Done (Pending)'],
            'done_approved' => ['class' => 'badge-success', 'label' => 'Done (Approved)'],
        ],
        'priority' => [
            'low' => ['class' => 'badge-secondary', 'label' => 'Low'],
            'medium' => ['class' => 'badge-info', 'label' => 'Medium'],
            'high' => ['class' => 'badge-warning', 'label' => 'High'],
            'urgent' => ['class' => 'badge-danger', 'label' => 'Urgent'],
        ],
    ];

    $statusValue = is_object($status) ? $status->value : $status;
    $badge = $badges[$type][$statusValue] ?? ['class' => 'badge-secondary', 'label' => ucfirst($statusValue)];
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $badge['class']]) }}>
    {{ $slot->isEmpty() ? $badge['label'] : $slot }}
</span>
