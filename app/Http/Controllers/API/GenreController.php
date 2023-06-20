<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function getAllGenres(): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $genres = Genre::all();
            return response()->json(['genres' => $genres]);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_genres')])], 401);
    }
}
