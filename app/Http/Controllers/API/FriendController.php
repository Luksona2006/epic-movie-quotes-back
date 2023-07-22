<?php

namespace App\Http\Controllers\API;

use App\Events\RecieveNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Friend\FriendRequest;
use App\Http\Requests\Friend\AcceptFriendRequest;
use App\Http\Requests\Friend\DeclineFriendRequest;
use App\Http\Resources\FriendResource;
use App\Models\FriendRequest as ModelsFriendRequest;
use App\Models\Friend;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class FriendController extends Controller
{
    public function sendRequest(FriendRequest $request): JsonResponse
    {
        ModelsFriendRequest::create([
            'from_user' => auth()->id(),
            'to_user' => $request->to_user,
        ]);


        $notification = Notification::create([
            'to_user' => $request->to_user,
            'from_user' => auth()->id(),
            'type' => 'request',
        ]);

        $notificationFullData = [...$notification->toArray()];
        $notificationFullData['user'] = auth()->user();

        event(new RecieveNotification($request->to_user, $notificationFullData));

        return response()->json(['sended' => true]);
    }

    public function create(AcceptFriendRequest $request): JsonResponse
    {
        Friend::create([
            'first_user' =>  $request->from_user,
            'second_user' => auth()->id(),
        ]);

        $notification = Notification::create([
            'to_user' => $request->from_user,
            'from_user' => auth()->id(),
            'type' => 'accept',
        ]);

        $notificationFullData = [...$notification->toArray()];
        $notificationFullData['user'] = auth()->user();

        ModelsFriendRequest::where('from_user', $request->from_user)->where('to_user', auth()->id())->first()->delete();
        event(new RecieveNotification($request->from_user, $notificationFullData));

        return response()->json(['accepted' => true]);
    }

    public function destroyRequest(DeclineFriendRequest $request): JsonResponse
    {
        ModelsFriendRequest::where([['from_user', $request->from_user], ['to_user', auth()->id()]])
        ->orWhere([['from_user', auth()->id()], ['to_user', $request->from_user]])->firstOrFail()->delete();

        Notification::where([['from_user', $request->from_user], ['to_user', auth()->id()]])
        ->orWhere([['from_user', auth()->id()], ['to_user', $request->from_user]])->firstOrFail()->delete();

        return response()->json(['deleted' => true]);
    }

    public function index(int $id): JsonResponse
    {
        $friends = Friend::where('first_user', $id)->orWhere('second_user', $id)->get();
        return response()->json(['friends' => FriendResource::collection($friends)]);
    }
}
