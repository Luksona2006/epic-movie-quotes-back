<?php

namespace App\Http\Controllers\API\Quote;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\CreateQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Resources\MovieResource;
use App\Http\Resources\QuoteResource;
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

        $quote = Quote::with('movie', 'user', 'comments')->find($quote->id);

        return (new QuoteResource($quote))->response()->setStatusCode(200);
    }

    public function update(Quote $quote, UpdateQuoteRequest $request): JsonResponse
    {
        if($request->user()->can('update', $quote)) {
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

            return (new QuoteResource($quote))->response()->setStatusCode(200);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.update_quote')])]);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        if(auth()->user()->can('delete', $quote)) {
            $quote->delete();
            return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.quote')])]);
        }


        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.update_movie')])]);
    }

    public function index(Request $request): JsonResponse
    {
        $quotesPaginate = Quote::with('comments', 'user', 'movie')->latest()->paginate(10, ['*'], 'quotes-per-page', $request->pageNum);

        $quotes = QuoteResource::collection($quotesPaginate->items());

        return response()->json(['quotes' => $quotes, 'isLastPage' => $quotesPaginate->toArray()['last_page'] === $request->pageNum]);
    }

    public function show(int $id): JsonResponse
    {
        $quote = Quote::with('user', 'comments')->findOrFail($id);

        if(auth()->user()->can('view', Quote::class)) {
            return (new QuoteResource($quote))->response()->setStatusCode(200);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_quote')])]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->searchBy;

        if($search[0] === '#') {
            $search = ltrim($search, '#');
            $quotesPaginate = Quote::with('movie', 'user', 'comments')->whereRaw('LOWER(JSON_EXTRACT(text, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(text, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->latest()->paginate(10, ['*'], 'quotes-per-page', $request->pageNum);

            $quotes = QuoteResource::collection($quotesPaginate->items());

            return response()->json(['quotes' => $quotes, 'isLastPage' => $quotesPaginate['last_page'] === $request->pageNum]);
        }

        if($search[0] === '@') {
            $search = ltrim($search, '@');
            $moviesPaginate = Movie::whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
            ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
            ->latest()->paginate(10, ['*'], 'movies-per-page', $request->pageNum);

            $movies = MovieResource::collection($moviesPaginate->items());

            return response()->json(['movies' => $movies, 'isLastPage' => $moviesPaginate['last_page'] === $request->pageNum]);
        }

        return response()->json(['quotes' => []], 204);
    }
}
