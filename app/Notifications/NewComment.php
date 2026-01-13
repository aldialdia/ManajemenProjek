<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public string $targetType, // 'task' or 'project'
        public string $targetTitle,
        public int $targetId
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
        $typeLabel = $this->targetType === 'task' ? 'Tugas' : 'Proyek';
        $url = $this->targetType === 'task'
            ? url('/tasks/' . $this->targetId)
            : url('/projects/' . $this->targetId);

        return (new MailMessage)
            ->subject('Komentar Baru di ' . $typeLabel . ': ' . $this->targetTitle)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line($this->comment->user->name . ' menambahkan komentar:')
            ->line('"' . \Str::limit($this->comment->body, 200) . '"')
            ->line('**' . $typeLabel . ':** ' . $this->targetTitle)
            ->action('Lihat Komentar', $url)
            ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_comment',
            'comment_id' => $this->comment->id,
            'comment_body' => \Str::limit($this->comment->body, 100),
            'commenter_id' => $this->comment->user_id,
            'commenter_name' => $this->comment->user->name,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'target_title' => $this->targetTitle,
            'message' => $this->comment->user->name . ' mengomentari ' .
                ($this->targetType === 'task' ? 'tugas' : 'proyek') . ': ' . $this->targetTitle,
        ];
    }
}
