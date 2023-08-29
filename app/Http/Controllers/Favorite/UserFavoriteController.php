<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Favorite\CreateUserFavoriteRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Response;

/**
 * @group User Favorites
 *
 * API endpoints for managing user's favorite users
 */
class UserFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $users = $request->user()->favorite_users()->get();
        return UserResource::collection($users);
    }

    public function store(CreateUserFavoriteRequest $request, User $user)
    {
        $request->user()->addUserAsFavorite($user);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, User $user)
    {
        $favorite = $user->favorites()->where('favoritable_id', $user->id)->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }
}
