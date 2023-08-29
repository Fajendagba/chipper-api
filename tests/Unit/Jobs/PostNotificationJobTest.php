<?php

namespace Tests\Unit\Jobs;

use App\Jobs\PostNotificationJob;
use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PostNotificationJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_dispatch_post_notification_job(): void
    {
        Bus::fake();
        Notification::fake();

        $authUser = User::factory()->create();
        $mockUsers = User::factory()->count(2)->create();

        foreach($mockUsers as $user) {
            $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $authUser->id]))
            ->assertCreated();
        }

        $this->actingAs($authUser)->postJson(route('posts.store'), [
            'title' => 'Test Post',
            'body' => 'This is a test post.',
        ]);

        Bus::assertDispatched(PostNotificationJob::class, function (PostNotificationJob $job) use ($authUser) {
            $this->assertTrue($job->user->is($authUser));

            return true;
        });

        (new PostNotificationJob($authUser))->handle();

        Notification::assertSentTo(
            $mockUsers,
            NewPostNotification::class
        );

        Notification::assertCount(2);
    }
}
