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
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function getAllNotifications(string $token): JsonResponse
    {
        $user = User::where('token', $token)->first();

        if($user) {
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->get()->toArray();

            $notificationsWithUsers = [];

            if(count($notifications)) {
                $notificationsWithUsers = array_map(function ($notification) use ($user) {
                    $notificationUserId = UserNotification::where('to_user_id', $user->id)->first()->from_user_id;
                    $notificationUser = User::where('id', $notificationUserId)->first();
                    $notification['time'] =  Carbon::parse($notification['created_at'])->diffForHumans(Carbon::now());
                    $notification['user'] =  $notificationUser;

                    return $notification;
                }, $notifications);
            }

            return response()->json(['notifications' => $notificationsWithUsers]);
        }

        return response()->json(['message' => 'Wrong user, you are not able to get notifications'], 401);
    }

    public function update(int $notificationId, Request $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();

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

    public function updateAll(string $userToken): JsonResponse
    {
        $user = User::where('token', $userToken)->first();

        if($user) {
            $notifications = Notification::where('user_id', $user->id)->get()->toArray();
            if($notifications) {
                foreach ($notifications as $notification) {
                    $notificationData = Notification::where('id', $notification['id'])->first();
                    $notificationData->seen = true;
                    $notificationData->save();
                };
                return response()->json(['message' => 'All notifications has been marked as read']);
            }

            return response()->json(['message' => 'Wrong id, no notificaiton found']);
        }

        return response()->json(['message' => 'Wrong user, you are not able to get notifications'], 401);
    }
}
