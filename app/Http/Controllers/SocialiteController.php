<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function socialiteRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function socialiteCreateUser()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
        ]
        );

        Auth::login($user);

        $token = $user->createToken('user_token')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 200);
    }

}
