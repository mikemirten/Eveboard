<?php
namespace Eveboard\Api\Functions\Corp;

use Eveboard\Api\Functions\FunctionAbstract;
use SimpleXMLElement, stdClass;

class CorporationSheet extends FunctionAbstract {
	
	protected function processData(SimpleXMLElement $data) {
		$sheet = new stdClass();
		
		$sheet->corporationID   = (int)    $data->corporationID;
		$sheet->corporationName = (string) $data->corporationName;
		$sheet->ceoID           = (int)    $data->ceoID;
		$sheet->ceoName         = (string) $data->ceoName;
		$sheet->stationID       = (int)    $data->stationID;
		$sheet->stationName     = (string) $data->stationName;
		$sheet->description     = (string) $data->description;
		$sheet->url             = (string) $data->url;
		$sheet->corporationID   = (int)    $data->corporationID;
		$sheet->allianceID      = (int)    $data->allianceID;
		$sheet->factionID       = (int)    $data->factionID;
		$sheet->allianceName    = (string) $data->allianceName;
		$sheet->taxRate         = (int)    $data->taxRate;
		$sheet->memberCount     = (int)    $data->memberCount;
		$sheet->memberLimit     = (int)    $data->memberLimit;
		$sheet->shares          = (int)    $data->shares;
		
		return $sheet;
	}
	
}