<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = Notification::where('to_user', auth()->id())->orWhere('from_user', auth()->id())->latest()->get()->toArray();

        if(count($notifications)) {
            $notificationsWithUsers = [];
            $newsSum = 0;
            $messagesNewsSum = 0;

            foreach ($notifications as $notification) {
                if(($notification['type'] !== 'message' && $notification['to_user'] === auth()->id()) || $notification['type'] === 'message') {

                    $notification['time'] = Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
                    $notification['user'] = new UserResource($notification['from_user'] !== auth()->id() ? User::findOrFail($notification['from_user']) : User::findOrFail($notification['to_user']));

                    array_push($notificationsWithUsers, $notification);
                }
            };

            $newsSum = count(array_filter($notificationsWithUsers, function ($notification) {
                if(!$notification['seen'] && $notification['type'] !== 'message') {
                    return $notification;
                }
            }));

            $messagesNewsSum = count(array_filter($notificationsWithUsers, function ($notification) {
                if(!$notification['seen'] && ($notification['type'] === 'message' && $notification['to_user'] === auth()->id())) {
                    return $notification;
                }
            }));

            return response()->json(['notifications' => $notificationsWithUsers, 'newsSum' => $newsSum, 'messagesNewsSum' => $messagesNewsSum]);
        }

        return response()->json(['message' => __('messages.no_notifications_yet')], 204);
    }

    public function update(Notification $notification): JsonResponse
    {
        $notification->seen = true;
        $notification->save();
        $notification['time'] = Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
        $notification['user'] = new UserResource(User::find($notification->from_user));

        return response()->json(['notification' => $notification]);
    }

    public function updateAll(): JsonResponse
    {
        Notification::where('to_user', auth()->id())->update(['seen' => true]);
        return response()->json(['message' => __('messages.all_notifications_marked')]);
    }
}
