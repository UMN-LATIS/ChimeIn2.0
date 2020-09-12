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
        
        if(!$chime) {
            $returnData = array(
                'status' => '',
                'message' => "Error"
            );
            if(Auth::user()->guest_user) {
                $returnData["status"] = "AttemptAuth";
                $returnData["message"] = "Auth May Be Required";
            }
            else {
                $returnData["status"] = "Error";
                $returnData["message"] = "You don't have permission to access this Chime";
            }
            return Response()->json($returnData, 403);
        }

        $chime->load("folders");

        if ($chime != null && $chime->pivot->permission_number >= 300) {
            return response()->json($chime);
        } else {
            return response()->json(["status"=>"error", "message"=>"You don't have permission to access this Chime"], 403);
        }
    }

    public function update(Chime $chime, Request $req) {
        $chime->fill($req->all());
        $chime->save();
        return response()->json(["success"=>true]);
    }

    public function updateFolders(Chime $chime, Request $req) {
        $requestFolders = $req->get("folders");
        foreach($chime->folders as $folder) {
            foreach($requestFolders as $request) {
                if($request["id"] == $folder->id) {
                    $folder->order = $request["order"];
                    $folder->save();
                }
            }
        }
        return response()->json(["success"=>true]);
    }



    public function getChimes(Request $req) {
        $chimes = $req->user()->chimes()->get();
        return response()->json($chimes);
    }
    
    public function createChime(Request $req) {
        $user = $req->user();

        if ($user->permission_number >= 100) {

            $new_chime = new Chime();
            $new_chime->fill($req->all());

            $new_chime->access_code = $new_chime->getUniqueCode();
            $new_chime->save();
            $user->chimes()->attach($new_chime, [
                'permission_number' => 300
            ]);

            return response()->json($new_chime);
        } else {
            return response('Invalid Permissions to Create Chime', 403);
        }
    }

    public function joinChime(Request $req) {

        $accessCode = $req->route('access_code');
        $strippedAccessCode = preg_replace('/\D/', '', $accessCode);

        $chime = (Chime::where('access_code', $strippedAccessCode)
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
        Image::configure(array('driver' => 'imagick'));
        $validator = Validator::make($req->all(), [
             'image'  => 'required|max:24576',
         ]);

         if ($validator->fails()) {
            return response()->json(['message'=>'Images must be 24mb or less', 'sizeError' => $validator->errors()->getMessages()], 400);
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
            $image_resize->save(null, 70, 'jpg');
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
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $highest = $chime->folders()->max('order');
            $order_num = 1;

            if ($highest != null) {
                $order_num = $highest + 1;
            }

            $new_folder = $chime->folders()->create([
                'name' => $req->get('folder_name'),
                'order' => $order_num
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

    public function getOpenQuestions(Chime $chime) {
        if(!Auth::user()->chimes()->where('chime_id', $chime->id)->first()) {
            $returnData = array(
                'status' => '',
                'message' => "Error"
            );
            if(Auth::user()->guest_user) {
                $returnData["status"] = "AttemptAuth";
                $returnData["message"] = "Auth May Be Required";
            }
            else {
                $returnData["status"] = "Error";
                $returnData["message"] = "You don't have permission to access this Chime";
            }
            return Response()->json($returnData, 403);
        }


        $questions = \App\Question::join('folders', 'folders.id','=','questions.folder_id')->join('chimes','folders.chime_id','=','chimes.id')->whereNotNull('questions.current_session_id')->where('chimes.id', $chime->id)->select('questions.*')->with('current_session')->with('current_session.question')->get(); 
        $sessions = [];


        foreach($questions as $question) {
            $sessions[] = $question->current_session;
        }

        usort($sessions, function($a, $b) {
            if(strtotime($a->updated_at) == strtotime($b->updated_at)) {
                return $a->question->order < $b->question->order;
                // return 0;
            }
            return strtotime($a->updated_at)<strtotime($b->updated_at)?-1:1;
        });


        return response()->json([
            'chime' => $chime,
            'sessions' => $sessions
        ]);

    }

    public function exportChime(Chime $chime, Request $req) {
        $user = $req->user();

        $loadedChime = (
            $user
            ->chimes()
            ->where('chime_id', $chime->id)
            ->first());
        
        if ($loadedChime == null || $loadedChime->pivot->permission_number < 300) {
            return "Insufficient permissions";
        }
        $chime = $loadedChime;

       
        $folderArray = [];
        $questionArray = [];
        $globalUsers = [];
        $outputArray = [];
        $selectedFolders = null;
        if($req->get('selectedFolder')) {
            $selectedFolders = $req->get('selectedFolder');
        }

        foreach($chime->folders as $folder) {
            if($selectedFolders && !in_array($folder->id, $selectedFolders)) {
                continue;
            }
            $folderArray[$folder->id] = ["name"=>$folder->name, "questions"=>$folder->questions->count()];
            foreach($folder->questions as $question) {
                $questionArray[$question->id] = strip_tags($question->text);
            }
        }



        $exportType = $req->get("export_type");

        $headers = array(
                "Content-type" => "text/csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );


        $callback = function() use ($folderArray, $exportType, $questionArray, $chime) {
            $file = fopen('php://output', 'w');

            $headers = ['Student', 'ID', 'SIS User ID', 'SIS Login ID', 'Section'];
            $secondHeaders = ['Points Possible', '','','',''];
            switch ($exportType) {
                case 'folder_summary':

                    foreach($folderArray as $folderId => $folderInfo) {
                        $headers[] = $folderInfo["name"];
                        $secondHeaders[] = $folderInfo['questions'];
                    }
                    
                    fputcsv($file, $headers);
                    fputcsv($file, $secondHeaders);

                    foreach($chime->users as $participant) {
                        $row = [];
                        $row[] =  $participant->name;
                        $row[] = '';
                        $row[] = '';
                        $row[] = $participant->email;
                        $row[] = '';
                        foreach($folderArray as $folderId => $folderInfo) {
                            $responses = DB::table('responses')->where("user_id", $participant->id)->join('sessions', 'responses.session_id', '=', 'sessions.id')->join('questions', 'sessions.question_id', '=', 'questions.id')->join('folders', 'questions.folder_id', '=', 'folders.id')->where('folders.id', $folderId)->groupBy("questions.id")->select('questions.id')->get();
                            $row[] = $responses->count();
                        }
                        fputcsv($file, $row);
                        
                    
                    }
                    fclose($file);
        
                    break;
                case 'question_summary':

                    foreach($questionArray as $questionId => $questionText) {
                        $headers[] = $questionText;
                        $secondHeaders[] = 1;
                    }
                    fputcsv($file, $headers);
                    fputcsv($file, $secondHeaders);
                    foreach($chime->users as $participant) {
                        $row = [];
                        $row[] =  $participant->name;
                        $row[] = '';
                        $row[] = '';
                        $row[] = $participant->email;
                        $row[] = '';
                        foreach($questionArray as $questionId => $questionText) {
                            $responses = DB::table('responses')->where("user_id", $participant->id)->join('sessions', 'responses.session_id', '=', 'sessions.id')->join('questions', 'sessions.question_id', '=', 'questions.id')->where('questions.id', $questionId)->groupBy("questions.id")->select('questions.id')->get();
                            $row[] = $responses->count();
                        }
                        fputcsv($file, $row);
                    }
                    
                    break;
                case 'question_full':

                    foreach($questionArray as $questionId => $questionText) {
                        $headers[] = $questionText;
                        $secondHeaders[] = 1;
                    }
                    fputcsv($file, $headers);
                    fputcsv($file, $secondHeaders);

                    foreach($chime->users as $participant) {
                        $row = [];
                        $row[] =  $participant->name;
                        $row[] = '';
                        $row[] = '';
                        $row[] = $participant->email;
                        $row[] = '';
                        foreach($questionArray as $questionId => $questionText) {
                            $responses = DB::table('responses')->where("user_id", $participant->id)->join('sessions', 'responses.session_id', '=', 'sessions.id')->join('questions', 'sessions.question_id', '=', 'questions.id')->where('questions.id', $questionId)->select('responses.*')->get();
                            $responseModels = \App\Response::hydrate($responses->toArray());    
                            $responses = $responseModels->pluck('response_info');
                            foreach($responses as $key=>$value) {
                                switch($value["question_type"]) {
                                    case "multiple_choice": 
                                    case "slider": 
                                        $responses[$key] = $value["choice"];
                                        break;
                                    case "free_response": 
                                        $responses[$key] = $value["text"];
                                        break;
                                    case "image_response":
                                        $responses[$key] = $value["image_name"];
                                        break;
                                    case "heatmap_response":
                                        $responses[$key] = $value["image_coordinates"]["coordinate_x"] . "," . $value["image_coordinates"]["coordinate_y"];
                                        break;
                                }
                            }
                            if(count($responses) > 1) {
                                $row[] = json_encode($responses);
                            }
                            else if(count($responses) == 0) {
                                $row[] = "";
                            }
                            else {
                                $row[] = $responses[0];
                            }
                            
                        }
                        fputcsv($file, $row);
                    }
                    break;
                default:
                    # code...
                    break;
                }
        };
        return Response()->streamDownload($callback, "chimeExport.csv", $headers);


    }

    public function forceSync(Request $req, $chime) {
        if(\App\Library\LTIProcessor::syncChime($chime)) {
            return response()->json(["success"=>"success"]);
        }
        else {
            return response('Failed to sync', 500);
        }
    }
}
