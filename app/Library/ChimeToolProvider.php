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
		abort(500, "There was an error launching ChimeIn from Canvas. Usually this is due to an incorrect key/secret being entered when adding ChimeIn to a Canvas Course. Please double check the <a href='https://umn-latis.github.io/ChimeIn2.0/canvas.html'>setup instructions</a> or <a href='mailto:help@umn.edu'>contact us</a> for assistance. Please note that each time you add the integration, Canvas adds a new item to the 'external tools' list, so it might be helpful to change the name to something like 'ChimeIn Fixed' to differentiate.");

	}


}

?>