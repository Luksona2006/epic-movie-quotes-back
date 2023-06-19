<?php

namespace App\Http\Controllers\API;

use App\Events\QuoteNotification;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Quote;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationsController extends Controller
{
    public function getAllNotifications(): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $notifications = Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray();

            if(count($notifications)) {
                $notificationsWithUsers = [];
                $newsSum = 0;

                $notificationsWithUsers = array_map(function ($notification) use ($user, $newsSum) {
                    $notificationUserId = UserNotification::where('to_user_id', $user->id)->first()->from_user_id;
                    $notificationUser = User::where('id', $notificationUserId)->first();
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

            return response()->json(['message' => 'No notifications found'], 204);
        }

        return response()->json(['message' => 'Wrong user, you are not able to get notifications'], 401);
    }

    public function update(int $notificationId): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $notification = Notification::where('id', $notificationId)->first();

            if($notification) {
                $notification->seen = true;
                $notification->save();
                $notificationUserId = UserNotification::where('to_user_id', $user->id)->first()->from_user_id;
                $notificationUser = User::where('id', $notificationUserId)->first();
                $notification['time'] =  Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
                $notification['user'] =  $notificationUser;

                return response()->json(['notification' => $notification]);
            }


            return response()->json(['message' => 'Wrong id, no notificaiton found']);
        }

        return response()->json(['message' => 'Wrong user, you are not able to get notifications'], 401);
    }

    public function updateAll(): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $notifications = $user->notifications->get()->toArray();
            if($notifications) {
                foreach ($notifications as $notification) {
                    $notificationData = Notification::where('id', $notification['id'])->first();
                    $notificationData->seen = true;
                    $notificationData->save();
                };
                return response()->json(['message' => __('messages.all_notifications_marked')]);
            }

            return response()->json(['message' => __('messages.wrong_id')]);
        }

        return response()->json(['message' => __('messages.wrong_user')], 401);
    }
}
