<?php

namespace App\Http\Controllers\API;

use App\Events\ChangeUserEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ChangeEmail;
use App\Models\FriendRequest;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = User::findOrFail(auth()->id());

        if($request->new_username) {
            $user->name = $request->new_username;
        }

        if($request->new_password && !password_verify($request->new_password, $user->password)) {
            $user->name = $request->new_password;
        }

        if($request->image) {
            $image = $request->image;
            $extension = explode(';', explode('/', $image)[1])[0];
            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(30) . '.' . $extension;
            if($user->image && $user->image !== 'userImages/DefaultProfile.png') {
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

        return (new UserResource($user))->response()->setStatusCode(200);
    }

    public function show($id): JsonResponse
    {
        $user = (new UserResource(User::findOrFail($id)));
        $authUser = auth()->user();
        $friends = $user->allFriends->count();
        $hasRecievedFriendRequest = $authUser->pendingFriendsFrom()->wherePivot('user_id', $id)->count() > 0;
        $hasSentFriendRequest = $authUser->pendingFriendsTo()->wherePivot('friend_id', $id)->count() > 0;
        $isFriend = count([...$user->acceptedFriendsTo()->wherePivot('user_id', $id)->get()->toArray(), ...$user->acceptedFriendsFrom()->wherePivot('friend_id', $id)->get()->toArray()]) > 0;
        return response()->json(['user' => [...$user->toArray('get'), 'friends' => $friends], 'hasRecievedFriendRequest' => $hasRecievedFriendRequest || $hasSentFriendRequest ? ($hasRecievedFriendRequest ? true : false) : null, 'isFriend' => $isFriend, ]);
    }

    public function getAuthUser(): JsonResponse
    {
        return (new UserResource(auth()->user()))->response()->setStatusCode(200);
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
                auth()->logout();

                return redirect(env('FRONTEND_URL'));
            }

            $emailModel->delete();
            return redirect(env('FRONTEND_URL').'/expired');
        };

        return redirect(env('FRONTEND_URL').'/404');
    }

}
