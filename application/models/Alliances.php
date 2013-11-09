<?php

use Phalcon\Mvc\Model;

class Alliances extends Model {
	
	static public function getPAlianceById($id) {
		return self::find(['alliance_id in(' . implode(',', (array) $id) . ')']);
	}
	
}