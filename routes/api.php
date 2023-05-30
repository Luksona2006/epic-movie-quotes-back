<?php

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

Route::post('locale', [App\Http\Controllers\LocalizationController::class, 'setLocale']);

Route::group(['middleware' => 'guest:sanctum'], function () {
    Route::post('login', [App\Http\Controllers\API\AuthController::class, 'login'])->name('login');
    Route::post('signup', [App\Http\Controllers\API\AuthController::class, 'register'])->name('signup.register');
    Route::get('verify/{token}', [App\Http\Controllers\API\AuthController::class,'verifyEmail'])->name('verify.verifyEmail');
    Route::post('forgot-password', [App\Http\Controllers\API\AuthController::class,'sendPasswordResetRequest'])->name('forgot-password.send-password-reset-request');
    Route::post('reset-password/{token}', [App\Http\Controllers\API\AuthController::class,'resetPassword'])->name('reset-password.resetPassword');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [App\Http\Controllers\API\AuthController::class, 'logout'])->name('logout');

    Route::get('users/{id}', [App\Http\Controllers\API\UserController::class, 'show'])->name('users.show');
    Route::put('users/{id}', [App\Http\Controllers\API\UserController::class, 'update'])->name('users.update');
});

Route::get('/auth/google/redirect', [App\Http\Controllers\SocialiteController::class, 'socialiteRedirect'])->name('auth-google.socialiteRedirect');
Route::get('/auth/google/callback', [App\Http\Controllers\SocialiteController::class, 'socialiteCreateUser'])->name('auth-google.socialiteCreateUser');
;
