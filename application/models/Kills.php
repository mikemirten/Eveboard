<?php

use Phalcon\Mvc\Model;

class Kills extends Model {
	
	static public function getLastKills($allianceId = null) {
		$query = self::query()
			->order('committed DESC')
			->limit(40);
		
		if ($allianceId !== null) {
			$query->where('alliance_id != ' . $allianceId);
		}
		
		return $query->execute();
	}
	
}