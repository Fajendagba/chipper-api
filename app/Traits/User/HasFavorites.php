<?php

namespace App\Traits\User;

use App\Models\Post;
use App\Models\User;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFavorites
{
    /**
     * Define the favorites polymorphic relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Get a list of the user's favorites
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function all_favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get a list of users who have favorited a user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function followers(): Builder
    {
        return User::whereHas('all_favorites', function ($query) {
            $query->where('favoritable_id', $this->id)
                ->where('favoritable_type', Favorite::TYPE_USER);
        });
    }

    /**
     * Get a list of all the favorite post for a given user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function favorite_posts(): Builder
    {
        return Post::whereHas('favorites', function ($query) {
            $query->where('user_id', $this->id)
                ->where('favoritable_type', Favorite::TYPE_POST);
        });
    }

    /**
     * Get the list of all the favorite users for a given user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function favorite_users(): Builder
    {
        return User::whereHas('favorites', function ($query) {
            $query->where('user_id', $this->id)
                ->where('favoritable_type', Favorite::TYPE_USER);
        });
    }

    /**
     * Mark a post as user favorite
     *
     * @param App\Models\Post
     */
    public function addPostAsFavorite(Post $post): void
    {
        $favoriteCount = $post->favorites()
                    ->where('user_id', $this->id)
                    ->count();

        if ((bool)$favoriteCount) {
            abort(409, "Already added to favorites.");
        }

        $post->favorites()->create();
    }


    /**
     * Mark a user as user favorite
     *
     * @param App\Models\User
     */
    public function addUserAsFavorite(User $user): void
    {
        $favoriteCount = $user->favorites()
                    ->where('user_id', $this->id)
                    ->count();

        if ((bool)$favoriteCount) {
            abort(409, "Already following user.");
        }

        $user->favorites()->create();
    }
}
