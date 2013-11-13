<?php

use Phalcon\Mvc\Model\Transaction\Manager;
use Eveboard\Api\Exceptions\ApiError;
use Eveboard\Api\Client;

class KillsService {
	
	/**
	 * Eve API client
	 *
	 * @var Client 
	 */
	protected $client;
	
	protected $transaction;
	
	protected $alliancesIds, $corpsIds, $playersIds;
	
	public function __construct(Client $client) {
		$this->client = $client;
	}

	public function importKills() {
		$lastKillId = Kills::getLastKillId();
		
		try {
			if ($lastKillId === null) {
				$kills = $this->client->corp->killLog();
			} else {
				$kills = $this->client->corp->killLog($lastKillId);
			}
		} catch (ApiError $exception) {
			// Kill log exhausted
			if ($exception->getCode() === 119) {
				return;
			}
			
			throw $exception;
		}
		
		if (empty($kills)) {
			return;
		}
		
		// Resolve existing data for players. corporations and alliances
		$playersIds   = [];
		$corpsIds     = [];
		$alliancesIds = [];
		$itemsIds     = [];
		
		foreach ($kills as $kill) {
			// Not a fresh kill
			if ($kill->killID <= $lastKillId) {
				return;
			}
			
			if (! isset($playersIds[$kill->characterID])) {
				$playersIds[$kill->characterID] = true;
			}
			
			if (! isset($corpsIds[$kill->corporationID])) {
				$corpsIds[$kill->corporationID] = true;
			}
			
			if (! isset($alliancesIds[$kill->allianceID])) {
				$alliancesIds[$kill->allianceID] = true;
			}
			
			if (! isset($itemsIds[$kill->shipTypeID])) {
				$itemsIds[$kill->shipTypeID] = true;
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
				
				if (! isset($itemsIds[$part->shipTypeID])) {
					$itemsIds[$part->shipTypeID] = true;
				}
			}
			
			foreach ($kill->items as $item) {
				if (! isset($itemsIds[$item->typeID])) {
					$itemsIds[$item->typeID] = true;
				}
			}
		}
		unset($playersIds[0], $corpsIds[0], $alliancesIds[0], $itemsIds[0], $kill);
		
		// Players data
		$this->playersIds = [];
		
		foreach (Players::getPlayerById(array_keys($playersIds)) as $player) {
			$this->playersIds[(int) $player->player_id] = true;
		}
		unset($playersIds, $player);
		
		// Corporations data
		$this->corpsIds = [];
		
		foreach (Corps::getCorpById(array_keys($corpsIds)) as $corp) {
			$this->corpsIds[(int) $corp->corp_id] = true;
		}
		unset($playersIds, $corp);
		
		// Alliances data
		$this->alliancesIds = [];
		
		foreach (Alliances::getPAlianceById(array_keys($alliancesIds)) as $ally) {
			$this->alliancesIds[(int) $ally->alliance_id] = true;
		}
		unset($playersIds, $ally);
		
		// ItemsData
		$existsItemsIds = [];
		
		foreach (Items::getItemsById(array_keys($itemsIds)) as $item) {
			$existsItemsIds[(int) $item->item_id] = true;
		}
		
		$needItemsIds = array_keys(array_diff_key($itemsIds, $existsItemsIds));
		unset($existsItemsIds, $itemsIds, $item);
		
		$transactionManager = new Manager();
		$this->transaction  = $transactionManager->get();
		
		foreach ($kills as $killData) {
			$this->saveAllianceData($killData->allianceID, $killData->allianceName);
			$this->saveCorpData($killData->corporationID, $killData->allianceID, $killData->corporationName);
			$this->savePlayerData($killData->characterID, $killData->corporationID, $killData->characterName);
			
			// Save kill
			$kill = new Kills();
			$kill->setTransaction($this->transaction);
			
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
				$this->transaction->rollback(implode(' ;', $kill->getMessages()));
			}
			
			// Save involved parties
			foreach ($killData->parts as $partData) {
				$this->saveAllianceData($partData->allianceID, $partData->allianceName);
				$this->saveCorpData($partData->corporationID, $partData->allianceID, $partData->corporationName);
				$this->savePlayerData($partData->characterID, $partData->corporationID, $partData->characterName);
				
				// Save involved data
				$involved = new Involved();
				$involved->setTransaction($this->transaction);
				
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
					$this->transaction->rollback(implode(' ;', $involved->getMessages()));
				}
			}
			
			// Lost items (destroyed, dropped)
			foreach ($killData->items as $itemData) {
				$lost = new LostItems();
				$involved->setTransaction($this->transaction);
				
				$lost->kill_id       = $killData->killID;
				$lost->item_id       = $itemData->typeID;
				$lost->flag          = $itemData->flag;
				$lost->qty_destroyed = $itemData->qtyDestroyed;
				$lost->qty_dropped   = $itemData->qtyDropped;
				
				if (! $lost->save()) {
					$this->transaction->rollback(implode(' ;', $lost->getMessages()));
				}
			}
		}
		
		// Items data from API
		if (! empty($needItemsIds)) {
			$this->getAndSaveItemsData($existsItemsIds);
		}
		
		$this->transaction->commit();
	}
	
	protected function getAndSaveItemsData(array $needItemsIds) {
		$chunks = array_chunk($needItemsIds, 100);
			
		foreach ($chunks as $chunk) {
			$items = $this->client->Eve->TypeName(implode(',', $chunk));

			foreach ($items as $itemId => $itemTitle) {
				$item = new Items();
				$item->setTransaction($this->transaction);

				$item->item_id = $itemId;
				$item->title   = $itemTitle;

				if (! $item->save()) {
					$this->transaction->rollback(implode(' ;', $item->getMessages()));
				}
			}
		}
	}
	
	protected function saveAllianceData($allyId, $allyTitle) {
		if ($allyId === 0 || isset($this->alliancesIds[$allyId])) {
			return;
		}
		
		$this->alliancesIds[$allyId] = true;

		$ally = new Alliances();
		$ally->setTransaction($this->transaction);

		$ally->alliance_id = $allyId;
		$ally->title       = $allyTitle;

		if (! $ally->save()) {
			$this->transaction->rollback(implode(' ;', $ally->getMessages()));
		}
	}
	
	protected function saveCorpData($corpId, $allyId, $corpTitle) {
		if ($corpId === 0 || isset($this->corpsIds[$corpId])) {
			return;
		}
		
		$this->corpsIds[$corpId] = true;

		$corp = new Corps();
		$corp->setTransaction($this->transaction);

		$corp->corp_id     = $corpId;
		$corp->title       = $corpTitle;
		$corp->alliance_id = $allyId;

		if (! $corp->save()) {
			$this->transaction->rollback(implode(' ;', $corp->getMessages()));
		}
	}
	
	protected function savePlayerData($playerId, $corpId, $playerName) {
		if ($playerId === 0 || isset($this->playersIds[$playerId])) {
			return;
		}
		
		$this->playersIds[$playerId] = true;

		$player = new Players();
		$player->setTransaction($this->transaction);

		$player->player_id = $playerId;
		$player->name      = $playerName;
		$player->corp_id   = $corpId;

		if (! $player->save()) {
			$this->transaction->rollback(implode(' ;', $player->getMessages()));
		}	
	}
	
}