<?php
namespace Eveboard\Api\Functions;

use Eveboard\Api\Client;
use Eveboard\Api\Exceptions\ParamsError;
use SimpleXMLElement;

abstract class FunctionAbstract {
	
	/**
	 * Eve-Online API Client
	 *
	 * @var Client 
	 */
	protected $_client;
	
	/**
	 * Section and name of the function
	 *
	 * @var string
	 */
	protected $_section, $_name;
	
	/**
	 * Constructor
	 * 
	 * @param Client $client
	 */
	public function __construct(Client $client) {
		$this->_client = $client;
		
		$classParts = explode('\\', get_class($this));
		
		$this->_name    = ucfirst(array_pop($classParts));
		$this->_section = strtolower(array_pop($classParts));
	}
	
	/**
	 * Call the function
	 * 
	 * @return mixed
	 * @throws ParamsError
	 */
	public function __invoke() {
		$arguments  = func_get_args();
		$definition = $this->getParamsDefinition();
		
		$params = [];
		
		foreach ($definition as $param => $required) {
			$argValue = array_shift($arguments);
			
			if ($argValue === null) {
				if ($required) {
					throw new ParamsError('Required parameter(s) absent');
				}
			} else {
				$params[$param] = $argValue;
			}
		}
		
		$result = $this->_client->request($this->_section, $this->_name, $params);
		
		return $this->processData($result);
	}
	
	/**
	 * Get the parameters definition [string paramName => bool required]
	 * 
	 * @return array
	 */
	protected function getParamsDefinition() {
		return [];
	}
	
	/**
	 * Process the requested data
	 * 
	 * @param SimpleXMLElement $data
	 */
	abstract protected function processData(SimpleXMLElement $data);
	
}