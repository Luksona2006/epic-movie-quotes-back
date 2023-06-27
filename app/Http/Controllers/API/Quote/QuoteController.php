<?php

namespace App\Http\Controllers\API\Quote;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\CreateQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\MovieResource;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\UserResource;
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

        $newQuote = Quote::create($attributes);

        $quote = (new QuoteResource($newQuote))->toArray('get');
        $quote['movie'] = (new MovieResource($newQuote->movie))->toArray('get');
        $quote['author'] = (new UserResource($newQuote->user))->toArray('get');
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

        $quote = (new QuoteResource($quote))->toArray('get');

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

        $quotesPaginate = Quote::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $request->pageNum);

        $quotes = QuoteResource::collection(collect($quotesPaginate->items()))->toArray('get');

        $quotesFullData = array_map(function ($quote) {
            $quoteModel = Quote::find($quote['id']);

            $quote['movie'] = (new MovieResource($quoteModel->movie))->toArray('get');
            $quote['author'] = (new UserResource($quoteModel->user))->toArray('get');
            $quote['comments'] = CommentResource::collection($quoteModel->comments)->toArray('get');

            return $quote;
        }, $quotes);

        return response()->json(['quotes' => $quotesFullData, 'isLastPage' => $quotesPaginate->toArray()['last_page'] === $request->pageNum]);
    }

    public function getQuote(int $id): JsonResponse
    {
        $quoteModel = Quote::findOrFail($id);

        $quote = (new QuoteResource($quoteModel))->toArray('get');
        $quote['author'] =  (new UserResource($quoteModel->user))->toArray('get');
        $quote['comments'] = CommentResource::collection($quoteModel->comments)->toArray('get');

        return response()->json(['quote' => $quote]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->searchBy;

        if($search[0] === '#') {
            $search = ltrim($search, '#');
            $quotesPaginate = Quote::whereRaw('LOWER(JSON_EXTRACT(text, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(text, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $request->pageNum);

            $quotes = QuoteResource::collection(collect($quotesPaginate->items()))->toArray('get');

            $quotesFullData = array_map(function ($quote) {
                $quoteModel = Quote::find($quote['id']);

                $quote['movie'] = (new MovieResource($quoteModel->movie))->toArray('get');
                $quote['author'] =  (new UserResource($quoteModel->user))->toArray('get');
                $quote['comments'] = CommentResource::collection($quoteModel->comments)->toArray('get');

                return $quote;
            }, $quotes);

            return response()->json(['quotes' => $quotesFullData, 'isLastPage' => $quotesPaginate['last_page'] === $request->pageNum]);
        }

        if($search[0] === '@') {
            $search = ltrim($search, '@');
            $moviesPaginate = Movie::whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'movies-per-page', $request->pageNum);

            $movies = MovieResource::collection(collect($moviesPaginate->items()))->toArray('get');

            return response()->json(['movies' => $movies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum]);
        }

        return response()->json(['quotes' => []], 204);
    }
}
