<?php

use App\Http\Controllers\API\AuthController;
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
    Route::get('verify/{token}', [AuthController::class, 'verifyEmail'])->name('verify.verifyEmail');
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetRequest'])->name('forgot-password.send-password-reset-request');
    Route::post('reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset-password.resetPassword');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('users/{token}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{token}', [UserController::class, 'update'])->name('users.update'); // Not using it yet
});

Route::get('/auth/google/redirect', [SocialiteController::class, 'socialiteRedirect'])->name('auth-google.socialiteRedirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'socialiteCreateUser'])->name('auth-google.socialiteCreateUser');
;
