<?php

namespace App\Http\Controllers\API;

use App\Events\RecieveNotification;
use App\Events\SendMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\Notification;
use Carbon\Carbon;
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

        $notification = Notification::where([['from_user', auth()->id()], ['to_user', $request->friend_id], ['type', 'message']])->orWhere([['to_user', auth()->id()], ['from_user', $request->friend_id], ['type', 'message']]);

        if($notification->count()) {
            $notification->first()->update(
                ['from_user' => auth()->id(), 'to_user' => $request->friend_id, 'text' => substr($request->text, 0, 20), 'seen' => false, 'created_at' => Carbon::now()]
            );

            $notification = $notification->first();
        } else {
            $notification = Notification::create(['from_user' => auth()->id(), 'to_user' => $request->friend_id, 'type' => 'message', 'text' => $request->text]);
        }

        $notificationFullData = [...$notification->toArray()];

        $notificationFullData['user'] = new UserResource(auth()->user());
        $notificationFullData['time'] = Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
        event(new RecieveNotification($request->friend_id, $notificationFullData));

        return (new MessageResource($message))->response()->setStatusCode(200);
    }
}
