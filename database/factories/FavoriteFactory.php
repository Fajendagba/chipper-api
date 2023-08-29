<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Post;
use App\Models\Favorite;
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
     * Define the model's default state for "favoriting" posts.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'favoritable_id' => Post::factory(),
            'favoritable_type' => function (array $attributes) {
                return Post::find($attributes['favoritable_id'])->getMorphClass();
            },
            'user_id' => User::factory()
        ];
    }

    /**
     * Define the model's state for "favoriting" users.
     *
     * @return Factory
     */
    public function forUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'favoritable_id' => User::factory(),
                'favoritable_type' => function (array $attributes) {
                    return User::find($attributes['favoritable_id'])->getMorphClass();
                },
                'user_id' => User::factory()
            ];
        });
    }
}
