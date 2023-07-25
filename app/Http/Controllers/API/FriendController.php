<?php

namespace App\Http\Controllers\API;

use App\Events\RecieveNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Friend\FriendRequest;
use App\Http\Requests\Friend\AcceptFriendRequest;
use App\Http\Requests\Friend\DeclineFriendRequest;
use App\Http\Resources\FriendResource;
use App\Models\Friend;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class FriendController extends Controller
{
    public function sendRequest(FriendRequest $request): JsonResponse
    {
        Friend::create([
            'user_id' => auth()->id(),
            'friend_id' => $request->to_user,
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
        Friend::where('friend_id', auth()->id())->where('user_id', $request->from_user)->update(['accepted' => true]);

        $notification = Notification::create([
            'to_user' => $request->from_user,
            'from_user' => auth()->id(),
            'type' => 'accept',
        ]);

        $notificationFullData = [...$notification->toArray()];
        $notificationFullData['user'] = auth()->user();

        event(new RecieveNotification($request->from_user, $notificationFullData));

        return response()->json(['accepted' => true]);
    }

    public function destroyRequest(DeclineFriendRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->pendingFriendsTo->where('friend_id', $request->from_user)->firstOrFail()->delete();
        $user->pendingFriendsFrom->where('user_id', $request->from_user)->firstOrFail()->delete();

        Notification::where([['from_user', $request->from_user], ['to_user', auth()->id()]])
        ->orWhere([['from_user', auth()->id()], ['to_user', $request->from_user]])->firstOrFail()->delete();

        return response()->json(['deleted' => true]);
    }

    public function index(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json(['friends' => FriendResource::collection($user->friends)]);
    }
}
