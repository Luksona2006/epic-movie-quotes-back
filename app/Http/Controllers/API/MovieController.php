<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Movie\CreateMovieRequest;
use App\Http\Requests\Movie\UpdateMovieRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\MovieResource;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\UserResource;
use App\Models\Movie;
use App\Models\MovieGenre;
use App\Models\Quote;
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
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(30) . '.' . $extension;


            Storage::put('movieImages/' . $imageName, base64_decode($image));

            $movie['image'] = 'movieImages/' .  $imageName;

            $newMovie = Movie::create($movie);

            foreach ($request->genres_ids as $genre_id) {
                MovieGenre::create(['movie_id' => $newMovie->id, 'genre_id' => $genre_id]);
            }

            $movie = (new MovieResource($newMovie))->toArray('get');
            $movie['quotes'] = MovieResource::collection($newMovie->quotes)->toArray('get');
            $movie['genres'] = GenreResource::collection($newMovie->quotes)->toArray('get');

            return response()->json(['movie' => $movie]);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.create_movie')])], 401);
    }

    public function update(int $id, UpdateMovieRequest $request): JsonResponse
    {
        $movie = Movie::findOrFail($id);
        $user = auth()->user();

        if($user->id === $movie->user_id) {
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
                    foreach ($movie->genres->toArray() as $movieGenre) {
                        $movieGenre['id'] === $genreId ? $isSameGenre = true : 0;
                    }
                    if(!$isSameGenre) {
                        MovieGenre::create(['genre_id' => $genreId, 'movie_id' => $movie->id]);
                    }
                }

                foreach ($movie->genres->toArray() as $movieGenre) {
                    $isRemoved = true;
                    foreach ($request->genres_ids as $genreId) {
                        $movieGenre['id'] === $genreId ? $isRemoved = false : 0;
                    }

                    if($isRemoved) {
                        MovieGenre::where('genre_id', $movieGenre['id'])->first()->delete();
                    }
                }
            }

            if($request->year) {
                $movie->year = $request->year;
            }

            if($request->image) {
                $image = $request->image;
                $extension = explode(';', explode('/', $image)[1])[0];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(30) . '.' . $extension;


                Storage::delete($movie->image);
                Storage::put('movieImages/' . $imageName, base64_decode($image));

                $movie->image = 'movieImages/' .  $imageName;
            }

            $movie->save();

            $quotesFullData = array_map(function ($quote) {
                $quoteModel = Quote::find($quote['id']);

                $quote['movie'] = (new MovieResource($quoteModel->movie))->toArray('get');
                $quote['user'] = (new UserResource($quoteModel->user))->toArray('get');
                $quote['comments'] = CommentResource::collection($quoteModel->comments)->toArray('get');

                return $quote;
            }, QuoteResource::collection($movie->quotes)->toArray('get'));

            $movieResource = (new MovieResource($movie))->toArray('get');
            $movieResource['quotes'] = $quotesFullData;
            $movieResource['genres'] = GenreResource::collection($movie->genres)->toArray('get');

            return response()->json(['movie' => $movieResource]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);
    }

    public function destroy(int $id): JsonResponse
    {
        $movie = Movie::findOrFail($id);

        $movie->delete();
        return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.movie')])]);
    }

    public function paginateMovies(Request $request): JsonResponse
    {
        $user = auth()->user();

        $moviesPaginate = Movie::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(6, ['*'], 'movies-per-page', $request->pageNum);

        $movies = MovieResource::collection(collect($moviesPaginate->items()))->toArray('get');

        $moviesFullData = array_map(function ($movie) {
            $movieModel = Movie::find($movie['id']);

            $movie['quotes'] = $movieModel->quotes->count();

            return $movie;
        }, $movies);

        $totalMovies = count($user->movies->toArray());
        return response()->json(['movies' => $moviesFullData, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum, 'total' => $totalMovies]);
    }


    public function getMovies(): JsonResponse
    {
        $user = auth()->user();

        $movies = Movie::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
        $movies = MovieResource::collection($movies)->toArray('get');

        return response()->json(['movies' => $movies]);
    }

    public function getMovie(int $id): JsonResponse
    {
        $movie = Movie::findOrFail($id);

        $movieResource = (new MovieResource($movie))->toArray('get');
        $movieResource['quotes'] = QuoteResource::collection($movie->quotes)->toArray('get');

        return response()->json(['movie' => $movieResource]);
    }

    public function search(Request $request): JsonResponse
    {
        $user = auth()->user();
        $search = $request->searchBy;
        if($search) {
            $search = ltrim($search, '@');
            $searchedMovies = Movie::where('user_id', $user->id)
            ->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->orderBy('created_at', 'desc');

            $moviesPaginate = $searchedMovies->paginate(10, ['*'], 'movies-per-page', $request->pageNum);

            $movies = MovieResource::collection(collect($moviesPaginate->items()))->toArray('get');

            $moviesFullData = array_map(function ($movie) {
                $movieModel = Movie::find($movie['id']);

                $movie['quotes'] = $movieModel->quotes->count();

                return $movie;
            }, $movies);

            $totalMovies = $searchedMovies->count();
            return response()->json(['movies' => $moviesFullData, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum, 'total' => $totalMovies]);
        }

        return response()->json(['message' => __('messages.enter_movie_name_to_search_movie'), 'movies' => []], 204);
    }
}
