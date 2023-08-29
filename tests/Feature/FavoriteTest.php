<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_user_can_list_favorites_correctly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('favorites.index'));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'posts' => ['*' => ['id', 'title', 'body', 'user']],
                    'users' => ['*' => ['id', 'name', 'email']]
                ]
            ]);
    }

    public function test_a_guest_can_not_favorite_a_post()
    {
        $post = Post::factory()->create();

        $this->postJson(route('favorites.post.store', ['post' => $post]))
            ->assertStatus(401);
    }

    public function test_a_user_can_favorite_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.post.store', ['post' => $post->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Favorite::TYPE_POST,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_remove_a_post_from_his_favorites()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.post.store', ['post' => $post->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Favorite::TYPE_POST,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('favorites.post.destroy', ['post' => $post->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $post->id,
            'favoritable_type' => Favorite::TYPE_POST,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_not_remove_a_non_favorited_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('favorites.post.destroy', ['post' => $post]))
            ->assertNotFound();
    }

    public function test_a_user_can_not_favorite_a_post_twice()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.post.store', ['post' => $post->id]))
            ->assertCreated();

        $this->actingAs($user)
            ->postJson(route('favorites.post.store', ['post' => $post->id]))
            ->assertConflict();
    }

    public function test_a_guest_can_not_favorite_a_user()
    {
        $user = User::factory()->create();

        $this->postJson(route('favorites.user.store', ['user' => $user]))
            ->assertStatus(401);
    }

    public function test_a_user_can_favorite_a_user()
    {
        $authUser = User::factory()->create();
        $mockUser = User::factory()->create();

        $this->actingAs($authUser)
            ->postJson(route('favorites.user.store', ['user' => $mockUser->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $mockUser->id,
            'favoritable_type' => Favorite::TYPE_USER,
            'user_id' => $authUser->id,
        ]);
    }

    public function test_a_user_can_remove_a_user_from_his_favorites()
    {
        $authUser = User::factory()->create();
        $mockUser = User::factory()->create();

        $this->actingAs($authUser)
            ->postJson(route('favorites.user.store', ['user' => $mockUser->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favoritable_id' => $mockUser->id,
            'favoritable_type' => Favorite::TYPE_USER,
            'user_id' => $authUser->id,
        ]);

        $this->actingAs($authUser)
            ->deleteJson(route('favorites.user.destroy', ['user' => $mockUser->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $mockUser->id,
            'favoritable_type' => Favorite::TYPE_USER,
            'user_id' => $authUser->id,
        ]);
    }

    public function test_a_user_can_not_remove_a_non_favorited_user()
    {
        $authUser = User::factory()->create();
        $mockUser = Post::factory()->create();

        $this->actingAs($authUser)
            ->deleteJson(route('favorites.user.destroy', ['user' => $mockUser]))
            ->assertNotFound();
    }

    public function test_a_user_can_not_favorite_another_user_twice()
    {
        $authUser = User::factory()->create();
        $mockUser = User::factory()->create();

        $this->actingAs($authUser)
            ->postJson(route('favorites.user.store', ['user' => $mockUser->id]))
            ->assertCreated();

        $this->actingAs($authUser)
            ->postJson(route('favorites.user.store', ['user' => $mockUser->id]))
            ->assertConflict();
    }

    public function test_a_user_can_not_favorite_himself()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $user->id]))
            ->assertForbidden();
    }

    public function test_a_user_can_list_followers_correctly()
    {
        $authUser = User::factory()->create();
        $mockUser = User::factory()->create();

        $this->actingAs($mockUser)
            ->postJson(route('favorites.user.store', ['user' => $authUser->id]))
            ->assertCreated();

        $response = $this->actingAs($authUser)->getJson(route('favorites.followers'));

        $response->assertSuccessful()
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data', 1)
                    ->has('data.0', fn ($json) => $this->assertJsonHasUser($json, $mockUser))
                    ->etc()
            );
    }

    private function assertJsonHasUser(AssertableJson $json, User $user): void
    {
        $json->where('id', $user->id)
            ->where('name', $user->name)
            ->where('email', $user->email)
            ->etc();
    }
}
