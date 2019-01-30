<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Response;
use App\Question;
use App\User;
use App\Chime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Validator;
use Auth;

class ChimeController extends Controller
{

    public function getChime(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        $chime->load("folders");

        if ($chime != null && $chime->pivot->permission_number >= 200) {
            return response()->json($chime);
        } else {
            
            // TODO : this should be its own method 
            // 
            // 
            // $questions = \App\Question::whereNotNull("current_session")->whereHas('folder', function($f) use ($chime) {
            //     $f->whereHas("chime", function($c) use ($chime) {
            //         $c->where("id", $chime->id);
            //     });
            // })->get();


            $questions = \App\Question::whereNotNull("current_session_id")->whereHas('folder.chime', function($c) use ($chime) {
                $c->where("id", $chime->id);
            })->get();
            $sessions = [];


            foreach($questions as $question) {
                $question->current_session->load("question");
                $sessions[] = $question->current_session;
            }

            usort($sessions, function($a, $b) {
                if(strtotime($a->updated_at) == strtotime($b->updated_at)) {
                    return 0;
                }
                return strtotime($a->updated_at)<strtotime($b->updated_at)?-1:1;
            });

            // dd($questions);

            return response()->json([
                'chime' => $chime,
                'sessions' => $sessions
            ]);
        }
    }

    public function update(Chime $chime, Request $req) {
        $chime->fill($req->all());
        $chime->save();
        return response()->json(["success"=>true]);
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
                    'editPermission' => false,
                    'permission_number' => $u->pivot->permission_number];
            });

            return response()->json($ids);
        } else {
            return response('Invalid Permissions to get Users', 403);
        }
    }

    public function syncUsers(Chime $chime, Request $req) {
        $user = Auth::user();
        // check perm

        $users = $req->get('users');
        $mappedUsers = array_reduce($users, function($result, $u) {
            $result[$u['id']] = ["permission_number" => $u['permission_number']];
            return $result;
        });        

        $chime->users()->sync($mappedUsers);
        $chime->save();
        return response()->json(["success"=>true]);
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

   
  
    public function getImage(Request $req) {
        $path = Storage::get('image/'. $req->route('image_name'));

        return Image::make($path)->response();
    }

    public function uploadImage(Request $req) {
        $user = Auth::user();
        $chime = $user->chimes()->find($req->route('chime_id'));

        $validator = Validator::make($req->all(), [
             'image'  => 'required|max:8192',
         ]);

         if ($validator->fails()) {
            return response()->json(['sizeError' => $validator->errors()->getMessages()], 400);
         }

        if ($chime != null) {
            $image = $req->file('image');
            if(!$image) {
                return response()->json(["error" => "unableToStore"]);
            }

            $image_resize = Image::make($image);
            $image_resize->resize(2048, 2048, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image_resize->save();
            $path = $image->store('public');

            if(!$path) {
                return response()->json(["error" => "unableToStore"]);
            }

            return response()->json(["image" => basename($path)]);
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
