<?php


namespace App\Library;

use DB;

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;

class LTIProcessor {
	
	function __construct() {
		
	}
	
	static function syncFolder(\App\Folder $folder) {
		
		
		if(!$folder->resource_link_pk) {
			return;
		}
		
		$tool = new ChimeToolProvider();
		
		$resource_link = ToolProvider\ResourceLink::fromRecordId($folder->resource_link_pk, $tool->data_connector);
		
		$ltiUsers = $resource_link->getUserResultSourcedIDs();
		
		$questions = $folder->questions;
		
		$totalQuestions = $questions->count();
		
		$globalUsers = [];
		
		$chime = $folder->chime;
		$filterForCorrectAnswers = false;
		if($chime->only_correct_answers_lti) {
			$filterForCorrectAnswers = true;
		}
		
		foreach($questions as $question) {
			LTIProcessor::getPointsForQuestion($question, $chime, $globalUsers);
		}
		
		foreach($ltiUsers as $user) {
			if($user->ltiUserId) {
				$score = 0;
				if(array_key_exists($user->ltiUserId, $globalUsers)) {
					$score = $globalUsers[$user->ltiUserId] / $totalQuestions;
				}
				$lti_outcome = new ToolProvider\Outcome($score, null);
				$resource_link->doOutcomesService(ToolProvider\ResourceLink::EXT_WRITE, $lti_outcome, $user);
			}
		}
		
		return true;
	}
	
	
	static function syncChime(\App\Chime $chime) {
		
		
		if(!$chime->resource_link_pk) {
			return;
		}
		
		$tool = new ChimeToolProvider();
		
		$resource_link = ToolProvider\ResourceLink::fromRecordId($chime->resource_link_pk, $tool->data_connector);
		
		$ltiUsers = $resource_link->getUserResultSourcedIDs();
		
		$folders = $chime->folders;
		
		$totalQuestions = 0;
		$globalUsers = [];
				

		foreach($folders as $folder) {
			$questions = $folder->questions;
			
			$totalQuestions += $questions->count();
			
			foreach($questions as $question) {
			
				LTIProcessor::getPointsForQuestion($question, $chime, $globalUsers);
			}
		
		}
		
		foreach($ltiUsers as $user) {
			if($user->ltiUserId) {
				$score = 0;
				if(array_key_exists($user->ltiUserId, $globalUsers)) {
					$score = $globalUsers[$user->ltiUserId] / $totalQuestions;
				}
				
				$lti_outcome = new ToolProvider\Outcome($score, null);
				$resource_link->doOutcomesService(ToolProvider\ResourceLink::EXT_WRITE, $lti_outcome, $user);
			}
		}
		return true;
	}
	
	static function getPointsForQuestion($question, $chime, &$globalUsers) {
		$filterForCorrectAnswers = false;
		if($chime->only_correct_answers_lti) {
			$filterForCorrectAnswers = true;
		}
		$correctText = null;
		$correctAnswers = null;
		if($filterForCorrectAnswers) {
			$correctAnswers = array_filter($question->question_info["question_responses"], function($k) { if(isset($k["correct"])) { return $k["correct"]==true;} return false;});
			$correctText = array_map(function($k) { return $k["text"];}, $correctAnswers);
		}
		
		$users = $question->sessions->map(function ($session) use($correctText, $chime) {
			return $session->responses->map(function ($response) use ($correctText, $chime) {
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
					if(count(array_intersect($choice, $correctText)) > 0 && $response->user->lti_user_id) {
						return ["user"=>$response->user, "points"=>1];
					}
					else if($chime->only_correct_answers_lti == 2) { // partial credit
						return ["user"=>$response->user, "points"=>0.5];
					}
					return false;
				}
				else {
					return $response->user->lti_user_id?["user"=>$response->user, "points"=>1]:false;
				}
				
			});
		})->flatten(1)->unique(function ($userCollection) {
			if(isset($userCollection["user"])) {
				return $userCollection["user"]->id;
			}
			else {
				return $userCollection;
			}
		});
		
		foreach($users as $userCollection) {
			$user = $userCollection["user"];
			$points = $userCollection["points"];
			if(!isset($user->lti_user_id)) {
				continue;
			}
			if(array_key_exists($user->lti_user_id, $globalUsers)) {
				$globalUsers[$user->lti_user_id] += $points;
			}
			else {
				$globalUsers[$user->lti_user_id] = $points;
			}
			
		}
	}

}

?>