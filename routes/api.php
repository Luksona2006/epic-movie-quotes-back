<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GenreController;
use App\Http\Controllers\API\MovieController;
use App\Http\Controllers\API\NotificationsController;
use App\Http\Controllers\API\QuoteController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('locale', [LocalizationController::class, 'setLocale']);

Route::group(['middleware' => 'guest:sanctum'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('signup', [AuthController::class, 'register'])->name('signup.register');
    Route::get('verify/{token}', [AuthController::class, 'verifyEmail'])->name('verify.verify-email');
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetRequest'])->name('forgot-password.send-password-reset-request');
    Route::post('reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset-password');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('user/details', [UserController::class, 'show'])->name('users.show');
    Route::put('user/details', [UserController::class, 'update'])->name('users.update');

    Route::post('quote/create', [QuoteController::class, 'create'])->name('quote.create');
    Route::put('quote/update/{id}', [QuoteController::class, 'update'])->name('quote.update');
    Route::post('quote/remove/{id}', [QuoteController::class, 'remove'])->name('quote.remove');
    Route::post('quotes/search', [QuoteController::class, 'filterQuotes'])->name('quotes.filter-quotes');
    Route::get('user/quotes/page/{num}', [QuoteController::class, 'paginateQuotes'])->name('user.paginate-quotes');
    Route::get('user/quotes/{id}/comments', [QuoteController::class, 'getAllComments'])->name('user.get-all-comments');
    Route::get('user/quotes/{id}', [QuoteController::class, 'getQuote'])->name('user.get-quote');

    Route::post('movie/create', [MovieController::class, 'create'])->name('movie.create');
    Route::put('movie/update/{id}', [MovieController::class, 'update'])->name('movie.update');
    Route::post('movie/remove/{id}', [MovieController::class, 'remove'])->name('movie.remove');
    Route::post('my-movies/search', [MovieController::class, 'filterMyMovies'])->name('my-movies.filter-my-movies');
    Route::post('movies/search', [MovieController::class, 'filterMovies'])->name('movies.filter-movies');
    Route::get('user/movies', [MovieController::class, 'getAllMovies'])->name('user.get-all-movies');
    Route::get('user/movies/page/{num}', [MovieController::class, 'paginateMovies'])->name('user.paginate-movies');
    Route::get('user/movies/{id}', [MovieController::class, 'getMovie'])->name('user.get-movie');

    Route::get('user/genres', [GenreController::class, 'getAllGenres'])->name('user.get-all-genres');

    Route::get('user/notifications', [NotificationsController::class, 'getAllNotifications'])->name('user.get-all-notifications');
    Route::post('notification/update/{id}', [NotificationsController::class, 'update'])->name('notification.update');
    Route::post('user/notifications/update', [NotificationsController::class, 'updateAll'])->name('user.update-all');
});

Route::get('auth/google/redirect', [SocialiteController::class, 'socialiteRedirect'])->name('auth-google.socialite-redirect');
Route::get('auth/google/callback', [SocialiteController::class, 'socialiteCreateUser'])->name('auth-google.socialite-create-user');
