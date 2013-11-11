<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;

class Involved extends Model {
	
	/**
	 * Get the number of involved parties by the kills ids
	 * 
	 * @return array [killId => involvedNumber]
	 */
	static public function getInvolvedByKillsIds(array $ids) {
		$result = (new Builder)
			->from('Involved')
			->columns(['kill_id', 'count' => 'count(*)'])
			->where('kill_id in(' . implode(',', $ids) . ')')
			->groupBy('kill_id')
			->getQuery()
			->execute();

		$data = [];
		
		foreach ($result as $row) {
			$data[(int) $row->kill_id] = (int) $row->count;
		}
		
		return $data;
	}
	
}