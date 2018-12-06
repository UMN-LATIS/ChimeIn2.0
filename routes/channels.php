<?php

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

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('session-response.{chime_id}', function ($user, $chime_id) {
    // Responses can be handled by all admins on the chime
    $chime = App\Chime::find($chime_id);
    return ($chime
                ->users()
                ->where('user_id', $user->id)
                ->first()
                ->pivot
                ->permission_number) >= 200;
});

Broadcast::channel('session-status.{chime_id}', function ($user, $chime_id) {
    // Session start events can be received by all members of chime
    $chime = App\Chime::find($chime_id);

    return ($chime
        ->users()
        ->where('user_id', $user->id)
        ->first()) != null;
});

