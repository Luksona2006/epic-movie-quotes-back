<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function socialiteRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function socialiteCreateUser(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
        ]
        );

        Auth::login($user);

        return response()->json(['user' => $user]);
    }

}
