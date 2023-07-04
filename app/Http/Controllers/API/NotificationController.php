<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $notifications = Notification::where('to_user', $user->id)->latest()->toArray();

        if(count($notifications)) {
            $notificationsWithUsers = [];
            $newsSum = 0;

            $notificationsWithUsers = array_map(function ($notification) use ($newsSum) {
                $notification['time'] = Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
                $notification['user'] = User::find($notification['from_user']);
                if(!$notification['seen']) {
                    $newsSum = $newsSum + 1;
                }
                return $notification;
            }, $notifications);

            $newsSum = count(array_filter($notifications, function ($notification) {
                return $notification['seen'] === 0;
            }));

            return response()->json(['notifications' => $notificationsWithUsers, 'newsSum' => $newsSum]);
        }

        return response()->json(['message' => __('messages.wrong_user')], 204);
    }

    public function update(Notification $notification): JsonResponse
    {
        $notification->seen = true;
        $notification->save();
        $notification['time'] = Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
        $notification['user'] = User::find($notification->from_user);

        return response()->json(['notification' => $notification]);
    }

    public function updateAll(): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications->get()->toArray();
        if($notifications) {
            Notification::all()->update(['seen' => true]);
            return response()->json(['message' => __('messages.all_notifications_marked')]);
        }

        return response()->json(['message' => __('messages.wrong_id')]);
    }
}
