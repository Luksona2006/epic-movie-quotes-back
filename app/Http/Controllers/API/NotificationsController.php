<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationsController extends Controller
{
    public function getAllNotifications(): JsonResponse
    {
        $user = auth()->user();

        $notifications = Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray();

        if(count($notifications)) {
            $notificationsWithUsers = [];
            $newsSum = 0;

            $notificationsWithUsers = array_map(function ($notification) use ($user, $newsSum) {
                $notificationUserId = UserNotification::where('to_user_id', $user->id)->first()->from_user_id;
                $notificationUser = User::find($notificationUserId);
                $notification['time'] =  Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
                $notification['user'] =  $notificationUser;
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
        $user = auth()->user();


        if($notification) {
            $notification->seen = true;
            $notification->save();
            $notificationUserId = UserNotification::where('to_user_id', $user->id)->first()->from_user_id;
            $notificationUser = User::find($notificationUserId);
            $notification['time'] =  Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
            $notification['user'] =  $notificationUser;

            return response()->json(['notification' => $notification]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);

    }

    public function updateAll(): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications->get()->toArray();
        if($notifications) {
            foreach ($notifications as $notification) {
                $notificationData = Notification::find($notification['id']);
                $notificationData->seen = true;
                $notificationData->save();
            };
            return response()->json(['message' => __('messages.all_notifications_marked')]);
        }

        return response()->json(['message' => __('messages.wrong_id')]);
    }
}
