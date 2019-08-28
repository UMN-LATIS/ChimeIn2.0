<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chime;
use Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        Log::warning("Genearting some log noise");
        $cookie =  Cookie::get('ltiLaunch');
        return view('home', ['user' => $req->user(), 'ltiLaunch'=>$cookie]);
    }

    
    // call this URL with target=<target> to force a login and redirect
    public function loginAndRedirect(Request $req) {
        $target = $req->query('target');
        if(!Auth::user()->guest_user) {
            return redirect($target);
        }
        else {
            return redirect()->guest('login');    
        }
    }
}
