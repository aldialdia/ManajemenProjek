<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tugas Selesai: ' . $this->task->title)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Sebuah tugas telah diselesaikan.')
            ->line('**Tugas:** ' . $this->task->title)
            ->line('**Proyek:** ' . $this->task->project->name)
            ->action('Lihat Tugas', url('/tasks/' . $this->task->id))
            ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_completed',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'message' => 'Tugas "' . $this->task->title . '" telah diselesaikan',
        ];
    }
}
