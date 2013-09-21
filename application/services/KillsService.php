<?php

use Eveboard\Killmail\Parser;

class KillsService {
	
	public function addKill($source) {
		$mail = new Parser($source);
		
		$kill = Kills::findByHash($mail->getHash());
		
		if ($kill !== false) {
			return;
		}
		
		$kill = new Kills();
		
		$kill->committed    = $mail->getDatetime();
		$kill->security     = $mail->getSecurityLevel();
		$kill->damage_taken = $mail->getTakenDamage();
		$kill->kill_hash    = $mail->getHash();
		$kill->mail_source  = gzcompress($source, 1);
		
		// Resolve player
		$playerName = $mail->getVictimName();
		
		if ($playerName !== null) {
			$player = Players::findByName($playerName);

			if ($player === false) {
				$player = new Players();
				$player->name = $playerName;
				$player->save();
			}

			$kill->player_id = $player->player_id;
		}
		
		// Resolve aliance
		$allianceName = $mail->getAllianceName();
		
		if ($allianceName !== null) {
			$alliance = Alliances::findByTitle($allianceName);

			if ($alliance === false) {
				$alliance = new Alliances();
				$alliance->title = $allianceName;
				$alliance->save();
			}
			
			$kill->alliance_id = $alliance->alliance_id;
		}
		
		// Resolve corporation
		$corpName = $mail->getCorpName();
		
		if ($corpName !== null) {
			$corp = Corps::findByTitle($corpName);

			if ($corp === false) {
				$corp = new Corps();
				$corp->title = $corpName;
				$corp->save();
			}
			
			$kill->corp_id = $corp->corp_id;
		}
		
		// Resolve item
		$itemName = $mail->getDestroyedItemName();
		
		if ($itemName !== null) {
			$item = Items::findByTitle($itemName);

			if ($item === false) {
				$item = new Items();
				$item->title = $itemName;
				$item->save();
			}
			
			$kill->item_id = $item->item_id;
		}
		
		// Resolve system
		$systemName = $mail->getSystemName();
		
		if ($systemName !== null) {
			$system = Systems::findByTitle($systemName);

			if ($system === false) {
				$system = new Systems();
				$system->title = $systemName;
				$system->save();
			}
			
			$kill->system_id = $system->system_id;
		}
		
		// Resolve involved
		$involvedParties = $mail->getInvolvedParties();
		
		if (! empty($involvedParties)) {
			$involvedModels = $this->processInvolved($involvedParties);
			
			var_dump($involvedModels); die;
		}
		
//		$kill->save();
	}
	
	/**
	 * 
	 * @param array $involvedParts
	 */
	protected function processInvolved(array $involvedParts) {
		$involvedModels = array();
		
		foreach ($involvedParts as $part) {
			if ($part->isNpc()) {
				continue;
			}
			
			$involved = new Involved();
			
			$involved->security    = $part->getSecurity();
			$involved->damage_done = $part->getDamageDone();
			
			if ($part->isFinalBlow()) {
				$involved->final_blow = 1;
			}
			
			// Resolve involved
			$involvedName = $part->getPartName();
			
			if ($involvedName !== null) {
				$player = Players::findByName($involvedName);

				if ($player === false) {
					$player = new Players();
					$player->name = $involvedName;
					$player->save();
				}

				$involved->player_id = $player->player_id;
			}
			
			// Resolve aliance
			$allianceName = $part->getAllianceName();

			if ($allianceName !== null) {
				$alliance = Alliances::findByTitle($allianceName);

				if ($alliance === false) {
					$alliance = new Alliances();
					$alliance->title = $allianceName;
					$alliance->save();
				}

				$involved->alliance_id = $alliance->alliance_id;
			}

			// Resolve corporation
			$corpName = $part->getCorpName();

			if ($corpName !== null) {
				$corp = Corps::findByTitle($corpName);

				if ($corp === false) {
					$corp = new Corps();
					$corp->title = $corpName;
					$corp->save();
				}

				$involved->corp_id = $corp->corp_id;
			}
			
			// Resolve ship
			$shipName = $part->getShipName();

			if ($shipName !== null) {
				$ship = Items::findByTitle($shipName);

				if ($ship === false) {
					$ship = new Items();
					$ship->title = $shipName;
					$ship->save();
				}

				$involved->ship_id = $ship->item_id;
			}
			
			// Resolve item
			$weaponName = $part->getWeaponName();

			if ($weaponName !== null) {
				$weapon = Items::findByTitle($weaponName);

				if ($weapon === false) {
					$weapon = new Items();
					$weapon->title = $weaponName;
					$weapon->save();
				}

				$involved->weapon_id = $weapon->item_id;
			}
			
			$involvedModels[] = $involved;
		}
		
		return $involvedModels;
	}
	
}