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
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function comment(Quote $quote, Request $request): JsonResponse
    {
        $user = auth()->user();

        $comment = Comment::create([
            'text' => $request->comment,
            'quote_id' => $request->quote_id,
            'user_id' => $request->user_id
        ]);

        $comment = new CommentResource($comment);

        $isOwnQuote = $user->id === $quote->user_id;

        if(!$isOwnQuote) {
            $notification = Notification::create(['from_user' => $user->id, 'to_user' => $quote->user_id ,'quote_id' => $quote->id, 'type' => 'comment']);
            $notificationFullData = [...$notification->toArray()];
            $notificationFullData['user'] = $user;
            event(new RecieveNotification($quote->user_id, $notificationFullData));
        }

        event(new CommentQuote($quote->id, $comment, $isOwnQuote));


        return response()->json(['quote_id' => $quote->id , 'comment' => $comment]);
    }
}
