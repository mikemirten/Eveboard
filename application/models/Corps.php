<?php

use Phalcon\Mvc\Model;

class Corps extends Model {
	
	static public function getCorpById($id) {
		return self::find(['corp_id in(' . implode(',', (array) $id) . ')']);
	}
	
}