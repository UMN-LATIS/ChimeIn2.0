<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chime;
use Auth;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        // Auth::user()->impersonate(\App\User::find(8));
        Auth::user()->leaveImpersonation();
        return view('home', ['user' => $req->user()]);
    }

    public function getChimes(Request $req) {
        $chimes = $req->user()->chimes()->get();
        return response()->json($chimes);
    }
    
    public function createChime(Request $req) {
        $user = $req->user();

        if ($user->permission_number >= 100) {
            $new_chime = Chime::create([
                'name' => $req->get('chime_name'),
                'access_code' => strtolower(str_random(6))
            ]);
        
            $user->chimes()->attach($new_chime, [
                'permission_number' => 300
            ]);

            return response()->json($new_chime);
        } else {
            return response('Invalid Permissions to Create Chime', 403);
        }
    }

    public function joinChime(Request $req) {

        $chime = (Chime::where('access_code', strtolower($req->route('access_code')))
                ->first());
        
        if(!$chime) {
            return response()->json(["chimeNotFound"=>true], 401);
        }

        if($chime->require_login && Auth::user()->guest_user) {
            if($req->ajax()) {
                return response()->json(["requiresLogin"=>true], 401);
            }
            return redirect()->guest('login');
        }

        if(!Auth::user()->chimes->contains($chime)) {
            Auth::user()->chimes()->attach($chime, [
                'permission_number' => 100
            ]);
        }
        

        if($req->ajax()) {
            return response()->json($chime);
        }
        else {
            return redirect("/chimeParticipant/" . $chime->id);
        }
    }


    public function deleteChime(Request $req) {
        $user = $req->user();
        $chime = ($user->chimes()->find($req->route('chime_id')));
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $chime->delete();
        
            return response('Chime Deleted', 200);
        } else {
            $user->chimes()->detach($chime);
            return response('Removed from Chime', 200);
        }
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
