<?php

use Phalcon\Mvc\Model;

class Kills extends Model {
	
	static public function getLastKills() {
		return self::query()
			->order('committed DESC')
			->limit(40)
			->execute();
	}
	
}