<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Notifications\NewPostFromFollowedUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewPostNotificationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_followers_are_notified_when_user_creates_post()
    {
        Notification::fake();

        $author = User::factory()->create();
        $follower = User::factory()->create();

        $this->actingAs($follower)
            ->postJson(route('favorites.store.user', ['user' => $author]));

        $this->actingAs($author)
            ->postJson(route('posts.store'), [
                'title' => 'New Post',
                'body' => 'Post content',
            ]);

        Notification::assertSentTo($follower, NewPostFromFollowedUser::class);
        Notification::assertNotSentTo($author, NewPostFromFollowedUser::class);
    }

    public function test_non_followers_are_not_notified()
    {
        Notification::fake();

        $author = User::factory()->create();
        $nonFollower = User::factory()->create();

        $this->actingAs($author)
            ->postJson(route('posts.store'), [
                'title' => 'New Post',
                'body' => 'Post content',
            ]);

        Notification::assertNotSentTo($nonFollower, NewPostFromFollowedUser::class);
    }

    public function test_notification_uses_queue()
    {
        $notification = new NewPostFromFollowedUser(
            Post::factory()->make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $notification);
    }
}
