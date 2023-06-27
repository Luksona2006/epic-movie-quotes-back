<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function getAllGenres(): JsonResponse
    {
        $genres = GenreResource::collection(Genre::all())->toArray('get');
        return response()->json(['genres' => $genres]);
    }
}
