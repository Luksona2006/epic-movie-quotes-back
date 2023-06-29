<?php

namespace App\Http\Controllers\API;

use App\Events\ChangeUserEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ChangeEmail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request): JsonResource
    {
        $user = User::find(auth()->user()->id);

        if($request->new_username) {
            $user->name = $request->new_username;
        }

        if($request->new_password) {
            $user->name = $request->new_password;
        }

        if($request->image) {
            $image = $request->image;
            $extension = explode(';', explode('/', $image)[1])[0];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(30) . '.' . $extension;
            if($user->image) {
                Storage::delete($user->image);
            }
            Storage::put('userImages/' . $imageName, base64_decode($image));

            $user->image = 'userImages/' .  $imageName;
        }

        $userWithEmail = User::where('email', $request->new_email)->count();
        if($request->new_email && !$userWithEmail) {
            $hasChangeRequest = ChangeEmail::where('from_email', $user->email)->first();
            if($hasChangeRequest) {
                $hasChangeRequest->delete();
            }

            $emailVerificationToken = Str::random(100);
            ChangeEmail::create([
                'from_email' => $user->email,
                'to_email' => $request->new_email,
                'email_verification_token' => $emailVerificationToken
            ]);
            $data['token'] = $emailVerificationToken;
            $data['email'] = $request->new_email;

            Mail::send('email.change_email', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject('Confirm your account email address change');
            });
        }

        $user->save();

        return new UserResource($user);
    }

    public function confirmEmailChange(string $token): RedirectResponse
    {
        $emailModel = ChangeEmail::where('email_verification_token', $token)->first();

        if ($emailModel) {
            if($emailModel->expires_at > Carbon::now()) {
                $user = User::where('email', $emailModel->from_email)->firstOrFail();
                $user->email = $emailModel->to_email;
                $user->save();

                event(new ChangeUserEmail($user->id, $emailModel->to_email));

                return redirect(env('FRONTEND_URL'));
            }

            $emailModel->delete();
            return redirect(env('FRONTEND_URL').'/expired');
        };

        return redirect(env('FRONTEND_URL').'/404');
    }

}
