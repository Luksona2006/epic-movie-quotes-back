<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
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

            $movie = Movie::create($movie);

            foreach ($request->genres_ids as $genre_id) {
                MovieGenre::create(['movie_id' => $movie->id, 'genre_id' => $genre_id]);
            }

            $movie['quotes'] = $movie->quotes;
            $movie['genres'] = $movie->genres;

            return response()->json(['movie' => $movie]);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.create_movie')])], 401);
    }

    public function update(Movie $movie, UpdateMovieRequest $request): JsonResponse
    {
        $user = auth()->user();

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
                $movie['genres'] = $movie->genres;
            }

            return response()->json(['movie' => $movie]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);
    }

    public function remove(Movie $movie): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            if($movie) {
                $movie->delete();
                return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.movie')])]);
            }

            return response()->json(['message' => __('messages.wrong_id'), 404]);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_movies')]), 404]);
    }

    public function paginateMovies(Request $request): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $moviesPaginate = Movie::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(6, ['*'], 'movies-per-page', $request->pageNum)->toArray();
            $movies = $moviesPaginate['data'];

            $moviesFullData = [];
            foreach ($movies as $movie) {
                $movieModel = Movie::find($movie['id']);
                array_push($moviesFullData, [...$movie, 'genres' => $movieModel->genres, 'quotes' => $movieModel->quotes]);
            }

            $totalMovies = count($user->movies->toArray());
            return response()->json(['movies' => $moviesFullData, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum, 'total' => $totalMovies]);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_movies')])], 401);
    }


    public function getMovies(): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $movies = Movie::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
            $moviesFullData = [];
            foreach ($movies as $movie) {
                array_push($moviesFullData, [...$movie->toArray(), 'genres' => $movie->genres, 'quotes' => $movie->quotes]);
            }

            return response()->json(['movies' => $moviesFullData]);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_movies')])], 401);
    }

    public function getMovie(Movie $movie): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            if($movie) {
                $quotes = $movie->quotes;
                foreach ($quotes as $quote) {
                    $quoteModel = Quote::find($quote['id'])->getFullData();
                    $quote['comments'] = count($quoteModel['comments']->toArray());

                    $quote['likes'] = $quoteModel['likes'];
                    $quote['liked'] = $quoteModel['liked'];
                };
                $movie['quotes'] = $quotes;
                $movie['genres'] = $movie->genres;

                return response()->json(['movie' => $movie]);
            }

            return response()->json(['message' => __('messages.not_found', ['notFound' => __('messages.movie')])], 404);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_movie')])], 401);
    }

    public function filterMyMovies(Request $request): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $search = $request->searchBy;
            if($search) {
                $search = ltrim($search, '@');
                $searchedMovies = Movie::where('user_id', $user->id)
                ->whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->orderBy('created_at', 'desc');

                $moviesPaginate = $searchedMovies->paginate(10, ['*'], 'movies-per-page', $request->pageNum)->toArray();
                $movies = $moviesPaginate['data'];

                $updatedMovies = [];
                foreach ($movies as $movie) {
                    $movieModel = Movie::find($movie['id']);
                    array_push($updatedMovies, $movieModel->getFullData());
                };

                $totalMovies = count($searchedMovies->get()->toArray());
                return response()->json(['movies' => $updatedMovies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum, 'total' => $totalMovies]);
            }

            return response()->json(['message' => __('messages.enter_movie_name_to_search_movie'), 'movies' => []], 204);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.search_for_movies')])], 401);
    }

    public function filterMovies(Request $request): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $search = $request->searchBy;

            if($search) {
                $search = ltrim($search, '@');
                $moviesPaginate = Movie::whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'movies-per-page', $request->pageNum)->toArray();

                $movies = $moviesPaginate['data'];

                $updatedMovies = [];
                foreach ($movies as $movie) {
                    $movieModel = Movie::find($movie['id']);
                    array_push($updatedMovies, $movieModel->getFullData());
                };

                return response()->json(['movies' => $updatedMovies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum]);
            }

            return response()->json(['message' => __('messages.enter_movie_name_to_search_movie'), 'movies' => []], 204);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.search_for_movies')])], 401);
    }
}
