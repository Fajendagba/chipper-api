<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_guest_can_not_favorite_a_post()
    {
        $post = Post::factory()->create();

        $this->postJson(route('favorites.store', ['post' => $post]))
            ->assertStatus(401);
    }

    public function test_a_user_can_favorite_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Post::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_remove_a_post_from_his_favorites()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Post::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('favorites.destroy', ['post' => $post]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Post::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_not_remove_a_non_favorited_item()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('favorites.destroy', ['post' => $post]))
            ->assertNotFound();
    }

    public function test_a_user_can_favorite_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store.user', ['user' => $otherUser]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $otherUser->id,
            'favoritable_type' => User::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_cannot_favorite_themselves()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store.user', ['user' => $user]))
            ->assertForbidden();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $user->id,
            'favoritable_type' => User::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_remove_a_user_from_favorites()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store.user', ['user' => $otherUser]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $otherUser->id,
            'favoritable_type' => User::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('favorites.destroy.user', ['user' => $otherUser]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $otherUser->id,
            'favoritable_type' => User::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_guest_cannot_favorite_a_user()
    {
        $user = User::factory()->create();

        $this->postJson(route('favorites.store.user', ['user' => $user]))
            ->assertUnauthorized();
    }

    public function test_favorites_index_returns_correct_structure()
    {
        $user = User::factory()->create();
        $postAuthor = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);
        $favoritedUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]));

        $this->actingAs($user)
            ->postJson(route('favorites.store.user', ['user' => $favoritedUser]));

        $response = $this->actingAs($user)
            ->getJson(route('favorites.index'))
            ->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'posts' => [
                    '*' => ['id', 'title', 'body', 'user' => ['id', 'name']],
                ],
                'users' => [
                    '*' => ['id', 'name'],
                ],
            ],
        ]);

        $response->assertJsonPath('data.posts.0.id', $post->id);
        $response->assertJsonPath('data.posts.0.title', $post->title);
        $response->assertJsonPath('data.posts.0.body', $post->body);
        $response->assertJsonPath('data.posts.0.user.id', $postAuthor->id);
        $response->assertJsonPath('data.posts.0.user.name', $postAuthor->name);

        $response->assertJsonPath('data.users.0.id', $favoritedUser->id);
        $response->assertJsonPath('data.users.0.name', $favoritedUser->name);

        $response->assertJsonMissing(['email' => $postAuthor->email]);
        $response->assertJsonMissing(['email' => $favoritedUser->email]);
    }
}
