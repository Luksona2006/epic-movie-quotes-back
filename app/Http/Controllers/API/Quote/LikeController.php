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
        $user = auth()->user();

        $likes = QuoteUser::where('quote_id', $quote->id)->get()->toArray();
        $likesSum = count($likes);
        $liked = QuoteUser::where('quote_id', $quote->id)->where('user_id', $user->id)->count() > 0;
        if($request->liked !== null) {
            if($request->liked === true) {
                QuoteUser::create([
                    'user_id' => $user->id,
                    'quote_id' => $quote->id
                ]);

                $likesSum = $likesSum + 1;
                $liked = true;
            }

            if($request->liked === false) {
                QuoteUser::where([
                    ['user_id', $user->id],
                    ['quote_id', $quote->id]
                ])->first()->delete();

                $likesSum = $likesSum - 1;
                $liked = false;
            }

            $isOwnQuote = $user->id === $quote->id;

            if(!$isOwnQuote) {
                $notification = Notification::create(['from_user' => $user->id, 'to_user' => $quote->user_id, 'quote_id' => $quote->id, 'type' => 'like']);
                $notificationFullData = [...$notification->toArray()];
                $notificationFullData['user'] = $user;
                event(new RecieveNotification($quote->user_id, $notificationFullData));
            }

            event(new LikeQuote($quote->id, $likesSum, $isOwnQuote));
        }

        return response()->json(['liked' => $liked]);
    }
}
