<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function create(CreateQuoteRequest $request): JsonResponse
    {
        $attributes['movie_id'] = $request->movie_id;
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

        if($quote) {
            return response()->json(['message', 'Quote has been created'], 200);
        }
        return response()->json(['message', 'Something went wrong, please check provided details and try again'], 200);
    }
}
