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