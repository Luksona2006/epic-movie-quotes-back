<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function getAllGenres(string $userToken): JsonResponse
    {
        $user = User::where('token', $userToken)->first();
        if($user) {
            $genres = Genre::all();
            return response()->json(['genres' => $genres]);
        }

        return response()->json(['message' => 'You are not able to get genres'], 401);
    }
}
