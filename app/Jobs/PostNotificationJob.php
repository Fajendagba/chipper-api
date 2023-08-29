<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class PostNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job to send a new post notification to the concerned users.
     */
    public function handle(): void
    {
        $followers = $this->user->followers();

        $followers->chunk(100, fn ($users) => $users->each(function ($user) {
            $user->notify(new NewPostNotification($this->user));
        }));
    }
}
