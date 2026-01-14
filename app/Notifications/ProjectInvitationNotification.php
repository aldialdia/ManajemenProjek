<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ProjectInvitation $invitation
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_invitation',
            'message' => $this->invitation->inviter->name . ' mengundang Anda bergabung ke project "' . $this->invitation->project->name . '" sebagai ' . ucfirst($this->invitation->role),
            'invitation_id' => $this->invitation->id,
            'invitation_token' => $this->invitation->token,
            'project_id' => $this->invitation->project_id,
            'project_name' => $this->invitation->project->name,
            'inviter_id' => $this->invitation->invited_by,
            'inviter_name' => $this->invitation->inviter->name,
            'role' => $this->invitation->role,
        ];
    }
}
