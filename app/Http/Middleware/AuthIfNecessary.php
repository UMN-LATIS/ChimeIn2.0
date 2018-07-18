<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AuthIfNecessary
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::user()) {
            $user = new \App\User;
            $user->guest_user=true;
            $user->userType="guest";
            $user->email = str_random(10);
            $user->save();
            Auth::login($user);
        }
        return $next($request);
    }
}
