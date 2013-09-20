<?php

use Phalcon\Mvc\Model;

class Corps extends Model {
	
	static public function findByTitle($title) {
		return self::query()
			->where('title = :title:')
			->bind(array('title' => $title))
			->execute()
			->getFirst();
	}
	
}