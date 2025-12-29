<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\CreateFavoriteRequest;
use Illuminate\Http\Response;

/**
 * @group Favorites
 *
 * API endpoints for managing favorites
 */
class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()
            ->with(['favoritable' => function ($morphTo) {
                $morphTo->morphWith([
                    Post::class => ['user'],
                ]);
            }])
            ->get();

        $postFavorites = $favorites->where('favoritable_type', Post::class);
        $userFavorites = $favorites->where('favoritable_type', User::class);

        return response()->json([
            'data' => [
                'posts' => $postFavorites->map(function ($favorite) {
                    $post = $favorite->favoritable;
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'body' => $post->body,
                        'user' => [
                            'id' => $post->user->id,
                            'name' => $post->user->name,
                        ],
                    ];
                })->values(),
                'users' => $userFavorites->map(function ($favorite) {
                    $user = $favorite->favoritable;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                })->values(),
            ],
        ]);
    }

    public function store(CreateFavoriteRequest $request, Post $post)
    {
        $request->user()->favorites()->create([
            'favoritable_id' => $post->id,
            'favoritable_type' => Post::class,
        ]);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, Post $post)
    {
        $favorite = $request->user()->favorites()
            ->where('favoritable_id', $post->id)
            ->where('favoritable_type', Post::class)
            ->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }

    public function storeUser(CreateFavoriteRequest $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot favorite yourself.'], Response::HTTP_FORBIDDEN);
        }

        $request->user()->favorites()->create([
            'favoritable_id' => $user->id,
            'favoritable_type' => User::class,
        ]);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroyUser(Request $request, User $user)
    {
        $favorite = $request->user()->favorites()
            ->where('favoritable_id', $user->id)
            ->where('favoritable_type', User::class)
            ->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }
}
