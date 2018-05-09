<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Response;
use App\Question;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ChimeController extends Controller
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
    public function index(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            return view('chime', ['user' => $user]);
        } else {
            return view('chime_student', ['user' => $user]);
        }
    }

    public function getChime(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            return response()->json([
                'chime' => $chime,
                'folders' => $chime->folders()->get()
            ]);
        } else {
            $open_sessions = $chime->sessions()->where('in_progress', true);
    
            return response()->json([
                'chime' => $chime,
                'sessions' => $open_sessions->get()
            ]);
        }
    }

    public function getUsers(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $users = $chime->users()->get();
            $ids = $users->map(function($u) {
                return [
                    'name' => $u->name,
                    'email' => $u->email,
                    'id' => $u->id,
                    'permission_number' => $u->pivot->permission_number];
            });

            return response()->json([
                'users' => $ids
            ]);
        } else {
            return response('Invalid Permissions to get Users', 403);
        }
    }

    public function addUser(Request $req) {
        $user = $req->user();
        $newUser = User::where('email', $req->get('email'))->first();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        $pn = $chime->pivot->permission_number;
        
        if (
            $chime != null 
            && $newUser != null 
            && $pn >= 300 
            && $newUser->permission_number < $pn
            && !$chime->users->contains($newUser->id)
            ) {
            $newUser->chimes()->attach($chime, [
                'permission_number' => $newUser->permission_number
            ]);

            return response()->json([
                'new_user' => $newUser
            ]);
        } else {
            return response('Cannot add user', 400);
        }
    }

    public function changePermission(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());

        $pn = $chime->pivot->permission_number;
        $changingUser = $chime->users()->find($req->route('user_id'));
        $newPN = $req->get('permission_number');
        
        if ($chime != null && $changingUser != null && $pn >= 300 && $newPN < $pn) {
            $changingUser->pivot->update(['permission_number' => $newPN]);

            return response()->json([
                'name' => $changingUser->name,
                'id' => $changingUser->id,
                'email' => $changingUser->email,
                'permission_number' => $changingUser->pivot->permission_number,
            ]);
        } else {
            return response('Cannot change user permissions', 400);
        }
    }

    public function removeUser(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $user->id != ($req->route('user_id')) && $chime->pivot->permission_number >= 300) {
            $chime->users()->detach($req->route('user_id'));
            return response('User removed', 200);
        } else {
            return response('Cannot remove user', 400);
        }
    }

    public function getOpenSessions(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        $open_sessions = $chime->sessions()->where('in_progress', true);
    
        return response()->json($open_sessions->get());
    }

    public function getPastResponses(Request $req) {
        if ((int)$req->route('chime_id') <= 0) {
            return response('Chime ID must be an integer', 400);
        } else {
            $ids = DB::select(
                'SELECT DISTINCT r.id AS response_id, q.id AS question_id'
                . ' FROM responses r, sessions s, questions q'
                . ' WHERE r.user_id = ' . $req->user()->id
                . ' AND r.session_id = s.id'
                . ' AND s.question_id = q.id'
                . ' AND s.in_progress = "0"'
                . ' AND s.chime_id = ' .  $req->route('chime_id'));
    
            foreach ($ids as $id) {
                $id->response = Response::find($id->response_id);
                $id->question = Question::find($id->question_id);
            }
        
            return response()->json($ids);
        }
    }

    public function getImage(Request $req) {
        $path = Storage::get('image/'. $req->route('image_name'));

        return Image::make($path)->response();
    }

    public function uploadImage(Request $req) {
        $user = $req->user();
        $chime = $user->chimes()->find($req->route('chime_id'));

        if ($chime != null) {
            $path = $req->file('image')->store('image');
            
            return response('/api/chime/'. $chime->id. '/'. $path, 200);
        } else {
            return response('Chime not found', 400);
        }
    }

    public function createFolder(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $new_folder = $chime->folders()->create([
                'name' => $req->get('folder_name')
            ]);
        
            return response()->json($new_folder);
        } else {
            return response('Invalid Permissions to Create Folder', 403);
        }
    }

    public function editFolder(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $folder->name = $req->get('folder_name');
            $folder->save();
        
            return response()->json($folder);
        } else {
            return response('Invalid Permissions to Delete Folder', 403);
        }
    }

    public function deleteFolder(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $chime->folders()->find($req->route('folder_id'))->delete();
        
            return response('Folder Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Folder', 403);
        }
    }
}
