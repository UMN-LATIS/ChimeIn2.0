<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        return view('app', ['user' => $request->user()]);
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
