<?php

namespace App\Http\Controllers\API\Quote;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\CreateQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Models\Movie;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function create(CreateQuoteRequest $request): JsonResponse
    {
        $attributes['movie_id'] = $request->movie_id;
        $user = auth()->user();
        $attributes['user_id'] = $user->id;
        $attributes['text'] = [
            'en' => $request->quote_en,
            'ka' => $request->quote_ka
        ];

        $image = $request->image;
        $extension = explode(';', explode('/', $image)[1])[0];
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(30) . '.' . $extension;

        Storage::put('quoteImages/' . $imageName, base64_decode($image));

        $attributes['image'] = 'quoteImages/' .  $imageName;

        $quote = Quote::create($attributes);

        $quote['comments'] = [];
        $quote['liked'] = false;
        $quote['likes'] = 0;
        $quote['movie'] = $quote->movie;
        $quote['user'] = $quote->user;
        return response()->json(['quote' => $quote]);

    }

    public function update(int $id, UpdateQuoteRequest $request): JsonResponse
    {
        $quote = Quote::findOrFail($id);

        if($request->quote_en && $request->quote_ka) {
            $text = [
                'en' => $request->quote_en ?? $quote->toArray()['text']['en'],
                'ka' => $request->quote_ka ?? $quote->toArray()['text']['ka'],
            ];

            $quote->text = $text;
        };

        if($request->image) {
            $image = $request->image;
            $extension = explode(';', explode('/', $image)[1])[0];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(30) . '.' . $extension;


            Storage::delete($quote->image);
            Storage::put('quoteImages/' . $imageName, base64_decode($image));

            $quote->image = 'quoteImages/' .  $imageName;
        }

        $quote->save();

        return response()->json(['quote' => $quote]);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();
        return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.quote')])]);
    }

    public function getQuotes(Request $request): JsonResponse
    {
        $user = auth()->user();

        $quotesPaginate = Quote::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $request->pageNum)->toArray();
        $quotes = $quotesPaginate['data'];
        $quotesFullData = array_map(function ($quote) use ($user) {
            $quoteModel = Quote::with('movie')->find($quote['id']);

            $likes = $quoteModel->likes->toArray();
            $likesSum = count($likes);

            $liked = count(array_filter($likes, function ($like) use ($user) {
                return $like['user_id'] === $user->id;
            })) ? true : false;

            $comments = $quoteModel->comments;
            $commentsWithUsers = $comments->map(function ($comment) {
                return ['user' => $comment->user, ...$comment->toArray()];
            });

            $quoteFullData = [...$quote];
            $quoteFullData['author'] = $quoteModel->user;
            $quoteFullData['likes'] = $likesSum;
            $quoteFullData['liked'] = $liked;
            $quotesFullData['commentsTotal'] = count($quoteModel->comments->toArray());
            $quoteFullData['comments'] = $commentsWithUsers;

            return [...$quoteFullData, 'commentsTotal' => count($quoteModel->comments->toArray())];
        }, $quotes);

        return response()->json(['quotes' => $quotesFullData, 'isLastPage' => $quotesPaginate['last_page'] === $request->pageNum]);
    }

    public function getQuote(int $id): JsonResponse
    {
        $quote = Quote::with('movie')->findOrFail($id);
        $user = auth()->user();

        $likes = $quote->likes->toArray();
        $likesSum = count($likes);

        $liked = count(array_filter($likes, function ($like) use ($user) {
            return $like['user_id'] === $user->id;
        })) ? true : false;

        $comments = $quote->comments;

        $commentsWithUsers = $comments->map(function ($comment) {
            return ['user' => $comment->user, ...$comment->toArray()];
        });

        $quoteFullData = [...$quote->toArray()];
        $quoteFullData['author'] = $quote->user;
        $quoteFullData['likes'] = $likesSum;
        $quoteFullData['liked'] = $liked;
        $quoteFullData['commentsTotal'] = count($quote->comments->toArray());
        $quoteFullData['comments'] = $commentsWithUsers;
        return response()->json(['quote' => $quoteFullData]);
    }

    public function search(Request $request): JsonResponse
    {
        $user = auth()->user();

        $search = $request->searchBy;
        if($search[0] === '#') {
            $search = ltrim($search, '#');
            $quotesPaginate = Quote::whereRaw('LOWER(JSON_EXTRACT(text, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(text, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $request->pageNum)->toArray();

            $quotes = $quotesPaginate['data'];

            $updatedQuotes = [];
            foreach ($quotes as $quote) {
                $quoteModel = Quote::find($quote['id']);

                $likes = $quoteModel->likes->toArray();
                $likesSum = count($likes);

                $liked = count(array_filter($likes, function ($like) use ($user) {
                    return $like['user_id'] === $user->id;
                })) ? true : false;

                $comments = $quoteModel->comments;

                $commentsWithUsers = $comments->map(function ($comment) {
                    return ['user' => $comment->user, ...$comment->toArray()];
                });

                $quoteFullData = [...$quote];
                $quoteFullData['movie'] = $quoteModel->movie;
                $quoteFullData['author'] = $quoteModel->user;
                $quoteFullData['likes'] = $likesSum;
                $quoteFullData['liked'] = $liked;
                $quoteFullData['commentsTotal'] = count($quoteModel->comments->toArray());
                $quoteFullData['comments'] = $commentsWithUsers;

                array_push($updatedQuotes, $quoteFullData);
            };

            return response()->json(['quotes' => $updatedQuotes, 'isLastPage' => $quotesPaginate['last_page'] === $request->pageNum]);
        }

        if($search[0] === '@') {
            $search = ltrim($search, '@');
            $moviesPaginate = Movie::whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'movies-per-page', $request->pageNum)->toArray();

            $movies = $moviesPaginate['data'];

            $updatedMovies = [];
            foreach ($movies as $movie) {
                $movieModel = Movie::find($movie['id']);
                array_push($updatedMovies, [...$movieModel->toArray(), 'quotes' => count($movieModel->quotes->toArray())]);
            };

            return response()->json(['movies' => $updatedMovies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum]);
        }


        return response()->json(['quotes' => []], 204);
    }
}
