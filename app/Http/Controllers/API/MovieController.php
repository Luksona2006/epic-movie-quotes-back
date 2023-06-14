<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\Like;
use App\Models\Movie;
use App\Models\MovieGenre;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function create(CreateMovieRequest $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();

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

            $movie = Movie::create($movie);

            foreach ($request->genres_ids as $genre_id) {
                MovieGenre::create(['movie_id' => $movie->id, 'genre_id' => $genre_id]);
            }

            $movie['quotes'] = $movie->quotes;
            $movie['genres'] = $movie->genres;

            return response()->json(['movie' => $movie]);
        }

        return response()->json(['message' => 'You are not able to create movie'], 401);
    }

    public function update(int $movieId, UpdateMovieRequest $request): JsonResponse
    {
        $movie = Movie::where('id', $movieId)->first();
        $user = User::where('token', $request->user_token)->first()->toArray();

        if($movie && $user) {
            if($user['id'] === $movie->user_id) {
                if($request->name_en && $request->name_ka) {
                    $name = [
                        'en' => $request->name_en,
                        'ka' => $request->name_ka
                    ];

                    $movie->name = $name;
                };

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

                $quotes = $movie->quotes;
                foreach ($quotes as $quote) {
                    $quoteModel = Quote::find($quote['id'])->getFullData();
                    $quote['comments'] = count($quoteModel['comments']->toArray());

                    $quote['likes'] = $quoteModel['likes'];
                    $quote['liked'] = $quoteModel['liked'];
                };
                $movie['quotes'] = $quotes;

                $movieGenres = MovieGenre::where('movie_id', $movieId)->get()->toArray();
                $genres = [];
                foreach ($movieGenres as $movieGenre) {
                    $genre = Genre::where('id', $movieGenre['genre_id'])->first();
                    array_push($genres, $genre);
                };
                $movie['genres'] = $genres;
            }

            return response()->json(['movie' => $movie]);
        }

        return response()->json(['message' => 'Wrong id, no movie found'], 404);
    }

    public function remove(int $id, Request $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();

        if($user) {
            $movie = Movie::where('id', $id)->where('user_id', $user->id)->first();
            if($movie) {
                $movie->delete();
                return response()->json(['message' => 'Movie deleted successfully']);
            }

            return response()->json(['message' => 'Wrong id, no movie found', 404]);
        }

        return response()->json(['message' => 'You are not able to remove movie', 404]);
    }

    public function getAllMovies(string $userToken): JsonResponse
    {
        $user = User::where('token', $userToken)->first();

        if($user) {
            $movies = Movie::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
            $moviesFullData = [];
            foreach ($movies as $movie) {

                array_push($moviesFullData, [...$movie->toArray(), 'genres' => $movie->genres, 'quotes' => $movie->quotes]);
            }

            return response()->json(['movies' => $moviesFullData]);
        };

        return response()->json(['message' => 'You are not able to get movies'], 401);
    }

    public function getMovie(string $userToken, int $movieId): JsonResponse
    {
        $user = User::where('token', $userToken)->first();
        if($user) {
            $movie = Movie::where('user_id', $user->id)->where('id', $movieId)->first();

            if($movie) {
                $quotes = $movie->quotes;
                foreach ($quotes as $quote) {
                    $quoteModel = Quote::find($quote['id'])->getFullData();
                    $quote['comments'] = count($quoteModel['comments']->toArray());

                    $quote['likes'] = $quoteModel['likes'];
                    $quote['liked'] = $quoteModel['liked'];
                };
                $movie['quotes'] = $quotes;

                $movieGenres = MovieGenre::where('movie_id', $movieId)->get()->toArray();
                $genres = [];
                foreach ($movieGenres as $movieGenre) {
                    $genre = Genre::where('id', $movieGenre['genre_id'])->first();
                    array_push($genres, $genre);
                };
                $movie['genres'] = $genres;

                return response()->json(['movie' => $movie]);
            }

            return response()->json(['message' => 'Movie not found'], 404);
        };

        return response()->json(['message' => 'You are not able to get movie'], 401);
    }

    public function filterMovies(Request $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();
        if($user) {
            $search = $request->searchBy;
            if($search) {
                $search = ltrim($search, '#');
                $movies = Movie::where('user_id', $user->id)->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->orderBy('created_at', 'desc')
                ->get()->toArray();

                $updatedMovies = [];
                foreach ($movies as $movie) {
                    $movieModel = Movie::find($movie['id']);
                    array_push($updatedMovies, $movieModel->getFullData());
                };

                return response()->json(['movies' => $updatedMovies]);
            }

            return response()->json(['message' => 'Enter movie name to search movie', 'movies' => []], 204);
        }

        return response()->json(['message' => 'You are not able to search for movies'], 401);
    }
}
