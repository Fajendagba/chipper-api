<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Favorite\FavoriteController;
use App\Http\Controllers\Favorite\PostFavoriteController;
use App\Http\Controllers\Favorite\UserFavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', RegisterController::class)->name('register');
Route::post('login', LoginController::class)->name('login');
Route::get('posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('session', SessionController::class)->name('session');
    Route::post('logout', LogoutController::class)->name('logout');

    # POSTS
    Route::apiResource('posts', PostController::class, ['except' => ['index']]);

    # FAVORITES
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::get('followers', [FavoriteController::class, 'getFollowers'])->name('favorites.followers');

    Route::get('favorite-posts', [PostFavoriteController::class, 'index'])->name('favorites.post.index');
    Route::get('favorite-users', [UserFavoriteController::class, 'index'])->name('favorites.user.index');

    Route::post('posts/{post}/favorite', [PostFavoriteController::class, 'store'])->name('favorites.post.store');
    Route::delete('posts/{post}/favorite', [PostFavoriteController::class, 'destroy'])->name('favorites.post.destroy');
    Route::post('users/{user}/favorite', [UserFavoriteController::class, 'store'])->name('favorites.user.store');
    Route::delete('users/{user}/favorite', [UserFavoriteController::class, 'destroy'])->name('favorites.user.destroy');
});
