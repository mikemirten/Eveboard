<?php
namespace Eveboard\Api\Functions;

use Eveboard\Api\Client;

abstract class FunctionAbstract {
	
	/**
	 * Eve-Online API Client
	 *
	 * @var Client 
	 */
	protected $_client;
	
	public function __construct(Client $client) {
		$this->_client = $client;
	}
	
	public function __invoke() {
//		$result = $this->_client->request($section, $name);
	}
	
}