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
use App\Events\EndSession;
use \App\Library\LTI13Processor;

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

        if ($chime != null && ($chime->pivot->permission_number >= 300 || $chime->students_can_view)) {
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
        $chimes = $req->user()
          ->chimes()
          ->withCount('presenters')
          ->get();
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
        if ($chime == null) {
            return response('Chime not found', 404);
        }

        if ($chime->pivot->permission_number >= 300) {
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

    public function updateChimeUser(Request $request, Chime $chime, User $user) 
    {
      abort_unless(Auth::user()->canEditChime($chime->id), 403);
      
      $validated = $request->validate([
        'permission_number' => [
          'required', 
          'integer', 
          'numeric', 
          'multiple_of:100',
          'min:0',
          'max:300'
        ]
      ]);

      $chime->users()->updateExistingPivot($user->id, [
        'permission_number' => $validated['permission_number']
      ]);

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

    public function removeChimeUser($chimeId, $userId)
    {
      abort_unless(
        Auth::user()->canEditChime($chimeId)
          || $userId === 'self',
        403
      );

      if ($userId === 'self') {
        $userId = Auth::user()->id;
      }

      $chime = Auth::user()->chimes()->findOrFail($chimeId);
      $chimeUser = $chime->users()->findOrFail($userId);

      // is this the last presenter on the chime?
      // if so, the user needs to assign the chime to a new presenter
      // or delete the chime
      abort_if(
        $chime->presenters->count() < 2
          && $chimeUser->isPresenter($chimeId),
        405,
        "Method not allowed. Cannot remove user `$userId` on chime `$chimeId`. This user is the last user with a presentation role. Chimes must always have one presenter. Either make another presenter or delete this chime if you no longer need it."
      );

      $chime->users()->detach($userId);
      return response('Removed user from Chime', 200);
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
                return response()->json(["message" => "Unable to Store Image"], 400);
            }

            try {
                $image_resize = Image::make($image);
            }
            catch (\Exception $e) {
                return response()->json(["message" => "Image Could Not be Read", "rawError"=>$e], 400);
            }
           

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
            ])->loadCount('questions');
        
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
            $folder = $chime->folders()->find($req->route('folder_id'));
            foreach($folder->questions as $question) {
                $currentSession = $question->current_session;
                if($currentSession) {
                    event(new EndSession($chime, $currentSession));
                    $question->current_session()->dissociate();
                    $question->save();
                }
                
            }
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


        $questions = \App\Question::join('folders', 'folders.id', '=', 'questions.folder_id')
            ->join('chimes', 'folders.chime_id', '=', 'chimes.id')
            ->whereNotNull('questions.current_session_id')
            ->where('chimes.id', $chime->id)
            ->select('questions.*')
            ->with('current_session')
            ->with('current_session.question')
            ->with('current_session.question.folder')
            ->get();

        $sessions = [];

        foreach ($questions as $question) {
            $sessions[] = $question->current_session;
        }

        usort($sessions, function ($a, $b) {
            $a_time = strtotime($a->updated_at);
            $b_time = strtotime($b->updated_at);

            // If there's a tie, sort by question order
            if ($a_time == $b_time) {
                return $a->question->order - $b->question->order;
            }
            // sort by update time, most recent at the top
            return $a_time < $b_time ? 1 : -1;
        });


        return response()->json([
            'chime' => $chime,
            'sessions' => $sessions
        ]);

    }

    private function getValuesForFolder($chime, $folder, $user, $correctOnly) {
        $result = [];

        foreach($folder["folder"]->questions->sortBy('questions.order') as $question) {
            $userResponses = $question->sessions->flatmap(function($value) use ($user) {
                return $value->responses->where("user_id", $user->id);
            });


            /*
             *  I really think there's a better way to pluck the correct choice when the correct key is true, but 
             *  I can't figure out it. Once we're on mysql8, I think this is possible - worst case by synthesizing a view?
             */
            if($correctOnly > 0 && $question->question_info["question_type"] == "multiple_choice") {
                // get only the correct objects from the array of answers
                $correctAnswers = null;
                $correctText = null;
                
                $correctAnswers = array_filter($question->question_info["question_responses"], function($k) { if(isset($k["correct"])) { return $k["correct"]==true;} return false;});
                // grab only the text itself from the array
                $correctText = array_map(function($k) { return $k["text"];}, $correctAnswers);
                $points = 0;
                foreach($userResponses as $response) {
                    $choice = [];
                    if(isset($response->response_info["choice"])) {
                        if(is_array($response->response_info["choice"])) {
                            $choice = $response->response_info["choice"];
                        }
                        else {
                            $choice = [$response->response_info["choice"]];
                        }
                        
                    }
                    if(!$correctText || count(array_intersect($choice, $correctText)) > 0) {
                        $points = max($points, 1);
                    }
                    if($correctText && $correctOnly == 2 && count(array_intersect($choice, $correctText)) == 0) {
                        $points = max($points, 0.5);
                    }
                }
                $result[] = $points;
            }
            elseif($userResponses->count() > 0) {
                $result[] = 1;
            }
            else {
                $result[] = 0;
            }
        }
        return $result;
    }

    public function exportChime(Chime $chime, Request $req) {
        $user = $req->user();
        ini_set('max_execution_time', 300);
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

        $exportType = $req->get("export_type");

        foreach($chime->folders as $folder) {
            if($selectedFolders && !in_array($folder->id, $selectedFolders)) {
                continue;
            }
            $folder->load('questions', 'questions.sessions', 'questions.sessions.responses');
            $folderArray[$folder->id] = ["name"=>$folder->name, "questions"=>LTI13Processor::getQuestionsWithResponsesCount($folder->questions), "folder"=>$folder];
            foreach($folder->questions()->orderBy("order")->get() as $question) {
                if($exportType == "question_sessions") {
                    foreach($question->sessions as $session) {
                        $questionArray[$question->id . "_" . $session->id] = strip_tags($question->text) . " : " . $session->created_at;
                    }
                }
                else {
                    $questionArray[$question->id] = strip_tags($question->text);
                }
                
            }
        }



       
        $onlyCorrectAnswers = $req->get("only_correct_answers");
        $headers = array(
                "Content-type" => "text/csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );

        $callback = function() use ($folderArray, $exportType, $questionArray, $chime, $onlyCorrectAnswers) {
            $file = fopen('php://output', 'w');

            $headers = ['Student', 'ID', 'SIS User ID', 'SIS Login ID', 'Section'];
            $secondHeaders = ['Points Possible', '','','',''];
            // add response date info to question_full and session responses, since those aren't canvas compatible anyways
            if($exportType == "question_full" || $exportType == "question_sessions") {
                $headers[] = 'Latest Response Date';
                $secondHeaders[] = '';
            }
            

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
                            $overallCount = $this->getValuesForFolder($chime, $folderInfo, $participant, $onlyCorrectAnswers);
                            $row[] = array_sum($overallCount);
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
                        foreach($folderArray as $folderId => $folderInfo) {
                            $row = array_merge($row, $this->getValuesForFolder($chime, $folderInfo, $participant, $onlyCorrectAnswers));
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
                        $mostRecentDate = null;
                        $userResponseRows = array();
                        foreach($folderArray as $folderId => $folderInfo) {
                            foreach($folderInfo["folder"]->questions()->orderBy("order")->get() as $question) { 
                                $userResponses = $question->sessions->flatmap(function($value) use ($participant) {
                                    return $value->responses->where("user_id", $participant->id);
                                });
                                $mostRecentDate = max($mostRecentDate, $userResponses->max("created_at"));
                                $userResponseRows[] = $this->getRowForResponses($userResponses);
                            }
                        }
                        // we have to shift how we write these to avoid some copy pasta with our loops
                        $row[] = $mostRecentDate;
                        foreach($userResponseRows as $response) {
                            $row[] = $response;
                        }
                        fputcsv($file, $row);
                    }
                    break;
                case 'question_only':
                        foreach($folderArray as $folderId => $folderInfo) {
                            
                            foreach($folderInfo["folder"]->questions()->orderBy("order")->get() as $question) { 
                                $row = [];
                                $row[] = $question->text;
                                $row[] = json_encode($question->question_info);
                                fputcsv($file, $row);
                            }
                            
                        }
                    break;
                case 'question_sessions':

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
                            $mostRecentDate = null;
                            $userResponseRows = array();
                            foreach($folderArray as $folderId => $folderInfo) {
                                foreach($folderInfo["folder"]->questions()->orderBy("order")->get() as $question) { 
                                    
                                    foreach($question->sessions as $session) {
                                        $userResponses = $session->responses->where("user_id", $participant->id);
                                        $userResponseRows[] = $this->getRowForResponses($userResponses);
                                        $mostRecentDate = max($mostRecentDate, $userResponses->max('created_at'));
                                    }
                                    
                                }
                            }
                            $row[] = $mostRecentDate;
                            foreach($userResponseRows as $response) {
                                $row[] = $response;
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

    private function getRowForResponses(object $responses) {
        $textResponses = array();
        foreach($responses as $key=>$value) {
            $textResponses[] = $value->response_text;
        }
        $row = "";
        if(count($textResponses) > 1) {
            $row = json_encode($textResponses);
        }
        else if(count($textResponses) == 0) {
            $row = "";
        }
        else {
            if(is_array($textResponses[0])) {
                $row = json_encode($textResponses[0]);
            }
            else {
                $row = $textResponses[0];
            }
            
        }
        if(!is_string($row)) {
            return "";
        }
        return $row;
    }

    public function forceSync(Request $req, $chime) {
        if($chime->resource_link_pk > 0) {
            if(\App\Library\LTIProcessor::syncChime($chime)) {
                return response()->json(["success"=>"success"]);
            }
        }
        else if($chime->lti13_resource_link_id > 0) {
            if(\App\Library\LTI13Processor::syncChime($chime)) {
             return response()->json(["success"=>"success"]);
            }
        }
        return response('Failed to sync', 500);
    }
}
