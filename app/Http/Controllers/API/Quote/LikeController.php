<?php

namespace App\Http\Controllers\API\Quote;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\LikeQuote;
use App\Events\RecieveNotification;

class LikeController extends Controller
{
    public function like(int $id, Request $request): JsonResponse
    {
        $quote = Quote::find($id);
        $user = auth()->user();

        if($quote) {
            $likes = Like::where('quote_id', $quote->id)->get()->toArray();
            $likesSum = count($likes);
            $liked = array_filter($likes, function ($like) use ($user) {
                return $like['user_id'] === $user->id;
            });

            $liked = count($liked) ? true : false;

            if($request->liked !== null) {
                if($request->liked === true) {
                    Like::create([
                        'user_id' => $user->id,
                        'quote_id' => $quote->id
                    ]);

                    $likesSum = $likesSum + 1;
                    $liked = true;
                }

                if($request->liked === false) {
                    $likeId = Like::where([
                        ['user_id', $user->id],
                        ['quote_id', $quote->id]
                    ])->first()->id;

                    Like::destroy($likeId);

                    $likesSum = $likesSum - 1;
                    $liked = false;
                }

                if($user->id !== $quote->user_id) {
                    $notification = Notification::create(['from_user' => $user->id, 'to_user' => $quote->user_id, 'quote_id' => $quote->id, 'type' => 'like']);
                    $notificationFullData = [...$notification->toArray()];
                    $notificationFullData['user'] = $user;
                    event(new RecieveNotification($quote->user_id, $notificationFullData));
                }

                $isOwnQuote = $user->id === $quote->id;
                event(new LikeQuote($quote->id, $likesSum, $isOwnQuote));
            }

            return response()->json(['liked' => $liked]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);
    }
}
