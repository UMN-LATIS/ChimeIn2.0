<?php


namespace App\Library;
use Illuminate\Support\Facades\Log;
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
		
		$totalQuestions = LTI13Processor::getQuestionsWithResponsesCount($questions);
		
		$globalUsers = [];
		
		$chime = $folder->chime;

		foreach($questions as $question) {
			LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers, "1.1");
		}

		
		foreach($ltiUsers as $user) {
			if($user->ltiUserId) {
				$score = 0;
				if(array_key_exists($user->ltiUserId, $globalUsers)) {
					$score = $globalUsers[$user->ltiUserId] / $totalQuestions;
				}
				$lti_outcome = new ToolProvider\Outcome($score, null);
				if(!$resource_link->doOutcomesService(ToolProvider\ResourceLink::EXT_WRITE, $lti_outcome, $user)) {
					Log::error("Error synchronizing LTI Outcome", ["user"=>$user->ltiUserId, "score"=>$score, "resource"=>$folder->resource_link_pk, "request"=>$resource_link->extRequest]);
				}
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
			
			$totalQuestions += LTI13Processor::getQuestionsWithResponsesCount($questions);
			
			foreach($questions as $question) {
				LTI13Processor::getPointsForQuestion($question, $chime, $globalUsers, "1.1");
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
	

}

?>