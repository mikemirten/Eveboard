<?php
namespace Eveboard\Killmail;

class InvolvedPart {
	
	const DATA_NAME     = 'Name';
	const DATA_SECURITY = 'Security';
	const DATA_CORP     = 'Corp';
	const DATA_ALLIANCE = 'Alliance';
	const DATA_FACTION  = 'Faction';
	const DATA_SHIP     = 'Ship';
	const DATA_WEAPON   = 'Weapon';
	const DATA_DAMAGE   = 'Damage Done';
	
	const FINAL_BLOW = '(laid the final blow)';
	
	const UNKNOWN = 'NONE';
	
	protected $_data;
	
	protected $_finalBlow = false;
	
	public function __construct(array $data) {
		if (! empty($data[self::DATA_NAME])) {
			$pos = strpos($data[self::DATA_NAME], self::FINAL_BLOW);
			
			if ($pos !== false) {
				$data[self::DATA_NAME] = rtrim(substr($data[self::DATA_NAME], 0, $pos));
				
				$this->_finalBlow = true;
			}
		}
		
		$this->_data = $data;
	}
	
	public function getPartName() {
		if (isset($this->_data[self::DATA_NAME])) {
			return $this->_data[self::DATA_NAME];
		}
	}
	
	public function getAllianceName() {
		if (isset($this->_data[self::DATA_ALLIANCE])
		&& $this->_data[self::DATA_ALLIANCE] !== self::UNKNOWN) {
			return $this->_data[self::DATA_ALLIANCE];
		}
	}
	
	public function getCorpName() {
		if (isset($this->_data[self::DATA_CORP])
		&& $this->_data[self::DATA_CORP] !== self::UNKNOWN) {
			return $this->_data[self::DATA_CORP];
		}
	}
	
	public function getFactionName() {
		if (isset($this->_data[self::DATA_FACTION])
		&& $this->_data[self::DATA_FACTION] !== self::UNKNOWN) {
			return $this->_data[self::DATA_FACTION];
		}
	}
	
	public function getShipName() {
		if (isset($this->_data[self::DATA_SHIP])
		&& $this->_data[self::DATA_SHIP] !== self::UNKNOWN) {
			return $this->_data[self::DATA_SHIP];
		}
	}
	
	public function getWeaponName() {
		if (isset($this->_data[self::DATA_WEAPON])
		&& $this->_data[self::DATA_WEAPON] !== self::UNKNOWN) {
			return $this->_data[self::DATA_WEAPON];
		}
	}
	
	public function getDamageDone() {
		if (isset($this->_data[self::DATA_DAMAGE])) {
			return $this->_data[self::DATA_DAMAGE];
		}
	}
	
	public function getSecurity() {
		if (isset($this->_data[self::DATA_SECURITY])) {
			return $this->_data[self::DATA_SECURITY];
		}
	}
	
	public function isFinalBlow() {
		return $this->_finalBlow;
	}
	
	public function isNpc() {
		return ! isset(
			$this->_data[self::DATA_ALLIANCE],
			$this->_data[self::DATA_CORP],
			$this->_data[self::DATA_FACTION],
			$this->_data[self::DATA_SECURITY],
			$this->_data[self::DATA_SHIP],
			$this->_data[self::DATA_WEAPON]
		);
	}
	
}