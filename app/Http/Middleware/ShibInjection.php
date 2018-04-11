<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ShibInjection
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
        foreach (config('shibboleth.user') as $local => $server) {
            $map[$local] = $this->getServerVariable($server);
        }

        foreach($map as $key=>$value) {
            Auth::user()->$key = $value;
        }
        dd(Auth::user());
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


        }

        $variable = Request::server($variableName);

        return (!empty($variable)) ?
            $variable :
            Request::server('REDIRECT_' . $variableName);
    }


}


