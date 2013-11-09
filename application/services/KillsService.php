<?php

use Phalcon\Mvc\Model\Transaction\Manager;

class KillsService {
	
	public function importKills() {
		$client = new Eveboard\Api\ClientTest();
		$client->setResponseXml(file_get_contents(TMP_PATH . DIRECTORY_SEPARATOR . 'KillLog.xml'));
		
		$kills = $client->corp->killLog();
		
		if (empty($kills)) {
			return;
		}
		
		// Resolve existing data for players. corporations and alliances
		$playersIds   = [];
		$corpsIds     = [];
		$alliancesIds = [];
		
		foreach ($kills as $kill) {
			if (! isset($playersIds[$kill->characterID])) {
				$playersIds[$kill->characterID] = true;
			}
			
			if (! isset($corpsIds[$kill->corporationID])) {
				$corpsIds[$kill->corporationID] = true;
			}
			
			if (! isset($alliancesIds[$kill->allianceID])) {
				$alliancesIds[$kill->allianceID] = true;
			}
			
			foreach ($kill->parts as $part) {
				if (! isset($playersIds[$part->characterID])) {
					$playersIds[$part->characterID] = true;
				}
				
				if (! isset($corpsIds[$part->corporationID])) {
					$corpsIds[$part->corporationID] = true;
				}
				
				if (! isset($alliancesIds[$part->allianceID])) {
					$alliancesIds[$part->allianceID] = true;
				}
			}
		}
		unset($playersIds[0], $corpsIds[0], $alliancesIds[0], $kill);
		
		// Players data
		$existedPlayersIds = [];
		
		foreach (Players::getPlayerById(array_keys($playersIds)) as $player) {
			$existedPlayersIds[(int) $player->player_id] = true;
		}
		unset($playersIds, $player);
		
		// Corporations data
		$existedCorpsIds = [];
		
		foreach (Corps::getCorpById(array_keys($corpsIds)) as $corp) {
			$existedCorpsIds[(int) $corp->corp_id] = true;
		}
		unset($playersIds, $corp);
		
		// Alliances data
		$existedAlliancesIds = [];
		
		foreach (Alliances::getPAlianceById(array_keys($alliancesIds)) as $ally) {
			$existedAlliancesIds[(int) $ally->alliance_id] = true;
		}
		unset($playersIds, $ally);
		
		$transactionManager = new Manager();
		$transaction        = $transactionManager->get();
		
		foreach ($kills as $killData) {
			// Save kill
			$kill = new Kills();
			$kill->setTransaction($transaction);
			
			$kill->kill_id      = $killData->killID;
			$kill->character_id = $killData->characterID;
			$kill->corp_id      = $killData->corporationID;
			$kill->alliance_id  = $killData->allianceID;
			$kill->faction_id   = $killData->factionID;
			$kill->item_id      = $killData->shipTypeID;
			$kill->system_id    = $killData->solarSystemID;
			$kill->moon_id      = $killData->moonID;
			$kill->committed    = $killData->killTime;
			$kill->damage_taken = $killData->damageTaken;
			
			if (! $kill->save()) {
				$transaction->rollback();
				throw new RuntimeException(implode(' ;', $kill->getMessages()));
			}
			
			// Save involved parties
			foreach ($killData->parts as $partData) {
				// Save alliance's data
				if ($partData->allianceID !== 0
				&& ! isset($existedAlliancesIds[$partData->allianceID])) {
					$existedAlliancesIds[$partData->allianceID] = true;
					
					$ally = new Alliances();
					$ally->setTransaction($transaction);

					$ally->alliance_id = $partData->allianceID;
					$ally->title       = $partData->allianceName;

					if (! $ally->save()) {
						$transaction->rollback();
						throw new RuntimeException(implode(' ;', $ally->getMessages()));
					}
				}
				
				// Save corporation's data
				if ($partData->corporationID !== 0
				&& ! isset($existedCorpsIds[$partData->corporationID])) {
					$existedCorpsIds[$partData->corporationID] = true;
					
					$corp = new Corps();
					$corp->setTransaction($transaction);

					$corp->corp_id     = $partData->corporationID;
					$corp->title       = $partData->corporationName;
					$corp->alliance_id = $partData->allianceID;

					if (! $corp->save()) {
						$transaction->rollback();
						throw new RuntimeException(implode(' ;', $corp->getMessages()));
					}
				}
				
				// Save player's data
				if ($partData->characterID !== 0
				&& ! isset($existedPlayersIds[$partData->characterID])) {
					$existedPlayersIds[$partData->characterID] = true;
					
					$player = new Players();
					$player->setTransaction($transaction);

					$player->player_id = $partData->characterID;
					$player->name      = $partData->characterName;
					$player->corp_id   = $partData->corporationID;

					if (! $player->save()) {
						$transaction->rollback();
						throw new RuntimeException(implode(' ;', $player->getMessages()));
					}
				}
				
				// Save involved data
				$involved = new Involved();
				$involved->setTransaction($transaction);
				
				$involved->kill_id      = $killData->killID;
				$involved->character_id = $partData->characterID;
				$involved->corp_id      = $partData->corporationID;
				$involved->alliance_id  = $partData->allianceID;
				$involved->faction_id   = $partData->factionID;
				$involved->ship_id      = $partData->shipTypeID;
				$involved->weapon_id    = $partData->weaponTypeID;
				$involved->damage_done  = $partData->damageDone;
				$involved->final_blow   = $partData->finalBlow;
				
				if (! $involved->save()) {
					$transaction->rollback();
					throw new RuntimeException(implode(' ;', $involved->getMessages()));
				}
			}
		}
		
		$transaction->commit();
	}
	
}