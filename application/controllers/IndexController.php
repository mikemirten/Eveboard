<?php
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Resultset;

class IndexController extends Controller {
	
	public function indexAction() {
		$kills = Kills::getLastKills()->toArray();
		
		// Gathering ids
		$playersIds   = [];
		$corpsIds     = [];
		$alliancesIds = [];
		
		foreach ($kills as &$kill) {
			$kill = (object) $kill;
			
			$playersIds[]   = $kill->character_id;
			$corpsIds[]     = $kill->corp_id;
			$alliancesIds[] = $kill->alliance_id;
		}
		unset($kill);
		
		// Players' data
		$playersRaw = Players::getPlayerById(array_unique($playersIds));
		$playersRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
		unset($playersIds);
		
		$players = [];
		
		foreach ($playersRaw as $player) {
			$players[$player->player_id] = $player;
		}
		unset($playersRaw);
		
		// Corporations' data
		$corpsRaw = Corps::getCorpById(array_unique($corpsIds));
		$corpsRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
		unset($corpsIds);
		
		$corps = [];
		
		foreach ($corpsRaw as $corp) {
			$corps[$corp->corp_id] = $corp;
		}
		unset($corpsRaw);
		
		// Alliances' data
		$alliancesRaw = Alliances::getPAlianceById(array_unique($alliancesIds));
		$alliancesRaw->setHydrateMode(Resultset::HYDRATE_OBJECTS);
		unset($alliancesIds);
		
		$alliances = [];
		
		foreach ($alliancesRaw as $ally) {
			$alliances[$ally->alliance_id] = $ally;
		}
		unset($alliancesRaw);
		
		// Gathered data into kills
		foreach ($kills as $kill) {
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
		}
		
		$this->view->kills = $kills;
	}
	
}