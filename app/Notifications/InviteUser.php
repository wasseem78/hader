<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class InviteUser extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = URL::temporarySignedRoute(
            'admin.users.accept-invite',
            Carbon::now()->addDays(3),
            ['user' => $this->user->uuid]
        );

        return (new MailMessage)
            ->subject('Invitation to Join Attendance System')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('You have been invited to join the Attendance Management System.')
            ->action('Accept Invitation', $url)
            ->line('This link will expire in 3 days.')
            ->line('If you did not expect this invitation, no further action is required.');
    }
}
