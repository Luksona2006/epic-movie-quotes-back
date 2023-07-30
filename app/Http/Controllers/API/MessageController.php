<?php

namespace App\Http\Controllers\API;

use App\Events\SendMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $messages = ($user->messagesFrom()->wherePivot('user_id', $id)->get()->push(...$user->messagesTo()->wherePivot('friend_id', $id)->get()))->sortBy('pivot.created_at');
        $resource =  MessageResource::collection($messages)->toArray('get');

        return response()->json(['messages' => [...$resource]]);
    }

    public function create(MessageRequest $request): JsonResponse
    {
        $message = Message::create([...$request->all(), 'user_id' => auth()->id()]);
        event(new SendMessage($request->friend_id, auth()->id(), $request->text));

        return (new MessageResource($message))->response()->setStatusCode(200);
    }
}
