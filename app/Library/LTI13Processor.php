<?php


namespace App\Library;

use DB;
use Log;
use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;
use \App\LTI13ResourceLink;

class LTI13Processor {
	
	function __construct() {
		
	}
	
    static function periodicTask() {

		$oldestResponse = now()->subMinutes(200);
		$newestResponse = now()->subMinutes(180);

		// Lti1.1 sync
        $folderIds = DB::table('folders')
		->join("questions", "folders.id", "=", "questions.folder_id")
		->join("sessions", "questions.id","=","sessions.question_id")
        ->join("responses", "sessions.id","=","responses.session_id")
        ->select("folders.id")
        ->whereNotNull("folders.resource_link_pk")
		->groupBy("responses.session_id")
		->groupBy("folders.id")
		->havingRaw("max(`responses`.`created_at`) > ?", [$oldestResponse])
		->havingRaw("max(`responses`.`created_at`) < ?", [$newestResponse])->get()->unique()->pluck("id");
		$folders = \App\Folder::find($folderIds);

		echo "Syncing LTI 1.1 Folders\n";
        foreach($folders as $folder) {
			echo "Syncing folder: " . $folder->id . "\n";
            \App\Library\LTIProcessor::syncFolder($folder);
        }

		// Lti1.1 chime
        $chimeIds = DB::table('chimes')
		->join("folders", "chimes.id", "=", "folders.chime_id")
		->join("questions", "folders.id", "=", "questions.folder_id")
		->join("sessions", "questions.id","=","sessions.question_id")
        ->join("responses", "sessions.id","=","responses.session_id")
        ->select("chimes.id")
        ->whereNotNull("chimes.resource_link_pk")
        ->where("chimes.lti_grade_mode", LTI13ResourceLink::LTI_GRADE_MODE_ONE_GRADE)
        ->groupBy("responses.session_id")
        ->groupBy("chimes.id")
		->havingRaw("max(`responses`.`created_at`) > ?", [$oldestResponse])
		->havingRaw("max(`responses`.`created_at`) < ?", [$newestResponse])->get()->unique()->pluck("id");
		$chimes = \App\Chime::find($chimeIds);
        
		echo "Syncing LTI 1.1 Chime\n";
		foreach($chimes as $chime) {
			echo "Syncing chime: " . $chime->id . "\n";
            \App\Library\LTIProcessor::syncChime($chime);
        }

		// // Lti1.3 folder sync
        $folderIds = DB::table('folders')
		->join("questions", "folders.id", "=", "questions.folder_id")
		->join("sessions", "questions.id","=","sessions.question_id")
        ->join("responses", "sessions.id","=","responses.session_id")
        ->select("folders.id")
        ->whereNotNull("folders.lti_lineitem")
        ->groupBy("responses.session_id")
        ->groupBy("folders.id")
		->havingRaw("max(`responses`.`created_at`) > ?", [$oldestResponse])
		->havingRaw("max(`responses`.`created_at`) < ?", [$newestResponse])->get()->unique()->pluck("id");
		$folders = \App\Folder::find($folderIds);
        foreach($folders as $folder) {
            \App\Library\LTI13Processor::syncFolder($folder);
        }

		// Lti1.3 chime
        $chimeIds = DB::table('chimes')
		->join("folders", "chimes.id", "=", "folders.chime_id")
		->join("questions", "folders.id", "=", "questions.folder_id")
		->join("sessions", "questions.id","=","sessions.question_id")
        ->join("responses", "sessions.id","=","responses.session_id")
        ->select("chimes.id")
        ->whereNotNull("chimes.lti13_resource_link_id")
        ->where("chimes.lti_grade_mode", LTI13ResourceLink::LTI_GRADE_MODE_ONE_GRADE)
		->groupBy("responses.session_id")
        ->groupBy("chimes.id")
        ->havingRaw("max(`responses`.`created_at`) > ?", [$oldestResponse])
		->havingRaw("max(`responses`.`created_at`) < ?", [$newestResponse])->get()->unique()->pluck("id");
		
		$chimes = \App\Chime::find($chimeIds);
        foreach($chimes as $chime) {
            \App\Library\LTI13Processor::syncChime($chime);
        }
    }

	static function syncFolder(\App\Folder $folder) {
		
		
		if(!$folder->lti_lineitem) {
			return;
		}
			
		$questions = $folder->questions;
		
		$totalQuestions = LTI13Processor::getQuestionsWithResponsesCount($questions);
		
		$globalUsers = [];
		
		$chime = $folder->chime;
		$filterForCorrectAnswers = false;
		if($chime->only_correct_answers_lti) {
			$filterForCorrectAnswers = true;
		}
		
		foreach($questions as $question) {
			LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers, "1.3");
		}
		
        $lineItem = new \Packback\Lti1p3\LtiLineitem(["id"=>$folder->lti_lineitem]);
		
        $ags = LTI13Processor::getAGS($chime);

