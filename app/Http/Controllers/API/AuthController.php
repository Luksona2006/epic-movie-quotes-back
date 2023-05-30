<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\PasswordResetEmailRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $request->email)->first();
        if(!$user->email_verified_at) {
            return response()->json(['message' => 'Account is not verified yet']);
        }

        if(Auth::attempt($credentials, $request->remember)) {
            $token = $user->createToken('user_token')->plainTextToken;
            return response()->json(['user' => $user, 'token' => $token], 200);
        };
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->logout;
        $user->tokens()->delete();
        return response()->json(['message' => 'User logged out'], 200);
    }

    public function register(RegisterRequest $request)
    {
        $attributes = $request->validated();
        $attributes['password'] = bcrypt($attributes['password']);
        $token = Str::random(100);
        $attributes['email_verification_token'] = $token;

        $user = User::create($attributes);

        if($user) {
            $data['token'] = $token;
            $data['email'] = $user->email;
            $data['name'] = $user->name;

            Mail::send('email.verification', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject('Please verify your email address');
            });
            return response()->json(['user' => $user], 200);
        }

        return response()->json(['message' => 'Invalid details'], 401);
    }

    public function verifyEmail(string $token)
    {
        $user = User::where('email_verification_token', $token)->first();
        if (!$user->email_verified_at) {
            $user->update([
                'email_verified_at' => Carbon::now(),
                'email_verification_token' => null
            ]);

            return redirect(`http://localhost:5173/verified/{$token}`);
        };

        return redirect('http://localhost:5173/404');
    }

    public function sendPasswordResetRequest(PasswordResetEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(40);
            $data['token'] = $token;
            $data['email'] = $request->email;

            Mail::send('email.password-reset', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject('Reset Password');
            });

            User::updateOrCreate(
                ['id' => $user->id],
                ['password_reset_token' => $token]
            );

            return response()->json(['message' => 'Email has sended to confirm password reset'], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('password_reset_token', $request->token)->first();
        $user->update(['password' => bcrypt($request->password) ,'password_reset_token' => null]);
    }
}
