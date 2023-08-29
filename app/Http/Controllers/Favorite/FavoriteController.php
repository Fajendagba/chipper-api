<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Favorite;

/**
 * @group Favorites
 *
 * API endpoints for managing user's favorite posts
 */
class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->favorite_posts()->get();
        $users = $request->user()->favorite_users()->get();

        return response()->json(['data' =>
            [
            'posts' => PostResource::collection($posts),
            'users'   => UserResource::collection($users)
            ]
        ]);
    }

    public function getFollowers(Request $request)
    {
        $users = $request->user()->followers()->get();
        return UserResource::collection($users);
    }
}
