<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectDeadlineWarning extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $project
    ) {}

    /**
     * Get the notification's delivery channels.
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
            ->subject('⚠️ Reminder Deadline Project: ' . $this->project->name)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Project berikut akan deadline **besok (H-1)**:')
            ->line('')
            ->line('📁 **Project:** ' . $this->project->name)
            ->line('📅 **Deadline:** ' . $this->project->end_date->format('d M Y'))
            ->line('')
            ->action('Lihat Project', route('projects.show', $this->project))
            ->line('Pastikan semua tugas sudah selesai sebelum deadline.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'title' => '⏰ Deadline Project Besok: ' . $this->project->name,
            'message' => 'Project ini harus selesai besok (H-1).',
            'type' => 'project_deadline_warning',
        ];
    }
}
