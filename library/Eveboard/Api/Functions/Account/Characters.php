<?php
namespace Eveboard\Api\Functions\Account;

use SimpleXMLElement, stdClass;

use Eveboard\Api\Functions\FunctionAbstract;

class Characters extends FunctionAbstract {

	protected function processData(SimpleXMLElement $data) {
		$chars = [];
		
		foreach ($data->rowset->row as $char) {
			$charData  = $char->attributes();
			$charModel = new stdClass();
			
			$charModel->name            = (string) $charData->name;
			$charModel->characterID     = (int)    $charData->characterID;
			$charModel->corporationName = (string) $charData->corporationName;
			$charModel->corporationID   = (int)    $charData->corporationID;
			
			$chars[] = $charModel;
		}
		
		return $chars;
	}
	
}