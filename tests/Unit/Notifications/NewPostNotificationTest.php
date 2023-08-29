<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class NewPostNotificationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_user_can_send_post_notifications_to_followers(): void
    {
        Notification::fake();

        $authUser = User::factory()->create();
        $mockUser = User::factory()->create();

        $this->actingAs($mockUser)
            ->postJson(route('favorites.user.store', ['user' => $authUser->id]))
            ->assertCreated();

        $this->actingAs($authUser)->postJson(route('posts.store'), [
            'title' => 'Test Post',
            'body' => 'This is a test post.',
        ]);

        Notification::assertSentTo(
            [$mockUser],
            NewPostNotification::class
        );

        Notification::assertCount(1);
    }
}
