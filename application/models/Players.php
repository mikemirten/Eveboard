<?php

use Phalcon\Mvc\Model;

class Players extends Model {
	
	static public function getPlayerById($id) {
		return self::find(['player_id in(' . implode(',', (array) $id) . ')']);
	}
	
}