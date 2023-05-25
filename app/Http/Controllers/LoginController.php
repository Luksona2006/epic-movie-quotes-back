<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if(auth()->attempt($credentials)) {
            return response()->json(['user' => $credentials], 200);
        };
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}
