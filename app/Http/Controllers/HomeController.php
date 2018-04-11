<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chime;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {

        return view('home', ['user' => $req->user()]);
    }

    public function getChimes(Request $req) {
        $chimes = $req->user()->chimes()->get();
        return response()->json($chimes);
    }

    public function createChime(Request $req) {
        $user = $req->user();

        if ($user->permission_number >= 300) {
            $new_chime = Chime::create([
                'name' => $req->get('chime_name'),
                'access_code' => str_random(6)
            ]);
        
            $user->chimes()->attach($new_chime, [
                'permission_number' => $user->permission_number
            ]);

            return response()->json($new_chime);
        } else {
            return response('Invalid Permissions to Create Chime', 403);
        }
    }

    public function joinChime(Request $req) {
        $user = $req->user();

        if ($user->permission_number < 300) {
            $chime = (Chime::where('access_code', $req->route('access_code'))
                ->first());
        
            $user->chimes()->attach($chime, [
                'permission_number' => $user->permission_number
            ]);
        
            return response()->json($chime);
        } else {
            return response('Invalid Permissions to Join Chime', 403);
        }
    }

    public function deleteChime(Request $req) {
        $user = $req->user();
        $chime = ($user->chimes()->find($req->route('chime_id')));
        
        if ($chime->pivot->permission_number >= 300) {
            $chime->delete();
        
            return response('Chime Deleted', 200);
        } else {
            $user->chimes()->detach($chime);
            return response('Removed from Chime', 200);
        }
    }
}
