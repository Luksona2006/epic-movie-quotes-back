<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function getAllMovies(Request $request): JsonResponse
    {
        $user = User::where('token', $request->token)->first();

        if($user) {
            $movies = Movie::all()->toArray();
            return response()->json($movies, 200);
        };

        return response()->json(['message' => 'You are not able to get all movies'], 401);
    }
}
