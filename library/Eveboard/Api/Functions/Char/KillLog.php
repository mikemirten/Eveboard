<?php
namespace Eveboard\Api\Functions\Char;

use Eveboard\Api\Functions\FunctionAbstract;

class KillLog extends FunctionAbstract {
	
	protected function getParamsDefinition() {
		return [
			'characterID'  => true,
			'BeforeKillID' => false
		];
	}
	
	protected function processData(\SimpleXMLElement $data) {
		;
	}
	
}