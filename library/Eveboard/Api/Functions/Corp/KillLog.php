<?php
namespace Eveboard\Api\Functions\Corp;

use Eveboard\Api\Functions\FunctionAbstract;
use SimpleXMLElement, stdClass;

class KillLog extends FunctionAbstract {
	
	protected function processData(SimpleXMLElement $data) {
		$kills = [];
		
		foreach ($data->rowset->row as $kill) {
			$killData = new stdClass();
			
			// Kill data
			$killAttrs = $kill->attributes();
			
			$killData->killID        = (int)    $killAttrs->killID;
			$killData->solarSystemID = (int)    $killAttrs->solarSystemID;
			$killData->killTime      = (string) $killAttrs->killTime;
			$killData->moonID        = (int)    $killAttrs->moonID;
			
			// Victim data
			$victimAttrs = $kill->victim->attributes();
			
			$killData->characterID     = (int)    $victimAttrs->characterID;
			$killData->corporationID   = (int)    $victimAttrs->corporationID;
			$killData->allianceID      = (int)    $victimAttrs->allianceID;
			$killData->factionID       = (int)    $victimAttrs->factionID;
			$killData->damageTaken     = (int)    $victimAttrs->damageTaken;
			$killData->shipTypeID      = (int)    $victimAttrs->shipTypeID;
			$killData->characterName   = (string) $victimAttrs->characterName;
			$killData->corporationName = (string) $victimAttrs->corporationName;
			$killData->allianceName    = (string) $victimAttrs->allianceName;
			$killData->factionName     = (string) $victimAttrs->factionName;
			
			$parts = [];
			
			foreach ($kill->rowset->row as $part) {
				$partData  = new stdClass();
				$partAttrs = $part->attributes();
				
				$partData->characterID     = (int)    $partAttrs->characterID;
				$partData->corporationID   = (int)    $partAttrs->corporationID;
				$partData->allianceID      = (int)    $partAttrs->allianceID;
				$partData->factionID       = (int)    $partAttrs->factionID;
				$partData->securityStatus  = (float)  $partAttrs->securityStatus;
				$partData->damageDone      = (int)    $partAttrs->damageDone;
				$partData->finalBlow       = (int)    $partAttrs->finalBlow;
				$partData->weaponTypeID    = (int)    $partAttrs->weaponTypeID;
				$partData->shipTypeID      = (int)    $partAttrs->shipTypeID;
				$partData->characterName   = (string) $partAttrs->characterName;
				$partData->corporationName = (string) $partAttrs->corporationName;
				$partData->allianceName    = (string) $partAttrs->allianceName;
				$partData->factionName     = (string) $partAttrs->factionName;
				
				$parts[] = $partData;
			}
			
			$killData->parts = $parts;
			
			$kills[$killData->killID] = $killData;
		}
		
		return $kills;
	}
	
}