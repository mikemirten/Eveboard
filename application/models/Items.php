<?php

use Phalcon\Mvc\Model;

class Items extends Model {
	
	static public function getItemsById($id) {
		return self::find(['item_id in(' . implode(',', (array) $id) . ')']);
	}
	
}