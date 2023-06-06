<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $token): JsonResponse
    {
        $user = User::where('token', $token)->first();
        if ($user) {
            return response()->json(['user' => $user], 200);
        }

        return response()->json(['message' => 'Something went wrong'], 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, string $token)
    {
        $user = User::where('token', $token)->first();
        if ($user) {
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

            $user->save();
            return response()->json(['user' => $user], 200);
        }

        return response()->json(['message' => 'Something went wrong'], 400);
    }

}
