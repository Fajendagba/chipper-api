<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'favoritable_id' => Post::factory(),
            'favoritable_type' => Post::class,
            'user_id' => User::factory(),
        ];
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'favoritable_id' => $post->id,
            'favoritable_type' => Post::class,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'favoritable_id' => $user->id,
            'favoritable_type' => User::class,
        ]);
    }
}
