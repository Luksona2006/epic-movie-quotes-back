<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function getAllGenres(): JsonResponse
    {
        $genres = Genre::all();
        return response()->json(['genres' => $genres]);
    }
}
