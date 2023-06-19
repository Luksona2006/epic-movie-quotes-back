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
    Route::post('quotes/page', [QuoteController::class, 'paginateQuotes'])->name('quotes.paginate-quotes');
    Route::get('quotes/{id}/comments', [QuoteController::class, 'getAllComments'])->name('quotes.get-all-comments');
    Route::get('quotes/{id}', [QuoteController::class, 'getQuote'])->name('quotes.get-quote');

    Route::post('movie/create', [MovieController::class, 'create'])->name('movie.create');
    Route::put('movie/update/{id}', [MovieController::class, 'update'])->name('movie.update');
    Route::post('movie/remove/{id}', [MovieController::class, 'remove'])->name('movie.remove');
    Route::post('my-movies/search', [MovieController::class, 'filterMyMovies'])->name('my-movies.filter-my-movies');
    Route::post('movies/search', [MovieController::class, 'filterMovies'])->name('movies.filter-movies');
    Route::get('movies', [MovieController::class, 'getAllMovies'])->name('movies.get-all-movies');
    Route::post('movies/page', [MovieController::class, 'paginateMovies'])->name('movies.paginate-movies');
    Route::get('movies/{id}', [MovieController::class, 'getMovie'])->name('movies.get-movie');

    Route::get('genres', [GenreController::class, 'getAllGenres'])->name('genres.get-all-genres');

    Route::get('notifications', [NotificationsController::class, 'getAllNotifications'])->name('notifications.get-all-notifications');
    Route::post('notification/update/{id}', [NotificationsController::class, 'update'])->name('notification.update');
    Route::post('notifications/update', [NotificationsController::class, 'updateAll'])->name('notifications.update-all');
});

Route::get('auth/google/redirect', [SocialiteController::class, 'socialiteRedirect'])->name('auth-google.socialite-redirect');
Route::get('auth/google/callback', [SocialiteController::class, 'socialiteCreateUser'])->name('auth-google.socialite-create-user');
