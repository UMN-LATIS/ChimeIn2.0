<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Laravel\Nova\Contracts\ImpersonatesUsers;

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
        Auth::user()->isImpersonated = app(ImpersonatesUsers::class)->impersonating($request);
        
        // skip shib injection if we're impersonating
        if (Auth::user()->isImpersonated) {
            return $next($request);
        }

        foreach (config('shibboleth.user') as $local => $server) {
            $map[$local] = $this->getServerVariable($server);
        }

        foreach($map as $key=>$value) {
            if($value) {
                Auth::user()->$key = $value;
            }
        }

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


