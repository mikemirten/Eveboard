<?php

use Phalcon\Mvc\Model;

class Kills extends Model {
	
	static public function findByHash($hash) {
		return self::findFirst(array('kill_hash' => $hash));
	}
	
}