<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $assignedBy
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
            ->subject('Tugas Baru Ditugaskan: ' . $this->task->title)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Anda telah ditugaskan untuk mengerjakan tugas baru.')
            ->line('**Tugas:** ' . $this->task->title)
            ->line('**Proyek:** ' . $this->task->project->name)
            ->line('**Ditugaskan oleh:** ' . $this->assignedBy->name)
            ->when($this->task->due_date, function ($message) {
                return $message->line('**Deadline:** ' . $this->task->due_date->format('d M Y'));
            })
            ->action('Lihat Tugas', url('/tasks/' . $this->task->id))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
            'message' => $this->assignedBy->name . ' menugaskan Anda untuk: ' . $this->task->title,
        ];
    }
}
