<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;

class Kills extends Model {
	
	static protected $perpage = 40;
	
	static public function setPerpage($perpage) {
		self::$perpage = (int) $perpage;
	}
	
	static public function getLastKills($params = [], $page = 1) {
		$builder = (new Builder())
			->from('Kills')
			->orderBy('committed DESC');
		
		if (isset($params['alliance'])) {
			if ($params['alliance'] > 0) {
				$builder->andWhere('alliance_id = ' . $params['alliance']);
			} else {
				$builder->andWhere('alliance_id != ' . abs($params['alliance']));
			}
		}
		
		if (isset($params['corp'])) {
			if ($params['corp'] > 0) {
				$builder->andWhere('corp_id = ' . $params['corp']);
			} else {
				$builder->andWhere('corp_id != ' . abs($params['corp']));
			}
		}
		
		if (isset($params['item'])) {
			if ($params['item'] > 0) {
				$builder->andWhere('item_id = ' . $params['item']);
			} else {
				$builder->andWhere('item_id != ' . abs($params['item']));
			}
		}
		
		if (isset($params['char'])) {
			if ($params['char'] > 0) {
				$builder->andWhere('character_id = ' . $params['char']);
			} else {
				$builder->andWhere('character_id != ' . abs($params['char']));
			}
		}
		
		return (new Paginator([
			'builder' => $builder,
			'limit'   => self::$perpage,
			'page'    => $page
		]))->getPaginate();
	}
	
	static public function getLastKillId() {
		$row = self::query()
			->columns(['id' => 'MAX(kill_id)'])
			->execute()
			->getFirst();
		
		if ($row->id === null) {
			return;
		}
		
		return (int) $row->id;
	}
	
}