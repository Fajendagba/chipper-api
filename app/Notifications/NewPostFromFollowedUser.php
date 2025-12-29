<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostFromFollowedUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Post $post
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Post from ' . $this->post->user->name)
            ->line($this->post->user->name . ' just dropped a new post: "' . $this->post->title . '"')
            ->action('View Post', url('/posts/' . $this->post->id))
            ->line('Thank you for using Chipper!');
    }
}
