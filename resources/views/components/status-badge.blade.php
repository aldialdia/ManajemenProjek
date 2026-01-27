@props([
    'status' => 'new',
    'type' => 'project', // project, task, priority
])
@php
    $badges = [
        'project' => [
            'new' => ['class' => 'badge-todo', 'label' => 'Baru'],
            'in_progress' => ['class' => 'badge-inprogress', 'label' => 'Sedang Berjalan'],
            'done' => ['class' => 'badge-done', 'label' => 'Selesai'],
            'review' => ['class' => 'badge-warning', 'label' => 'Review'],
        ],
        'task' => [
            'todo' => ['class' => 'badge-todo', 'label' => 'To Do'],
            'in_progress' => ['class' => 'badge-inprogress', 'label' => 'In Progress'],
            'review' => ['class' => 'badge-review', 'label' => 'In Review'],
            'done' => ['class' => 'badge-done', 'label' => 'Done'],
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
