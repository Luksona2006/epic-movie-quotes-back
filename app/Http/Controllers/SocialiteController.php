<?php

namespace App\Http\Controllers;

use App\Events\LoginGoogleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function socialiteRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function socialiteCreateUser(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->email],
            [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id
            ]
        );

        if(!$user->email_verified_at) {
            $user->email_verified_at = Carbon::now();
        };

        if($user->image) {
            Storage::delete($user->image);
        }

        $url = $googleUser->avatar;
        $imageBase64 = 'data:image/jpg;base64,'.base64_encode(file_get_contents($url));

        $imageName = Str::random(30) . '.jpg';
        Storage::put('userImages/' . $imageName, base64_decode($imageBase64));

        $user->image = 'userImages/'.$imageName;

        $user->save();


        Auth::guard()->login($user);
        session()->regenerate();

        return redirect(env('FRONTEND_URL').'/news-feed');
    }

}
