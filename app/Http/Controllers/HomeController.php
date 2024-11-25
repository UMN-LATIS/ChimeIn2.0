<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Laravel\Nova\Contracts\ImpersonatesUsers;

class HomeController extends Controller
{

    public function index(Request $request, ImpersonatesUsers $impersonator)
    {
        $user = $request->user();
        $user->isImpersonated = $impersonator->impersonating($request);

        return view('app', ['user' => $user]);
    }

    // call this URL with target=<target> to force a login and redirect
    public function loginAndRedirect(Request $req)
    {
        $target = $req->query('target');
        if (!Auth::user()->guest_user) {
            return redirect($target);
        } else {
            return redirect()->guest('login');
        }
    }
}
