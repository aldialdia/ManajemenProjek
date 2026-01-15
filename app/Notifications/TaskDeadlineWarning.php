<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineWarning extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $task
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Reminder Deadline: ' . $this->task->title)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Task berikut akan deadline **besok (H-1)**:')
            ->line('')
            ->line('📋 **Judul:** ' . $this->task->title)
            ->line('📅 **Deadline:** ' . $this->task->due_date->format('d M Y'))
            ->line('📁 **Project:** ' . ($this->task->project->name ?? '-'))
            ->line('')
            ->action('Lihat Task', route('tasks.show', $this->task))
            ->line('Mohon segera diselesaikan sebelum deadline.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => '⏰ Deadline Besok: ' . $this->task->title,
            'message' => 'Task ini harus selesai besok (H-1).',
            'type' => 'deadline_warning',
        ];
    }
}
