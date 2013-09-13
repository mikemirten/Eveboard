<?php
namespace Eveboard\Killmail;

use Eveboard\Killmail\Exceptions\ParseError;
use Eveboard\Killmail\Exceptions\InvalidDataRequest;

/**
 * Eve killmail parser
 * 
 * @package    Eveboard
 * @subpackage Killmail
 * @author     mike.mirten
 * @version    1.0beta
 * 
 * @property-read string $datetime
 * @property-read string $destroyedItemName
 * @property-read string $victimName
 * @property-read string $corpName
 * @property-read string $allianceName
 * @property-read string $factionName
 * @property-read string $systemName
 * @property-read int    $securityLevel
 * @property-read float  $takenDamage
 * @property-read array  $involvedParties
 * @property-read array  $destroyedItems
 * @property-read array  $droppedItems
 * @property-read string $hash
 */
class Parser {
	
	const VICTIM_NAME      = 'Victim';
	const VICTIM_CORP      = 'Corp';
	const VICTIM_ALLIANCE  = 'Alliance';
	const VICTIM_FACTION   = 'Faction';
	const VICTIM_DESTROYED = 'Destroyed';
	const VICTIM_SYSTEM    = 'System';
	const VICTIM_MOON      = 'Moon';
	const VICTIM_SECURITY  = 'Security';
	const VICTIM_DAMAGE    = 'Damage Taken';
	
	const DATA_INVOLVED  = 'Involved parties';
	const DATA_DESTROYED = 'Destroyed items';
	const DATA_DROPPED   = 'Dropped items';
	
	const INVOLVED_SECURITY = 'Security';
	const INVOLVED_DAMAGE   = 'Damage Done';
	
	const STATE_COMMON   = 0;
	const STATE_INVOLVED = 1;
	const STATE_LOST     = 2;
	const STATE_DROPPED  = 3;
	
	const KEYS_RAW        = 0;
	const KEYS_CAMELCASE  = 1;
	const KEYS_UNDERSCORE = 2;
	
	/**
	 * Killmail source
	 *
	 * @var string
	 */
	protected $_source;
	
	/**
	 * Is killmail parsed
	 *
	 * @var bool
	 */
	protected $_parsed = false;
	
	/**
	 * Date and time
	 *
	 * @var string
	 */
	protected $_datetime;
	
	/**
	 * Victim's data
	 *
	 * @var array
	 */
	protected $_victimData = array();
	
	/**
	 * Involved parties' data
	 *
	 * @var array
	 */
	protected $_involvedData = array();
	
	/**
	 * Destroyed items' data
	 *
	 * @var array
	 */
	protected $_destroyedData = array();
	
	/**
	 * Dropped items' data
	 *
	 * @var array
	 */
	protected $_droppedData = array();
	
	/**
	 * Hash of the killmail
	 *
	 * @var string
	 */
	protected $_hash;

	/**
	 * Constructor
	 * 
	 * @param string killmail source
	 */
	public function __construct($killmailSource = null) {
		if ($killmailSource !== null) {
			$this->setKillmailSource($killmailSource);
		}
	}
	
	/**
	 * Set the EVE killmail source
	 * 
	 * @param string $source
	 */
	public function setKillmailSource($source) {
		$this->_source = trim($source);
		$this->_parsed = false;
		$this->_hash   = null;
	}
	
	/**
	 * Get the event's datetime
	 * 
	 * @return string
	 */
	public function getDatetime() {
		$this->parse();
		
		return $this->_datetime;
	}
	
