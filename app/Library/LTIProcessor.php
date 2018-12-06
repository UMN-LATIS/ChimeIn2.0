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
		$consumer = ToolProvider\ToolConsumer::fromRecordId(3, $tool->data_connector);
		$resource_link = ToolProvider\ResourceLink::fromRecordId($folder->resource_link_pk, $tool->data_connector);

		$ltiUsers = $resource_link->getUserResultSourcedIDs();

		$questions = $folder->questions;

		$totalQuestions = $questions->count();

		$globalUsers = [];

		foreach($questions as $question) {

			$users = $question->sessions->map(function ($session) {
				return $session->responses->map(function ($response) {
					return $response->user->lti_user_id?$response->user:false;
				});
			})->flatten()->unique();

			foreach($users as $user ) {

				if(array_key_exists($user->lti_user_id, $globalUsers)) {
					$globalUsers[$user->lti_user_id]++;
				}
				else {
					$globalUsers[$user->lti_user_id] = 1;
				}

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


	}


}

?>