<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordResetEmailRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\ChangePassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $request->email)->firstOrFailOrFail();
        if(!$user->email_verified_at) {
            return response()->json(['message' => __('messages.account_is_not_verified_yet')]);
        }

        $remember = $request->remember ? true : false;
        if(Auth::guard()->attempt($credentials, $remember)) {
            return response()->json(['user' => $user]);
        };
        return response()->json(['message' => __('messages.invalid_credentials')], 401);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('web')->logout();
        session()->regenerate();
        return response()->json(['message' => __('messages.user_logged_out')]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $attributes['password'] = bcrypt($attributes['password']);
        $emailVerificationToken = Str::random(100);
        $attributes['email_verification_token'] = $emailVerificationToken;

        $user = User::create($attributes);

        if($user) {
            $data['token'] = $emailVerificationToken;
            $data['email'] = $user->email;
            $data['name'] = $user->name;

            Mail::send('email.verification', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject('Please verify your email address');
            });
            return response()->json(['user' => $user]);
        }

        return response()->json(['message' => __('messages.invalid_credentials')], 401);
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $user = User::where('email_verification_token', $token)->firstOrFail();
        if (!$user->email_verified_at) {
            $user->update([
                'email_verified_at' => Carbon::now(),
                'email_verification_token' => null
            ]);

            return redirect(env('FRONTEND_URL').`/verified/$token`);
        };

        return redirect(env('FRONTEND_URL')."/404");
    }

    public function sendPasswordResetRequest(PasswordResetEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();
        if ($user) {
            $token = Str::random(40);
            $data['token'] = $token;
            $data['email'] = $request->email;

            Mail::send('email.password-reset', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject('Reset Password');
            });

            if(ChangePassword::where('email', $request->email)->firstOrFail()) {
                ChangePassword::where('email', $request->email)->firstOrFail()->delete();
            }

            ChangePassword::create(['email' => $request->email, 'token' => $token]);

            return response()->json(['message' => __('messages.email_confirmation_sent_for_reset_password')]);
        }

        return response()->json(['message' => __('messages.invalid_credentials')], 401);
    }


    public function redirectToPasswordReset(Request $request): RedirectResponse
    {
        $changePasswordModel = ChangePassword::where('token', $request->token)->firstOrFail();
        if($changePasswordModel) {
            if($changePasswordModel->expires_at > Carbon::now()) {
                return redirect()->away(env('FRONTEND_URL').`/reset-password/$request->token`);
            }
            $changePasswordModel->delete();
            return redirect()->away(env('FRONTEND_URL').'/expired');
        }

        return redirect()->away(env('FRONTEND_URL').'/404');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $changePasswordModel = ChangePassword::where('token', $request->token)->firstOrFail();
        if($changePasswordModel) {
            if($changePasswordModel->expires_at > Carbon::now()) {
                $user = User::where('email', $changePasswordModel->email)->firstOrFail();
                $user->update(['password' => bcrypt($request->password)]);
                $changePasswordModel->delete();
                return response()->json(['user' => $user]);
            }
            $changePasswordModel->delete();
            return redirect()->away(env('FRONTEND_URL').'/expired');
        }

        return redirect()->away(env('FRONTEND_URL').'/404');
    }
}
