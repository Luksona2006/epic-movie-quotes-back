<?php

use App\Http\Controllers\API\Quote\QuoteController;
use App\Http\Controllers\API\Quote\CommentController;
use App\Http\Controllers\API\Quote\LikeController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GenreController;
use App\Http\Controllers\API\MovieController;
use App\Http\Controllers\API\NotificationController;
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
    Route::group(['controller' => AuthController::class], function () {
        Route::post('login', 'login')->name('login');
        Route::post('signup', 'register')->name('signup.register');
        Route::get('verify/{token}', 'verifyEmail')->name('verify.verify_email');
        Route::post('forgot-password', 'sendPasswordResetRequest')->name('forgot_password.send_password_reset_request');
        Route::get('reset-password/redirect/{token}', 'redirectToPasswordReset')->name('reset.redirect_to_password_reset');
        Route::post('reset-password/{token}', 'resetPassword')->name('reset_password');

        Route::get('change-email/{token}', [UserController::class, 'confirmEmailChange'])->name('change_email.confirm_email_change');
    });

    Route::get('change-email/{token}', [UserController::class, 'confirmEmailChange'])->name('change_email.confirm_email_change');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('genres', [GenreController::class, 'index'])->name('genres.index');

    Route::group(['controller' => UserController::class], function () {
        Route::put('user', 'update')->name('users.update');
    });

    Route::group(['controller' => QuoteController::class], function () {
        Route::post('quotes/all', 'index')->name('quotes.index');
        Route::post('quotes', 'create')->name('quotes.create');
        Route::get('quotes/{id}', 'getQuote')->name('quotes.get_quote');
        Route::put('quotes/{id}', 'update')->name('quotes.update');
        Route::delete('quotes/{id}', 'destroy')->name('quotes.destroy');

        Route::post('quotes/search', 'search')->name('quotes.search');
    });

    Route::post('quotes/{id}/like', [LikeController::class, 'like'])->name('quote.like');
    Route::post('quotes/{id}/comment', [CommentController::class, 'comment'])->name('quote.comment');

    Route::group(['controller' => MovieController::class], function () {
        Route::get('movies/all', 'index')->name('movies.index');
        Route::post('movies/page', 'paginateMovies')->name('movies.paginate_movies');
        Route::post('movies', 'create')->name('movies.create');
        Route::get('movies/{id}', 'getMovie')->name('movies.get_movie');
        Route::put('movies/{id}', 'update')->name('movies.update');
        Route::delete('movies/{id}', 'destroy')->name('movies.destroy');

        Route::post('movies/search', 'search')->name('movies.search');
    });

    Route::group(['controller' => NotificationController::class], function () {
        Route::get('notifications', 'getAllNotifications')->name('notifications.get_all_notifications');
        Route::post('notification/update/{id}', 'update')->name('notification.update');
        Route::post('notifications/update', 'updateAll')->name('notifications.update_all');
    });
});

Route::group(['middleware' => 'web', 'controller' => SocialiteController::class], function () {
    Route::get('auth/google/redirect', 'socialiteRedirect')->name('auth_google.socialite_redirect');
    Route::get('auth/google/callback', 'socialiteCreateUser')->name('auth_google.socialite_create_user');
});
