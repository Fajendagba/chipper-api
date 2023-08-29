<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\Favorite\CreatePostFavoriteRequest;
use App\Http\Resources\PostResource;
use Illuminate\Http\Response;

/**
 * @group Post Favorites
 *
 * API endpoints for managing user's favorite posts
 */
class PostFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->favorite_posts()->get();
        return PostResource::collection($posts);
    }

    public function store(CreatePostFavoriteRequest $request, Post $post)
    {
        $request->user()->addPostAsFavorite($post);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, Post $post)
    {
        $favorite = $post->favorites()->where('favoritable_id', $post->id)->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }
}
