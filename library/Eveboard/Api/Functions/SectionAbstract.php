<?php
namespace Eveboard\Api\Functions;

use Eveboard\Api\Client;

abstract class SectionAbstract {
	
	/**
	 * Eve-Online API Client
	 *
	 * @var Client 
	 */
	protected $_client;
	
	/**
	 * Constructor
	 * 
	 * @param Client $client
	 */
	public function __construct(Client $client) {
		$this->_client = $client;
	}
	
	/**
	 * Call the api function
	 * 
	 * @param string $name
	 * @param array  $arguments
	 */
	public function __call($name, $arguments) {
		$class = get_class($this) . '\\' . ucfirst($name);
		
		return call_user_func_array(new $class($this->_client), $arguments);
	}
	
}