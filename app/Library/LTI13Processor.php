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
        echo "Syncing LTI 1.3 folder\n";
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
		echo "Syncing LTI 1.3 Chime\n";
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
		
		foreach($questions as $question) {
			$globalUsers = LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers, "1.3");
		}
		
        $lineItem = new \Packback\Lti1p3\LtiLineitem(["id"=>$folder->lti_lineitem]);
		
        $ags = LTI13Processor::getAGS($chime);

        foreach($globalUsers as $userId=>$userScore) {
            $score = $userScore["points"] / $totalQuestions;
			// TODO: test the date stuff
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
			
				$globalUsers = LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers, "1.3");
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
			// TODO: test the date stuff
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
	
	static function getPointsForQuestion($question, $chime, $globalUsers, $ltiType = "1.1") {
		if($ltiType == "1.1") {
			$userKey = "lti_user_id";
		}
		else {
			$userKey = "lti13_sub_id";
		}

		$correctText = null;
		if($chime->only_correct_answers_lti) {
			$correctText = LTI13Processor::getCorrectTextForQuestion($question);
		}
		
		$userScores = LTI13Processor::getUserScoresForQuestion($question, $correctText, $chime, $userKey);
		
		foreach($userScores as $userCollection) {
			$user = $userCollection["user"];
			$points = $userCollection["points"];
			$submission_date = $userCollection["submission_date"];
			
			// no LTI id for this user, skip them. This shouldn't be possible
			if(!isset($user->{$userKey})) {
				continue;
			}
			
			if(array_key_exists($user->{$userKey}, $globalUsers)) {
				$existingEntry = $globalUsers[$user->{$userKey}];
				$globalUsers[$user->{$userKey}] = [
					"points"=>$existingEntry["points"] + $points, 
					"submission_date"=>max($submission_date, $existingEntry["submission_date"])
				];
			}
			else {
				$globalUsers[$user->{$userKey}] = [
					"points"=>$points, 
					"submission_date"=>$submission_date
				];
			}
			
		}
		return $globalUsers;
	}
	
	static function getQuestionsWithResponsesCount($questions) {
		return $questions->filter(function($q) {
			return $q->sessions->filter(function($s) {
				return $s->responses->count() > 0;
			})->count() > 0;
		})->count();
	}

	static function getCorrectTextForQuestion(\App\Question $question) : array {
		$correctAnswers = collect($question->question_info["question_responses"])
			->filter(
				function($response) { 
					if(isset($response["correct"])) { 
						return $response["correct"]==true;
					} 
					return false;
				}
			);
		
		$correctText = collect($correctAnswers)
			->map(
				function($response) { 
					return $response["text"];
				}
			);
		
		return $correctText->toArray();

	}

	static function getUserScoresForQuestion($question, $correctText, $chime, $userKey) {
		
		$questionResults = [];
		foreach($question->sessions as $session) {
			$questionResults[$session->id] = [];
			
			foreach($session->responses as $response) {
				// if we don't have an LTI entry for this user, we can't do anything. Skip it.
				if(!$response->user->{$userKey}) {
					continue;
				}

				if($correctText) {
					$userChoice = LTI13Processor::getUserChoiceForResponse($response);

					if(count(array_intersect($userChoice, $correctText)) > 0) {
						$questionResults[$session->id][] = [
							"user"=>$response->user, 
							"points"=>1, 
							"submission_date"=>$response->created_at
						];
					}
					else if($chime->only_correct_answers_lti == 2) { // partial credit
						$questionResults[$session->id][] = [
							"user"=>$response->user, 
							"points"=>0.5, 
							"submission_date"=>$response->created_at
						];
					}
				}
				else {
					$questionResults[$session->id][] = [
						"user"=>$response->user, 
						"points"=>1, 
						"submission_date"=>$response->created_at
					];
				}
			}


		}
		
		$userScores = collect($questionResults)->flatten(1)->reduce(function($carry, $item) {
			$user = $item["user"];
			$submission_date = $item["submission_date"];
			$points = $item["points"];
			
			if(!isset($carry[$user->id])) {
				$carry[$user->id] = [
					"user"=>$user, 
					"points"=>0, 
					"submission_date"=>$submission_date
				];
			}
			// make sure we grant student the max possible results
			if($points > $carry[$user->id]["points"]) {
				$carry[$user->id]["points"] = $points;
				$carry[$user->id]["submission_date"] = $submission_date;
			}
			return $carry;
		}, []);
		
		return $userScores;
	}

	static function getUserChoiceForResponse($response) {
		$choice = [];
		if(isset($response->response_info["choice"])) {
			if(is_array($response->response_info["choice"])) {
				$choice = $response->response_info["choice"];
			}
			else {
				$choice = [$response->response_info["choice"]];
			}
		}
		return $choice;
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