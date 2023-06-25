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
    Route::get('verify/{token}', [AuthController::class, 'verifyEmail'])->name('verify.verify_email');
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetRequest'])->name('forgot_password.send_password_reset_request');
    Route::get('reset-password/redirect/{token}', [AuthController::class, 'redirectToPasswordReset'])->name('reset.redirect_to_password_reset');
    Route::post('reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset_password');

    Route::get('change-email/{token}', [UserController::class, 'confirmEmailChange'])->name('change_email.confirm_email_change');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('user', [UserController::class, 'show'])->name('users.show');
    Route::put('user', [UserController::class, 'update'])->name('users.update');

    Route::post('quotes/all', [QuoteController::class, 'getQuotes'])->name('quotes.get_quotes');
    Route::post('quotes', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('quotes/{id}', [QuoteController::class, 'getQuote'])->name('quotes.get_quote');
    Route::put('quotes/{id}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('quotes/{id}', [QuoteController::class, 'remove'])->name('quotes.remove');

    Route::post('quotes/search', [QuoteController::class, 'filterQuotes'])->name('quotes.filter_quotes');
    Route::get('quotes/{id}/comments', [QuoteController::class, 'getAllComments'])->name('quotes.get_all_comments');

    Route::get('movies/all', [MovieController::class, 'getMovies'])->name('movies.get_movies');
    Route::post('movies/page', [MovieController::class, 'paginateMovies'])->name('movies.paginate_movies');
    Route::post('movies', [MovieController::class, 'create'])->name('movies.create');
    Route::get('movies/{id}', [MovieController::class, 'getMovie'])->name('movies.get_movie');
    Route::put('movies/{id}', [MovieController::class, 'update'])->name('movies.update');
    Route::delete('movies/{id}', [MovieController::class, 'remove'])->name('movies.remove');

    Route::post('my-movies/search', [MovieController::class, 'filterMyMovies'])->name('my_movies.filter_my_movies');
    Route::post('movies/search', [MovieController::class, 'filterMovies'])->name('movies.filter_movies');

    Route::get('genres', [GenreController::class, 'getAllGenres'])->name('genres.get_all_genres');

    Route::get('notifications', [NotificationsController::class, 'getAllNotifications'])->name('notifications.get_all_notifications');
    Route::post('notification/update/{id}', [NotificationsController::class, 'update'])->name('notification.update');
    Route::post('notifications/update', [NotificationsController::class, 'updateAll'])->name('notifications.update_all');
});

Route::group(['middleware' => 'web'], function () {
    Route::get('auth/google/redirect', [SocialiteController::class, 'socialiteRedirect'])->name('auth_google.socialite_redirect');
    Route::get('auth/google/callback', [SocialiteController::class, 'socialiteCreateUser'])->name('auth_google.socialite_create_user');
});
