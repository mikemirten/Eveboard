<?php
namespace Eveboard\Api\Functions\Eve;

use Eveboard\Api\Functions\FunctionAbstract;
use SimpleXMLElement;

class TypeName extends FunctionAbstract {
	
	protected function getParamsDefinition() {
		return ['ids' => true];
	}
	
	protected function processData(SimpleXMLElement $data) {
		$items = [];
		
		foreach ($data->rowset->row as $item) {
			$attrs = $item->attributes();
			$items[(int) $attrs->typeID] = (string) $attrs->typeName;
		}
		
		return $items;
	}
	
}