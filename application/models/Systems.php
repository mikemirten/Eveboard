<?php

use Phalcon\Mvc\Model;

class Systems extends Model {
	
	static public function findByTitle($title) {
		return self::query()
			->where('title = :title:')
			->bind(array('title' => $title))
			->execute()
			->getFirst();
	}
	
}