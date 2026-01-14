<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public User $mentionedBy,
        public string $targetType,
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
        $typeLabel = $this->targetType === 'task' ? 'tugas' : 'proyek';
        $url = $this->targetType === 'task'
            ? url('/tasks/' . $this->targetId)
            : url('/projects/' . $this->targetId);

        return (new MailMessage)
            ->subject($this->mentionedBy->name . ' menyebut Anda dalam komentar')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line($this->mentionedBy->name . ' menyebut Anda dalam komentar di ' . $typeLabel . ' "' . $this->targetTitle . '":')
            ->line('"' . \Str::limit($this->comment->body, 200) . '"')
            ->action('Lihat Komentar', $url)
            ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_mentioned',
            'comment_id' => $this->comment->id,
            'comment_body' => \Str::limit($this->comment->body, 100),
            'mentioned_by_id' => $this->mentionedBy->id,
            'mentioned_by_name' => $this->mentionedBy->name,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'target_title' => $this->targetTitle,
            'message' => $this->mentionedBy->name . ' menyebut Anda di ' .
                ($this->targetType === 'task' ? 'tugas' : 'proyek') . ': ' . $this->targetTitle,
        ];
    }
}