	/**
	 * Get the victim's name
	 * 
	 * @return string
	 */
	public function getVictimName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_NAME])) {
			return $this->_victimData[self::VICTIM_NAME];
		}
	}
	
	/**
	 * Get the victim's destroyed item name
	 * 
	 * @return string
	 */
	public function getDestroyedItemName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_DESTROYED])) {
			return $this->_victimData[self::VICTIM_DESTROYED];
		}
	}
	
	/**
	 * Get the victim's corporation name
	 * 
	 * @return string
	 */
	public function getCorpName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_CORP])) {
			return $this->_victimData[self::VICTIM_CORP];
		}
	}
	
	/**
	 * Get the victim's alliance name
	 * 
	 * @return string
	 */
	public function getAllianceName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_ALLIANCE])) {
			return $this->_victimData[self::VICTIM_ALLIANCE];
		}
	}
	
	/**
	 * Get the victim's faction name
	 * 
	 * @return string
	 */
	public function getFactionName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_FACTION])) {
			return $this->_victimData[self::VICTIM_FACTION];
		}
	}
	
	/**
	 * Get the solar system's name
	 * 
	 * @return string
	 */
	public function getSystemName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_SYSTEM])) {
			return $this->_victimData[self::VICTIM_SYSTEM];
		}
	}
	
	/**
	 * Get the moon's name
	 * 
	 * @return string
	 */
	public function getMoonName() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_MOON])) {
			return $this->_victimData[self::VICTIM_MOON];
		}
	}
	
	/**
	 * Get the security level of the solar system
	 * 
	 * @return float
	 */
	public function getSecurityLevel() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_SECURITY])) {
			return $this->_victimData[self::VICTIM_SECURITY];
		}
	}
	
	/**
	 * Get the taken damage points
	 * 
	 * @return int
	 */
	public function getTakenDamage() {
		$this->parse();
		
		if (isset($this->_victimData[self::VICTIM_DAMAGE])) {
			return $this->_victimData[self::VICTIM_DAMAGE];
		}
	}

	/**
	 * Get the involved parties
	 * 
	 * @return array
	 */
	public function getInvolvedParties() {
		$this->parse();
		
		return $this->_involvedData;
	}
	
	/**
	 * Get the destroyed items
	 * 
	 * @return array
	 */
	public function getDestroyedItems() {
		$this->parse();
		
		return $this->_destroyedData;
	}
	
	/**
	 * Get the dropped items
	 * 
	 * @return array
	 */
	public function getDroppedItems() {
		$this->parse();
		
		return $this->_droppedData;
	}
	
	/**
	 * Get the hash of the killmail
	 * 
	 * @return string
	 */
	public function getHash() {
		if ($this->_hash === null) {
			$this->parse();
			
			$this->_hash = md5(json_encode([
				$this->_victimData,
				$this->_involvedData,
				$this->_destroyedData,
				$this->_droppedData
			]));
		}
		
		return $this->_hash;
	}
	
	/**
	 * Get the data as an array
	 * 
	 * @param  int $keysType
	 * @return array
	 */
	public function toArray($keysType = null) {
		$this->parse();
		
		if ($keysType === null) {
			$keysType = self::KEYS_CAMELCASE;
		}
		
		if ($keysType === self::KEYS_RAW) {
			return $this->_victimData;
		}
		
		$keys = array_keys($this->_victimData);
		
		if ($keysType === self::KEYS_CAMELCASE) {
			$keys = array_map('lcfirst', $keys);
			$keys = array_map(function($in) {
				return str_replace(' ', '', $in);
			}, $keys);
		} else if ($keysType === self::KEYS_UNDERSCORE) {
			$keys = array_map('strtolower', $keys);
			$keys = array_map(function($in) {
				return str_replace(' ', '_', $in);
			}, $keys);
		}
		
		return array_combine($keys, $this->_victimData);
	}
	
	/**
	 * Parse the killmail
	 */
	protected function parse() {
		if ($this->_parsed) {
			return;
		}
		
		if ($this->_source === null) {
			throw new ParseError('Killmail must be specified first');
		}
		
		$parts = explode(PHP_EOL . PHP_EOL, $this->_source);
		
		if (empty($parts)) {
			throw new ParseError('Killmail can not be empty');
		}
		
		$state = self::STATE_COMMON;
		
		foreach ($parts as $position => $data) {
			$data = trim($data);
			
			// Common state
			if ($state === self::STATE_COMMON) {
				// Datetime
				if ($position === 0) {
					$this->parseDatetime($data);
				}
				// Victim's data
				else if ($position === 1) {
					$this->parseVictim($data);
				}
				// Involved state switch
				else if (strpos($data, self::DATA_INVOLVED) === 0) {
					$state = self::STATE_INVOLVED;
				}
				// Destroyed items state switch
				else if (strpos($data, self::DATA_DESTROYED) === 0) {
					$state = self::STATE_LOST;
				}
				// Dropped items state switch
				else if (strpos($data, self::DATA_DROPPED) === 0) {
					$state = self::STATE_DROPPED;
				}
			}
			// Involved state
			else if ($state === self::STATE_INVOLVED) {
				if (strpos($data, self::DATA_DESTROYED) === 0) {
					$state = self::STATE_LOST;
				} else if (strpos($data, self::DATA_DROPPED) === 0) {
					$state = self::STATE_DROPPED;
				} else {
					$this->parseInvolved($data);
				}
			}
			// Destroyed items state
			else if ($state === self::STATE_LOST) {
				if (strpos($data, self::DATA_INVOLVED) === 0) {
					$state = self::STATE_INVOLVED;
				} else if (strpos($data, self::DATA_DROPPED) === 0) {
					$state = self::STATE_DROPPED;
				} else {
					$this->parseDestroyed($data);
				}
			}
			// Dropped items state
			else if ($state === self::STATE_DROPPED) {
				if (strpos($data, self::DATA_INVOLVED) === 0) {
					$state = self::STATE_INVOLVED;
				} else if (strpos($data, self::DATA_DESTROYED) === 0) {
					$state = self::STATE_LOST;
				} else {
					$this->parseDropped($data);
				}
			}
		}
		
		$this->_parsed = true;
	}
	
	/**
	 * Parse the datetime
	 * 
	 * @param  string $data
	 * @throws ParseError
	 */
	protected function parseDatetime($data) {
		if (! preg_match('/^\d{4}\.\d{2}\.\d{2} \d{2}:\d{2}:\d{2}$/', $data)) {
			throw new ParseError('Datetime is absent or is invalid');
		}

		$this->_datetime = $data;
	}
	
	/**
	 * Parse the victim's data
	 * 
	 * @param  string $data
	 */
	protected function parseVictim($dataRaw) {
		$data = $this->parseList($dataRaw);
		
		if (isset($data[self::VICTIM_SECURITY])) {
			$data[self::VICTIM_SECURITY] = (float) $data[self::VICTIM_SECURITY];
		}
		
		if (isset($data[self::VICTIM_DAMAGE])) {
			$data[self::VICTIM_DAMAGE] = (int) $data[self::VICTIM_DAMAGE];
		}
		
		$this->_victimData = $data;
	}
	
	/**
	 * Parse an involved data
	 * 
	 * @param string $dataRaw
	 */
	protected function parseInvolved($dataRaw) {
		$data = $this->parseList($dataRaw);
		
		if (isset($data[self::INVOLVED_SECURITY])) {
			$data[self::INVOLVED_SECURITY] = (float) $data[self::INVOLVED_SECURITY];
		}
		
		if (isset($data[self::INVOLVED_DAMAGE])) {
			$data[self::INVOLVED_DAMAGE] = (int) $data[self::INVOLVED_DAMAGE];
		}
		
		$this->_involvedData[] = $data;
	}
	
	/**
	 * Parse the destroyed items data
	 * 
	 * @param string $dataRaw
	 */
	protected function parseDestroyed($dataRaw) {
		$data = explode(PHP_EOL, $dataRaw);
		$this->_destroyedData = array_map('trim', $data);
	}
	
	/**
	 * Parse the dropped items data
	 * 
	 * @param string $dataRaw
	 */
	protected function parseDropped($dataRaw) {
		$data = explode(PHP_EOL, $dataRaw);
		$this->_droppedData = array_map('trim', $data);
	}
	
	/**
	 * Parse the datalist
	 * 
	 * @param  string $dataRaw
	 * @return array
	 * @throws ParseError
	 */
	protected function parseList($dataRaw) {
		$parts = explode(PHP_EOL, $dataRaw);
		$data  = array();
		
		foreach ($parts as $part) {
			$sub = explode(':', $part);
			
			if (! isset($sub[1])) {
				throw new ParseError('Data parse error');
			}
			
			$data[trim($sub[0])] = trim($sub[1]);
		}
		
		return $data;
	}
	
	/**
	 * Get the data
	 * 
	 * @param  string $name Data type
	 * @return mixed
	 * @throws InvalidDataRequest
	 */
	public function __get($name) {
		$method = 'get' . ucfirst($name);
		
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		
		throw new InvalidDataRequest('Invalid data requested: "' . $name . '"');
	}
	
}