        foreach($globalUsers as $userId=>$userScore) {
            $score = $userScore["points"] / $totalQuestions;
            $score = \Packback\Lti1p3\LtiGrade::new()
                ->setScoreGiven($score)
                ->setScoreMaximum(1)
                ->setTimestamp($userScore["submission_date"]->toIso8601String())
                ->setActivityProgress('Submitted')
                ->setGradingProgress('FullyGraded')
                ->setUserId($userId);
            $result = $ags->putGrade($score, $lineItem);

		}
		return true;
	}
	
	
	static function syncChime(\App\Chime $chime) {
		
		
		if(!$chime->lti13_resource_link) {
			return;
		}
		
		
		$folders = $chime->folders;
		
		$totalQuestions = 0;
		$globalUsers = [];
				

		foreach($folders as $folder) {
			$questions = $folder->questions;
			
			$totalQuestions += LTI13Processor::getQuestionsWithResponsesCount($questions);
			
			foreach($questions as $question) {
			
				LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers);
			}
		
		}
		
        $ags = LTI13Processor::getAGS($chime);
        
        if($chime->lti13_resource_link->created_line_item) {
            $lineItemId = $chime->lti13_resource_link->created_line_item;
        }
        else if(isset($chime->lti13_resource_link->endpoint["lineitem"])) {
            $lineItemId = $chime->lti13_resource_link->endpoint["lineitem"];
        }

        $lineItem = new \Packback\Lti1p3\LtiLineitem(["id"=>$lineItemId]);
		foreach($globalUsers as $userId=>$userScore) {
            $score = $userScore["points"] / $totalQuestions;
            $score = \Packback\Lti1p3\LtiGrade::new()
                ->setScoreGiven($score)
                ->setScoreMaximum(1)
                ->setTimestamp($userScore["submission_date"]->toIso8601String())
                ->setActivityProgress('Submitted')
                ->setGradingProgress('FullyGraded')
                ->setUserId($userId);
            $result = $ags->putGrade($score, $lineItem);

		}
		return true;
	}
	
	static function getPointsForQuestion($question, $chime, &$globalUsers, $ltiType = "1.1") {
		if($ltiType == "1.1") {
			$userKey = "lti_user_id";
		}
		else {
			$userKey = "lti13_sub_id";
		}

		$filterForCorrectAnswers = false;
		if($chime->only_correct_answers_lti) {
			$filterForCorrectAnswers = true;
		}
		$correctText = null;
		$correctAnswers = null;
		if($filterForCorrectAnswers) {
			$correctAnswers = array_filter($question->question_info["question_responses"], 
				function($k) { 
					if(isset($k["correct"])) { 
						return $k["correct"]==true;
					} 
					return false;
				}
			);
			$correctText = array_map(
				function($k) { 
					return $k["text"];
				}, $correctAnswers
			);
		}
		
		$users = $question->sessions->map(
			function ($session) use($correctText, $chime, $userKey) {
				return $session->responses->map(
					function ($response) use ($correctText, $chime, $userKey) {
						// if this question has "correct" answers, see if the respondent got at least one correct
						// if so pass it back.  
						if($correctText) {
							$choice = [];
							if(isset($response->response_info["choice"])) {
								if(is_array($response->response_info["choice"])) {
									$choice = $response->response_info["choice"];
								}
								else {
									$choice = [$response->response_info["choice"]];
								}
								
							}
							if(count(array_intersect($choice, $correctText)) > 0 && $response->user->{$userKey}) {
								return ["user"=>$response->user, "points"=>1, "submission_date"=>$response->created_at];
							}
							else if($chime->only_correct_answers_lti == 2) { // partial credit
								return ["user"=>$response->user, "points"=>0.5, "submission_date"=>$response->created_at];
							}
							return false;
						}
						else {
							return $response->user->{$userKey}?["user"=>$response->user, "points"=>1, "submission_date"=>$response->created_at]:false;
						}
					}
				);
			}
		)->flatten(1)->unique(
			function ($userCollection) {
				if(isset($userCollection["user"])) {
					return $userCollection["user"]->id;
				}
				else {
					return $userCollection;
				}
			}
		);
		
		foreach($users as $userCollection) {
			$user = $userCollection["user"];
			$points = $userCollection["points"];
			$submission_date = $userCollection["submission_date"];
			if(!isset($user->{$userKey})) {
				continue;
			}
			if(array_key_exists($user->{$userKey}, $globalUsers)) {
				$existingEntry = $globalUsers[$user->{$userKey}];
				$globalUsers[$user->{$userKey}] = ["points"=>$existingEntry["points"] + $points, "submission_date"=>max($submission_date, $existingEntry["submission_date"])];
			}
			else {
				$globalUsers[$user->{$userKey}] = ["points"=>$points, "submission_date"=>$submission_date];
			}
			
		}
	}
	
	static function getQuestionsWithResponsesCount($questions) {
		return $questions->filter(function($q) {
			return $q->sessions->filter(function($s) {
				return $s->responses->count() > 0;
			})->count() > 0;
		})->count();
	}

    static function getAGS($chime) {
        $issuer = $chime->lti13_resource_link->deployment->issuer->host;
        $clientId = $chime->lti13_resource_link->deployment->issuer->client_id;
        $db = new \App\Library\LTI13Database();
        $registration = $db->findRegistrationByIssuer($issuer, $clientId);
        $endpoint = $chime->lti13_resource_link->endpoint;
        $ags = new \Packback\Lti1p3\LtiAssignmentsGradesService(
            new \Packback\Lti1p3\LtiServiceConnector($registration),
            $endpoint);
        return $ags;
    }

}

?>