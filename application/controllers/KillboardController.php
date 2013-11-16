<?php
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Resultset;

class KillboardController extends Controller {
	
	public function indexAction() {
		$this->dispatcher->forward(['action' => 'kills']);
	}
	
	public function killsAction() {
		$this->view->pick('killboard/kills');
		
		$this->killboard();
	}
	
	public function lossesAction() {
		$this->view->pick('killboard/kills');
		
		$this->killboard(true);
	}
	
	public function corpsAction() {
		$this->view->pick('killboard/kills');
		
		$this->killboard();
	}
	
	public function membersAction() {
		$this->view->pick('killboard/kills');
		
		$this->killboard();
	}
	
	protected function killboard() {
		$homeAllyId = $this->config->alliance->id;
		
		// Assemble query
		$allyId = $this->dispatcher->getParam('alliance', 'int');
		$corpId = $this->dispatcher->getParam('corp', 'int');
		$itemId = $this->dispatcher->getParam('item', 'int');
		$charId = $this->dispatcher->getParam('char', 'int');
		$params = [];
		
		if ($allyId !== null) {
			$params['alliance'] = (int) $allyId;
		} else {
			$params['alliance'] = - $homeAllyId;
		}
		
		if ($corpId !== null) {
			$params['corp'] = (int) $corpId;
		}
		
		if ($itemId !== null) {
			$params['item'] = (int) $itemId;
		}
		
		if ($charId !== null) {
			$params['char'] = (int) $charId;
		}
		
		$page = (int) $this->dispatcher->getParam('page', 'int', 1);
		Kills::setPerpage($this->config->killboard->killsPerPage);
		
		$paginator = Kills::getLastKills($params, $page);
		$kills     = $paginator->items->toArray();
		
		unset($paginator->items);
		
		// Gathering ids
		$killIds      = [];
		$playersIds   = [];
		$corpsIds     = [];
		$alliancesIds = [];
		$itemsIds     = [];
		
		foreach ($kills as &$kill) {
			$kill = (object) $kill;
			
			$killIds[]      = $kill->kill_id;
			$playersIds[]   = $kill->character_id;
			$corpsIds[]     = $kill->corp_id;
			$alliancesIds[] = $kill->alliance_id;
			$itemsIds[]     = $kill->item_id;
		}
		unset($kill);
		
		// Involved count
		if (! empty($killIds)) {
			$involvedNum = Involved::getInvolvedByKillsIds($killIds);
		}
		
		// Players' data
		if (! empty($playersIds)) {
			$playersRaw = Players::getPlayerById(array_unique($playersIds));
			$playersRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
			unset($playersIds);
		
			$players = [];

			foreach ($playersRaw as $player) {
				$players[$player->player_id] = $player;
			}
			unset($playersRaw);
		}
		
		// Corporations' data
		if (! empty($corpsIds)) {
			$corpsRaw = Corps::getCorpById(array_unique($corpsIds));
			$corpsRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
			unset($corpsIds);

			$corps = [];

			foreach ($corpsRaw as $corp) {
				$corps[$corp->corp_id] = $corp;
			}
			unset($corpsRaw);
		}
		
		// Alliances' data
		if (! empty($alliancesIds)) {
			$alliancesRaw = Alliances::getPAlianceById(array_unique($alliancesIds));
			$alliancesRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
			unset($alliancesIds);

			$alliances = [];

			foreach ($alliancesRaw as $ally) {
				$alliances[$ally->alliance_id] = $ally;
			}
			unset($alliancesRaw);
		}
		
		// Items' data
		if (! empty($itemsIds)) {
			$itemsRaw = Items::getItemsById(array_unique($itemsIds));
			$itemsRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
			unset($itemsIds);

			$items = [];

			foreach ($itemsRaw as $item) {
				$items[$item->item_id] = $item;
			}
			unset($itemsRaw);
		}
		
		// Gathered data into kills
		foreach ($kills as $kill) {
			// Involved number
			if (isset($involvedNum[$kill->kill_id])) {
				$kill->involved_number = $involvedNum[$kill->kill_id];
			} else {
				$kill->involved_number = 0;
			}
			// Players
			if (isset($players[$kill->character_id])) {
				$kill->character_name = $players[$kill->character_id]->name;
			} else {
				$kill->character_name = 'unknown';
			}
			// Corporations
			if (isset($corps[$kill->corp_id])) {
				$kill->corp_title = $corps[$kill->corp_id]->title;
			} else {
				$kill->corp_title = 'unknown';
			}
			// Alliances
			if (isset($alliances[$kill->alliance_id])) {
				$kill->alliance_title = $alliances[$kill->alliance_id]->title;
			} else {
				$kill->alliance_title = 'unknown';
			}
			// Items
			if (isset($items[$kill->item_id])) {
				$kill->item_title = $items[$kill->item_id]->title;
			} else {
				$kill->item_title = 'unknown';
			}
		}
		
		if (isset($params['alliance'])
		&&  $params['alliance'] === - $homeAllyId) {
			unset($params['alliance']);
		}
		
		$this->view->kills       = $kills;
		$this->view->pagination  = $paginator;
		$this->view->queryParams = $params;
		
		// Pagination base URL
		$dispatcher = $this->getDi()->get('dispatcher');
		$action     = $dispatcher->getActionName();
		
		$paginationUrl = 'killboard/' . $action . '/';
		
		foreach ($params as $param => $value) {
			$paginationUrl .= $param . '/' . $value . '/';
		}
		
		$this->view->paginationUrl = $this->url->get($paginationUrl . 'page/');
	}
	
}