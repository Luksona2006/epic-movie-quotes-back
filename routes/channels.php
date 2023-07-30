<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return $user->id === (int) $id;
});

Broadcast::channel('change-email.{id}', function ($user, $id) {
    return $user->id === (int) $id;
});

Broadcast::channel('messages.{id}', function ($user, $id) {
    return $user->id === (int) $id;
});
