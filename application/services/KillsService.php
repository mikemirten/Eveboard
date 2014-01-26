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
	
	protected $apiConfig;
	
	protected $alliancesIds, $corpsIds, $playersIds;
	
	public function __construct(Client $client, $apiConfig) {
		$this->client    = $client;
		$this->apiConfig = $apiConfig;
	}
	
	public function importKills() {
		$transactionManager = new Manager();
		
		foreach ($this->apiConfig as $corp) {
			$this->client->setKeyId($corp->keyId);
			$this->client->setKeyCode($corp->keyCode);
			
			$this->importCorpKills($this->client, $transactionManager);
		}
	}

	protected function importCorpKills(Client $client, $transactionManager) {
		$corpSheet  = $client->corp->corporationSheet();
		$lastKillId = Kills::getLastKillId($corpSheet->corporationID);
		
		try {
			if ($lastKillId === null) {
				$kills = $client->corp->killLog();
			} else {
				$kills = $client->corp->killLog($lastKillId);
			}
		} catch (ApiError $exception) {
			$errorCode = $exception->getCode();
			
			// No fresh kills
			if ($errorCode === 118
			// Kill log exhausted
			||  $errorCode === 119
			// Unexpected beforeKillID
			||  $errorCode === 120) {
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
		
		$transaction = $transactionManager->get();
		
		// Kills
		foreach ($kills as $killData) {
			$this->saveKill($killData, $transaction);
		}
		
		// Items data from API
		if (! empty($needItemsIds)) {
			$this->getAndSaveItemsData($needItemsIds, $client, $transaction);
		}
		
		$transaction->commit();
	}
	
	/**
	 * Save the committed kill
	 * 
	 * @param type $killData
	 * @param type $transaction
	 */
	protected function saveKill($killData, $transaction) {
		$this->saveMemberData($killData, $transaction);
		$this->saveKillData($killData, $transaction);
		
		// Involved parties
		foreach ($killData->parts as $partData) {
			$this->saveInvolvedParty($killData->killID, $partData, $transaction);
		}

		// Lost items (destroyed, dropped)
		foreach ($killData->items as $itemData) {
			$this->saveLostItem($killData->killID, $itemData, $transaction);
		}
	}
	
	/**
	 * Save the primary data of the kill
	 * 
	 * @param type $killData
	 * @param type $transaction
	 */
	protected function saveKillData($killData, $transaction) {
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
			$transaction->rollback(implode(' ;', $kill->getMessages()));
		}
	}
	
	/**
	 * Save the involved parties data
	 * 
	 * @param int  $killId
	 * @param type $data
	 * @param type $transaction
	 */
	protected function saveInvolvedParty($killId, $data, $transaction) {
		$this->saveMemberData($data, $transaction);
		
		$involved = new Involved();
		$involved->setTransaction($transaction);

		$involved->kill_id      = $killId;
		$involved->character_id = $data->characterID;
		$involved->corp_id      = $data->corporationID;
		$involved->alliance_id  = $data->allianceID;
		$involved->faction_id   = $data->factionID;
		$involved->ship_id      = $data->shipTypeID;
		$involved->weapon_id    = $data->weaponTypeID;
		$involved->damage_done  = $data->damageDone;
		$involved->final_blow   = $data->finalBlow;

		if (! $involved->save()) {
			$transaction->rollback(implode(' ;', $involved->getMessages()));
		}
	}
	
	/**
	 * Save the data of involved member (killer or an other member)
	 * 
	 * @param type $data
	 * @param type $transaction
	 */
	protected function saveMemberData($data, $transaction) {
		$this->saveAllianceData(
			$data->allianceID,
			$data->allianceName,
			$transaction
		);
		
		$this->saveCorpData(
			$data->corporationID,
			$data->allianceID,
			$data->corporationName, 
			$transaction
		);
		
		$this->savePlayerData(
			$data->characterID,
			$data->corporationID,
			$data->characterName,
			$transaction
		);
	}
	
	/**
	 * Save the lost item
	 * 
	 * @param int  $killId
	 * @param type $data
	 * @param type $transaction
	 */
	protected function saveLostItem($killId, $data, $transaction) {
		$lost = new LostItems();
		$lost->setTransaction($transaction);

		$lost->kill_id       = $killId;
		$lost->item_id       = $data->typeID;
		$lost->flag          = $data->flag;
		$lost->qty_destroyed = $data->qtyDestroyed;
		$lost->qty_dropped   = $data->qtyDropped;

		if (! $lost->save()) {
			$transaction->rollback(implode(' ;', $lost->getMessages()));
		}
	}
	
	protected function getAndSaveItemsData(array $needItemsIds, Client $client, $transaction) {
		$chunks = array_chunk($needItemsIds, 100);
			
		foreach ($chunks as $chunk) {
			$items = $client->Eve->TypeName(implode(',', $chunk));

			foreach ($items as $itemId => $itemTitle) {
				$item = new Items();
				$item->setTransaction($transaction);

				$item->item_id = $itemId;
				$item->title   = $itemTitle;

				if (! $item->save()) {
					$transaction->rollback(implode(' ;', $item->getMessages()));
				}
			}
		}
	}
	
	protected function saveAllianceData($allyId, $allyTitle, $transaction) {
		if ($allyId === 0 || isset($this->alliancesIds[$allyId])) {
			return;
		}
		
		$this->alliancesIds[$allyId] = true;

		$ally = new Alliances();
		$ally->setTransaction($transaction);

		$ally->alliance_id = $allyId;
		$ally->title       = $allyTitle;

		if (! $ally->save()) {
			$transaction->rollback(implode(' ;', $ally->getMessages()));
		}
	}
	
	protected function saveCorpData($corpId, $allyId, $corpTitle, $transaction) {
		if ($corpId === 0 || isset($this->corpsIds[$corpId])) {
			return;
		}
		
		$this->corpsIds[$corpId] = true;

		$corp = new Corps();
		$corp->setTransaction($transaction);

		$corp->corp_id     = $corpId;
		$corp->title       = $corpTitle;
		$corp->alliance_id = $allyId;

		if (! $corp->save()) {
			$transaction->rollback(implode(' ;', $corp->getMessages()));
		}
	}
	
	protected function savePlayerData($playerId, $corpId, $playerName, $transaction) {
		if ($playerId === 0 || isset($this->playersIds[$playerId])) {
			return;
		}
		
		$this->playersIds[$playerId] = true;

		$player = new Players();
		$player->setTransaction($transaction);

		$player->player_id = $playerId;
		$player->name      = $playerName;
		$player->corp_id   = $corpId;

		if (! $player->save()) {
			$transaction->rollback(implode(' ;', $player->getMessages()));
		}	
	}
	
}