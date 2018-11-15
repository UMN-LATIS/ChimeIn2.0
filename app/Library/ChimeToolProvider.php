<?php


namespace App\Library;

use DB;

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;

class ChimeToolProvider extends ToolProvider\ToolProvider {

	function __construct() {
		$db = \DB::connection()->getPdo();
        $this->data_connector = DataConnector\DataConnector::getDataConnector("", $db, 'pdo');
        parent::__construct($this->data_connector);
	}

	function onLaunch() {
	}

	function onError() {
		echo $this->reason;
		var_dump($this->details);
	}


}

?>