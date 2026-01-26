<?php

namespace App\Notifications;

use App\Models\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TimeTrackingOverdue extends Notification
{
    use Queueable;

    public function __construct(
        protected TimeEntry $timeEntry
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $hours = round($this->timeEntry->current_elapsed_seconds / 3600, 1);
        
        return [
            'time_entry_id' => $this->timeEntry->id,
            'task_id' => $this->timeEntry->task_id,
            'task_title' => $this->timeEntry->task->title ?? 'Unknown Task',
            'hours_running' => $hours,
            'title' => '⏰ Timer Berjalan Lebih dari 24 Jam',
            'message' => "Timer untuk tugas \"{$this->timeEntry->task->title}\" sudah berjalan {$hours} jam. Apakah lupa dimatikan?",
            'type' => 'time_tracking_overdue',
        ];
    }
}
