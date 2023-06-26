<?php

namespace App\Http\Controllers\API\Quote;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\CommentQuote;
use App\Events\RecieveNotification;

class CommentController extends Controller
{
    public function comment(int $id, Request $request): JsonResponse
    {
        $quote = Quote::find($id);
        $user = auth()->user();

        if($quote) {
            if($request->comment) {
                $comment = Comment::create([
                    'text' => $request->comment,
                    'quote_id' => $quote->id,
                    'user_id' => $user->id
                ]);

                $comment['user'] = $comment->user;

                if($user->id !== $quote->user_id) {
                    $notification = Notification::create(['from_user' => $user->id, 'to_user' => $quote->user_id ,'quote_id' => $quote->id, 'type' => 'comment']);
                    $notificationFullData = [...$notification->toArray()];
                    $notificationFullData['user'] = $user;
                    event(new RecieveNotification($quote->user_id, $notificationFullData));
                }

                $isOwnQuote = $user->id === $quote->id;
                event(new CommentQuote($quote->id, $comment, $isOwnQuote));
            }


            return response()->json(['quote_id' => $quote->id , 'comment' => $comment]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);
    }
}
