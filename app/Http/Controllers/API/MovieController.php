<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Movie\CreateMovieRequest;
use App\Http\Requests\Movie\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\GenreMovie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function create(CreateMovieRequest $request): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $movie['year'] = $request->year;
            $movie['user_id'] = $user->id;

            $movie['name'] = [
                'en' => $request->name_en,
                'ka' => $request->name_ka
            ];

            $movie['description'] = [
                'en' => $request->description_en,
                'ka' => $request->description_ka
            ];

            $movie['director'] = [
                'en' => $request->director_en,
                'ka' => $request->director_ka
            ];

            $image = $request->image;
            $extension = explode(';', explode('/', $image)[1])[0];
            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(30) . '.' . $extension;


            Storage::put('movieImages/' . $imageName, base64_decode($image));

            $movie['image'] = 'movieImages/' .  $imageName;

            $movie = Movie::create($movie);

            foreach ($request->genres_ids as $genre_id) {
                GenreMovie::create(['movie_id' => $movie->id, 'genre_id' => $genre_id]);
            }

            $movie = Movie::with('quotes', 'genres')->find($movie->id);

            return (new MovieResource($movie))->response()->setStatusCode(200);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.create_movie')])], 401);
    }

    public function update(Movie $movie, UpdateMovieRequest $request): JsonResponse
    {
        if(auth()->id() === $movie->user_id) {
            if($request->name_en && $request->name_ka) {
                $name = [
                    'en' => $request->name_en,
                    'ka' => $request->name_ka
                ];

                $movie->name = $name;
            };

            if($request->director_en && $request->director_ka) {
                $director = [
                    'en' => $request->director_en,
                    'ka' => $request->director_ka
                ];

                $movie->director = $director;
            };

            if($request->description_en && $request->description_ka) {
                $description = [
                    'en' => $request->description_en,
                    'ka' => $request->description_ka
                ];

                $movie->description = $description;
            };

            if($request->genres_ids) {
                foreach ($request->genres_ids as $genreId) {
                    $isSameGenre = false;
                    foreach ($movie->genres->toArray() as $genreMovie) {
                        $genreMovie['id'] === $genreId ? $isSameGenre = true : 0;
                    }
                    if(!$isSameGenre) {
                        GenreMovie::create(['genre_id' => $genreId, 'movie_id' => $movie->id]);
                    }
                }

                foreach ($movie->genres->toArray() as $genreMovie) {
                    $isRemoved = true;
                    foreach ($request->genres_ids as $genreId) {
                        $genreMovie['id'] === $genreId ? $isRemoved = false : 0;
                    }

                    if($isRemoved) {
                        GenreMovie::where('genre_id', $genreMovie['id'])->first()->delete();
                    }
                }
            }

            if($request->year) {
                $movie->year = $request->year;
            }

            if($request->image) {
                $image = $request->image;
                $extension = explode(';', explode('/', $image)[1])[0];
                $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(30) . '.' . $extension;


                Storage::delete($movie->image);
                Storage::put('movieImages/' . $imageName, base64_decode($image));

                $movie->image = 'movieImages/' .  $imageName;
            }

            $movie->save();
            $movie = Movie::with('quotes', 'genres')->find($movie->id);

            return (new MovieResource($movie))->response()->setStatusCode(200);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.update_movie')])], 404);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        if(auth()->id() === $movie->user_id) {
            $movie->delete();
            return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.movie')])]);
        }
        return response()->json(['message' => __('messages.you_are_not_able_to', ['deleted' => __('messages.remove_movie')])]);
    }

    public function paginateMovies(Request $request): JsonResponse
    {
        $moviesPaginate = Movie::where('user_id', auth()->id())->paginate(6, ['*'], 'movies-per-page', $request->pageNum);

        $movies = MovieResource::collection($moviesPaginate->items());

        $totalMovies = count($movies);
        return response()->json(['movies' => $movies, 'isLastPage' => $moviesPaginate->toArray()['last_page'] === $request->pageNum, 'total' => $totalMovies]);
    }


    public function index(): JsonResponse
    {
        $movies = Movie::where('user_id', auth()->id())->latest()->get();
        return response()->json(['movies' => MovieResource::collection($movies)]);
    }

    public function show(int $id): JsonResponse
    {
        if(auth()->id() === Movie::findOrFail($id)->user_id) {
            $movie = Movie::with('quotes', 'genres')->find($id);

            return (new MovieResource($movie))->response()->setStatusCode(200);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_movie')])], 404);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->searchBy;
        if($search) {
            $searchedMovies = Movie::where('user_id', auth()->id())
            ->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ? and user_id = '.auth()->id(), '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ? and user_id = '.auth()->id(), '%'.strtolower($search).'%');

            $moviesPaginate = $searchedMovies->paginate(10, ['*'], 'movies-per-page', $request->pageNum);

            $movies = MovieResource::collection($moviesPaginate->items());

            $totalMovies = $searchedMovies->count();
            return response()->json(['movies' => $movies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum, 'total' => $totalMovies]);
        }

        return response()->json(['message' => __('messages.enter_movie_name_to_search_movie'), 'movies' => []], 204);
    }
}
