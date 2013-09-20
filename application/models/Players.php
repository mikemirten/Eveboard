<?php

use Phalcon\Mvc\Model;

class Players extends Model {
	
	static public function findByName($name) {
		return self::query()
			->where('name = :name:')
			->bind(array('name' => $name))
			->execute()
			->getFirst();
	}
	
}