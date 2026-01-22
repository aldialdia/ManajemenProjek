<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineAdjusted extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected string $oldDeadline,
        protected string $newDeadline,
        protected string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name ?? 'Unknown',
            'old_deadline' => $this->oldDeadline,
            'new_deadline' => $this->newDeadline,
            'reason' => $this->reason,
            'message' => "Deadline tugas \"{$this->task->title}\" diubah dari {$this->oldDeadline} menjadi {$this->newDeadline} karena {$this->reason}.",
        ];
    }
}
