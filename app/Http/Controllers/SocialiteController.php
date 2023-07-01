<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
        $existingUser = User::where('email', $googleUser->email)->first();
        $existingUserGoogleId = null;
        if($existingUser) {
            $existingUserGoogleId = $existingUser->google_id;
        }
        if(($existingUser && $existingUserGoogleId !== null) || !$existingUser) {
            if(!Storage::get('userImages/DefaultProfile.png')) {
                $image = public_path('assets/images/DefaultProfile.png');
                Storage::put('userImages/DefaultProfile.png', file_get_contents($image));
            }

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

            $imageName = Str::random(30) . '.jpg';
            Storage::put('userImages/' . $imageName, file_get_contents($url));

            $user->image = 'userImages/'.$imageName;

            $user->save();

            Auth::guard()->login($user, true);

            return redirect(env('FRONTEND_URL')."/auth/user");
        }

        return redirect(env('FRONTEND_URL'));
    }

}
