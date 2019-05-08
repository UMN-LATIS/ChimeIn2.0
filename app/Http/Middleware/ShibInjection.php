<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ShibInjection
{
    /**
     * Inject shib parameters onto the user object (this avoids having to archive them to the DB)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach (config('shibboleth.user') as $local => $server) {
            $map[$local] = $this->getServerVariable($server);
        }

        foreach($map as $key=>$value) {
            if($value) {
                Auth::user()->$key = $value;
            }
        }

        $manager = app('impersonate');
        if($manager->isImpersonating()) {
            Auth::user()->impersonating = true;
        }
        else {
            Auth::user()->impersonating = false;
        }

        // dd(Auth::user());
        return $next($request);
    }

    private function getServerVariable($variableName)
    {
        if (config('shibboleth.emulate_idp') == true) {
            $users = config('shibboleth.emulate_idp_users');
            foreach($users as $userId => $userValues) {
                if($userValues['umnDID'] == Auth::user()->umndid) {
                    return $userValues[$variableName];
                }
            }
        } else if (!Auth::user()->guest_user) {
            $variable = \Request::server($variableName);

            return (!empty($variable)) ?
                $variable :
                \Request::server('REDIRECT_' . $variableName);
        } else {
            return null;
        }
    }
}


