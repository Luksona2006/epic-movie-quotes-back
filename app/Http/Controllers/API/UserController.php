<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $user = User::where('id', $id);
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
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::where('id', $id);
        if ($user) {
            $user->name = $request->name;

            $user->save();
            return response()->json(['message' => 'User details updated'], 200);
        }

        return response()->json(['message' => 'Something went wrong'], 400);
    }

}
