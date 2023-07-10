<?php

namespace App\Http\Controllers\API\Quote;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\LikeQuote;
use App\Events\RecieveNotification;
use App\Models\QuoteUser;

class LikeController extends Controller
{
    public function like(Quote $quote, Request $request): JsonResponse
    {
        $userId = auth()->id();

        $likes = QuoteUser::where('quote_id', $quote->id)->get()->toArray();
        $likesSum = count($likes);
        $likedBefore = QuoteUser::where('quote_id', $quote->id)->where('user_id', $userId)->count() > 0;

        if($request->liked === true) {
            QuoteUser::create([
                'user_id' => $userId,
                'quote_id' => $quote->id
            ]);

            $likesSum = $likesSum + 1;
            $liked = true;
        }

        if($request->liked === false) {
            QuoteUser::where([
                ['user_id', $userId],
                ['quote_id', $quote->id]
            ])->first()->delete();

            $likesSum = $likesSum - 1;
            $liked = false;
        }

        $isOwnQuote = $userId === $quote->id;

        if(!$isOwnQuote || !$likedBefore) {
            $notification = Notification::create(['from_user' => $userId, 'to_user' => $quote->user_id, 'quote_id' => $quote->id, 'type' => 'like']);
            $notificationFullData = [...$notification->toArray()];
            $notificationFullData['user'] = auth()->user();
            event(new RecieveNotification($quote->user_id, $notificationFullData));
        }

        event(new LikeQuote($quote->id, $likesSum, $userId));


        return response()->json(['liked' => $liked]);
    }
}
