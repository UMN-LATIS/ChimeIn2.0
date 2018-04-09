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

Broadcast::channel('session-response.{session_id}', function ($user, $session_id) {
    // Responses can be handled by all admins on the chime
    $session = App\Session::find($session_id);
    $chime = App\Chime::find($session->chime_id);
    return ($chime
                ->users()
                ->where('user_id', $user->id)
                ->first()
                ->permission_number) >= 200;
});

Broadcast::channel('session-status.{session_id}', function ($user, $session_id) {
    // Session status events can be received by all members of chime
    $session = App\Session::find($session_id);
    $chime = App\Chime::find($session->chime_id);
    return ($chime
                ->users()
                ->where('user_id', $user->id)
                ->first()) != null;
});

Broadcast::channel('start-session.{chime_id}', function ($user, $chime_id) {
    // Session start events can be received by all members of chime
    $chime = App\Chime::find($chime_id);
    return ($chime
                ->users()
                ->where('user_id', $user->id)
                ->first()) != null;
});